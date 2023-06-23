<template>
    <div>
        <!-- ENCABEZADO -->
        <div>
            <b-row>
                <b-col sm="3">
                    <h4 style="color: #170057">{{ agregar ? 'Nueva' : 'Editar' }} entrada</h4>
                </b-col>
                <b-col sm="6" class="text-right">
                    <b-button :disabled="load || this.form.registros.length == 0 || stateN != true"
                        @click="confirmarEntrada()" variant="success" pill>
                        <i v-if="agregar === true" class="fa fa-check"> {{ !load ? 'Guardar' : 'Guardando' }}</i>
                        <i v-else class="fa fa-check"> {{ !load ? 'Guardar  cambios' : 'Guardando' }}</i>
                    </b-button>
                </b-col>
                <b-col sm="3" class="text-right">
                    <!-- PENDIENTE EL BOTON DE REGRESAR -->
                    <b-button variant="secondary" pill @click="goBack()">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col sm="1"><label>Folio</label></b-col>
                <b-col sm="5">
                    <b-form-input style="text-transform:uppercase;"
                        v-model="form.folio" autofocus
                        :disabled="load" :state="stateN"
                        @change="guardarNum()">
                    </b-form-input>
                </b-col>
                <b-col sm="3" align="right">
                    <label><b>Unidades:</b> {{ form.unidades | formatNumber }}</label>
                </b-col>
            </b-row>
            <b-row>
                <b-col sm="1"><label>Editorial</label></b-col>
                <b-col sm="5">
                    <b-form-select v-model="form.editorial" :disabled="load || form.registros.length > 0"
                        autofocus :state="stateE" :options="options">
                    </b-form-select>
                </b-col>
            </b-row>
        </div>
        <hr>
        <b-table :items="form.registros" :fields="fieldsRE">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
            <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
            <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
            <template v-slot:cell(eliminar)="row">
                <b-button v-if="agregar == true" pill
                    variant="danger" @click="eliminarRegistro(row.item, row.index)">
                    <i class="fa fa-minus-circle"></i>
                </b-button>
            </template>
            <template #thead-top="row">
                <tr v-if="form.editorial !== null">
                    <th colspan="1"></th>
                    <th>ISBN</th>
                    <th>Libro</th>
                    <th>Unidades</th>
                </tr>
                <tr v-if="form.editorial !== null">
                    <th colspan="1"></th>
                    <th>
                        <b-input autofocus v-model="temporal.isbn" 
                            @keyup="buscarLibroISBN()"
                        ></b-input>
                        <div class="list-group" v-if="resultsISBNS.length" id="listaLR">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultsISBNS" @click="datosLibro(libro)">
                                {{ libro.ISBN }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-input style="text-transform:uppercase;"
                            v-model="temporal.titulo" @keyup="mostrarLibros()">
                        </b-input>
                        <div class="list-group" v-if="resultslibros.length" id="listaLR">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultslibros" @click="datosLibro(libro)">
                                {{ libro.titulo }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-form-input v-model="temporal.unidades" 
                            type="number" required>
                        </b-form-input>
                    </th>
                    <th>
                        <b-button :disabled="temporal.id == null || temporal.unidades <= 0"
                            variant="success" pill @click="saveTemporal()">
                            <i class="fa fa-level-down"></i>
                        </b-button>
                    </th>
                </tr>
            </template>
        </b-table>
        <!-- MODALS -->
        <b-modal ref="modal-confirmarEntrada" size="xl" title="Resumen de la entrada">
            <b-row>
                <b-col sm="8">
                    <label><b>Folio:</b> {{form.folio}}</label><br>
                    <label><b>Editorial:</b> {{form.editorial}}</label>
                </b-col>
                <b-col sm="4"><label><b>Unidades:</b> {{ form.unidades | formatNumber }}</label></b-col>
            </b-row>
            <b-table :items="form.registros" :fields="fieldsRE">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
            </b-table>
            <div slot="modal-footer">
                <b-row>
                    <b-col sm="10">
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> <b>Verificar los datos de la entrada.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                        </b-alert>
                    </b-col>
                    <b-col sm="2" align="right">
                        <b-button @click="onSubmit()" 
                            variant="success" :disabled="load">
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
    props: ['agregar', 'form', 'editoriales'],
    mixins: [formatNumber,toast],
    data(){
        return {
            load: false,
            stateN: null,
            stateE: null,
            options: [],
            fieldsRE: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                'unidades', 
                {key: 'eliminar', label: ''}
            ],
            temporal: {
                id: null,
                isbn: null,
                titulo: null,
                unidades: null
            },
            resultslibros: [],
            resultsISBNS: []
        }
    },
    created: function(){
        this.options.push({
            value: null,
            text: 'Selecciona una opción'
        });
        this.editoriales.forEach(editorial => {
            this.options.push({
                value: editorial.editorial,
                text: editorial.editorial
            });
        });
    },
    methods: {
        confirmarEntrada(){
            this.$refs['modal-confirmarEntrada'].show();
        },
        onSubmit(){
            this.load = true;
            axios.post('/entradas/store', this.form).then(response => {
                swal("OK", "La entrada se creo correctamente", "success")
                    .then((value) => { location.reload(); });
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        guardarNum(){
            if(this.form.folio.length > 0){
                axios.get('/buscarFolio', {params: {folio: this.form.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.stateN = false;
                        this.makeToast('warning', 'El folio ya existe.');
                    }
                    else{
                        this.stateN = true;
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
            else{
                this.stateN = false;
                this.makeToast('warning', 'Definir folio.');
            }
        },
        // ELIMINAR REGISTRO DE ENTRADA
        eliminarRegistro(item, i){
            this.restasUnidades(item, i);
        },
        restasUnidades(item, i){
            this.form.registros.splice(i, 1);
            this.acum_total();
        },
        buscarLibroISBN(){
            if(this.temporal.isbn.length > 0){
                axios.get('/isbn_por_editorial', {params: {isbn: this.temporal.isbn, editorial: this.form.editorial}}).then(response => {
                    this.resultsISBNS = response.data;
                }).catch(error => {
                    this.makeToast('warning', 'ISBN es incorrecto o pertenece a otra editorial.');
                });
            } else {
                this.resultsISBNS = [];
            }
        },
        inicializar_temporal(id, titulo, ISBN){
            this.temporal.id = id;
            this.temporal.titulo = titulo;
            this.temporal.isbn = ISBN;
            this.temporal.unidades = 0;
        },
        mostrarLibros(){
            if(this.temporal.titulo.length > 0){
                axios.get('/libros_por_editorial', {params: {queryTitulo: this.temporal.titulo, editorial: this.form.editorial}}).then(response => {
                    this.resultslibros = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            } else {
                this.resultslibros = [];
            }
        },
        datosLibro(libro){
            this.resultslibros = [];
            this.resultsISBNS = [];
            this.inicializar_temporal(libro.id, libro.titulo, libro.ISBN);
        },
        saveTemporal(){
            this.form.registros.push({
                id: this.temporal.id,
                isbn: this.temporal.isbn,
                titulo: this.temporal.titulo,
                unidades: parseInt(this.temporal.unidades)
            });
            this.acum_total();
            this.inicializar_temporal(null, null, null);
        },
        acum_total(){
            this.form.unidades = 0;
            this.form.registros.forEach(registro => {
                this.form.unidades += parseInt(registro.unidades);
            });
        },
        goBack(){
            this.$emit('goBack', true);
        }
    }
}
</script>

<style scoped>
    #listaLR{
        position: absolute;
        z-index: 100
    }
</style>