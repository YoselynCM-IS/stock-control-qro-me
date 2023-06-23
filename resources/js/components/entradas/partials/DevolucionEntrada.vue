<template>
    <div>
        <b-row>
            <b-col sm="8">
                <label><b>Folio:</b> {{form.folio}}</label><br>
                <label><b>Editorial:</b> {{form.editorial}}</label>
            </b-col>
            <b-col>
                <b-button variant="success" pill 
                    @click="confirmarDevolucion()">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="form.registros" :fields="fieldsD">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
            <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
            <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
            <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
            <template v-slot:cell(unidades_base)="row">
                <b-form-input :id="`inpEntDev-${row.index}`" type="number" 
                    @change="obtenerSubtotal(row.item, row.index)"
                    v-model="row.item.unidades_base"
                    required :disabled="load">
                </b-form-input>
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="4"></th>
                    <th>{{ todo_unidades | formatNumber }}</th>
                    <th>${{ todo_total | formatNumber }}</th>
                </tr>
            </template>
        </b-table>
        <b-modal ref="modal-confirmarDevolucion" size="xl" title="Resumen de la devolución">
            <label><b>Folio:</b> {{form.folio}}</label><br>
            <label><b>Editorial:</b> {{form.editorial}}</label><br>
            <b-table :items="form.registros" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
                <template v-slot:cell(unidades_base)="row">{{ row.item.unidades_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ todo_unidades | formatNumber }}</th>
                        <th>{{ todo_total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div slot="modal-footer">
                <b-row>
                    <b-col sm="9">
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> <b>Verificar la devolución.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                        </b-alert>
                    </b-col>
                    <b-col sm="3" align="right">
                        <b-button :disabled="load" variant="success"
                            @click="guardarDevolucion()">
                            <i class="fa fa-check"></i> Confirmar
                        </b-button>
                    </b-col>
                </b-row>
            </div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from './../../../mixins/formatNumber';
import toast from './../../../mixins/toast';
export default {
    props: ['formDev'],
    mixins: [formatNumber,toast],
    data(){
        return {
            fieldsD: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'costo_unitario', label: 'Costo unitario'}, 
                {key: 'unidades_base', label: 'Unidades'}, 
                {key: 'total_base', label: 'Subtotal'}
            ],
            load: false,
            form: {},
            todo_unidades: 0,
            todo_total: 0
        }
    },
    created: function(){
        this.form.id = this.formDev.entrada.id;
        this.form.folio = this.formDev.entrada.folio;
        this.form.editorial = this.formDev.entrada.editorial;
        this.form.total = this.formDev.entrada.total;
        this.form.unidades = this.formDev.entrada.unidades;
        this.form.total_devolucion = this.formDev.entrada.total_devolucion;
        this.form.created_at = this.formDev.entrada.created_at;
        this.form.registros = this.formDev.entrada.registros;
        this.form.entdevoluciones = this.formDev.entdevoluciones;
        this.form.todo_total = 0;
        this.form.todo_unidades = 0;
    },
    methods: {
        // GUARDAR DEVOLUCION
        confirmarDevolucion(){
            // if(this.form.todo_total > 0){
                this.$refs['modal-confirmarDevolucion'].show();
            // } else {
            //     this.makeToast('warning', 'El total debe ser mayor a cero.');
            // }
        },
        obtenerSubtotal(registro, i){
            if(registro.unidades_base <= registro.libro.piezas){
                if(registro.unidades_base >= 0){
                    if(registro.unidades_base <= registro.unidades_pendientes){
                        this.form.registros[i].total_base = registro.unidades_base * registro.costo_unitario;
                        if(i + 1 < this.form.registros.length){
                            document.getElementById('inpEntDev-'+(i+1)).focus();
                            document.getElementById('inpEntDev-'+(i+1)).select();
                        }
                    } else{
                        this.makeToast('warning', 'Las unidades son mayor a las unidades pendientes');
                        this.to_zero(i);
                    }
                } else{
                    this.makeToast('warning', 'Unidades invalidas');
                    this.to_zero(i);
                } 
            } else {
                this.makeToast('warning', `Hay ${registro.libro.piezas} en existencia`);
                this.to_zero(i);
            }
            this.acumular_ut();
        },
        acumular_ut(){
            this.todo_unidades = 0;
            this.todo_total = 0;
            this.form.registros.forEach(registro => {
                this.todo_unidades += parseInt(registro.unidades_base);
                this.todo_total += parseFloat(registro.total_base);
            });
        },
        to_zero(i){
            this.form.registros[i].unidades_base = 0;
            this.form.registros[i].total_base = 0;
        },
        // CONFIRMAR DEVOLUCION
        guardarDevolucion(){
            this.load = true;
            this.form.todo_unidades = this.todo_unidades;
            this.form.todo_total = this.todo_total;
            axios.post('/entradas/devolucion', this.form).then(response => {
                swal("OK", "La devolución se guardo correctamente.", "success")
                    .then((value) => { location.reload(); });
                this.load = false; 
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>