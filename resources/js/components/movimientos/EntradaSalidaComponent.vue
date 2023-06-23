<template>
    <div>
        <!-- ENCABEZADO -->
        <b-row>
            <b-col>
                <b-pagination v-if="movimientos.length > 0" aria-controls="table-es"
                    v-model="currentPage" :per-page="perPage"
                    :total-rows="movimientos.length">
                </b-pagination>
            </b-col>
            <b-col sm="3">
                <b-row>
                    <b-col sm="1">De:</b-col>
                    <b-col>
                        <b-form-datepicker v-model="fechas.de" :disabled="load"></b-form-datepicker>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col sm="1">A:</b-col>
                    <b-col>
                        <b-form-datepicker v-model="fechas.a" :disabled="load"></b-form-datepicker>
                    </b-col>
                </b-row>
            </b-col>
            <b-col sm="2">
                <b-button variant="primary" pill block
                    :disabled="load || (fechas.de == null || fechas.a == null)"
                    @click="get_entradas_salidas">
                    <i class="fa fa-search"></i>
                </b-button>
                <!-- <b-button variant="dark" pill block :disabled="load || movimientos.length == 0">
                    <i class="fa fa-download"></i> Descargar
                </b-button> -->
            </b-col>
        </b-row>
        <!-- REGISTROS -->
        <div v-if="!load" class="mt-5">
            <b-table v-if="searchFecha" :items="movimientos" :fields="fields"
                id="table-es" striped responsive 
                :per-page="perPage" :current-page="currentPage">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(details)="row">
                    <b-button variant="info" pill size="sm"
                        @click="getDetails(row.item)">
                        <i class="fa fa-info-circle"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="2" class="table-success text-center">ENTRADAS</th>
                        <th colspan="5" class="table-primary text-center">SALIDAS</th>
                        <th></th>
                    </tr>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary" class="text-center">
                <i class="fa fa-info-circle"></i> Selecciona las fechas en las que deseas hacer la consulta de libros.
            </b-alert>
        </div>
        <load-component v-else></load-component>

        <!-- MODALS -->
        <b-modal ref="show-libro" hide-footer size="xl" :title="libro.libro">
            <!-- entradas -->
            <table-mov-libro :label="'Folio'" :titulo="'ENTRADAS'" :registros="libro.entradas"></table-mov-libro>
            <!-- devoluciones -->
            <table-mov-libro :label="'Folio'" :titulo="'DEVOLUCIONES (REMISIONES)'" :registros="libro.devoluciones"></table-mov-libro>
            <!-- entdevoluciones -->
            <table-mov-libro :label="'Folio'" :titulo="'DEVOLUCIONES (ENTRADAS)'" :registros="libro.entdevoluciones"></table-mov-libro>
            <!-- remisiones -->
            <table-mov-libro :label="'Folio'" :titulo="'REMISIONES'" :registros="libro.remisiones"></table-mov-libro>
            <!-- notas -->
            <table-mov-libro :label="'Folio'" :titulo="'NOTAS'" :registros="libro.notas"></table-mov-libro>
            <!-- promociones -->
            <table-mov-libro :label="'Folio'" :titulo="'PROMOCIONES'" :registros="libro.promociones"></table-mov-libro>
            <!-- donaciones-->
            <table-mov-libro :label="'Plantel'" :titulo="'DONACIONES'" :registros="libro.donaciones"></table-mov-libro>
        </b-modal>
    </div>
</template>

<script>
import LoadComponent from '../cortes/partials/LoadComponent.vue';
import moment from 'moment';
import toast from './../../mixins/toast';
import TableMovLibro from './partials/TableMovLibro.vue';
export default {
  components: { LoadComponent, TableMovLibro },
  mixins: [toast],
    data(){
        return {
            fechas: {
                de: null,
                a: null
            },
            fields: [
                { key: 'index', label: 'N.' },
                { key: 'libro', label: 'Libro' },
                { key: 'entradas', label: 'Entradas' },
                { key: 'devoluciones', label: 'Devoluciones (Remisiones)' },
                { key: 'entdevoluciones', label: 'Devoluciones (Entradas)' },
                { key: 'remisiones', label: 'Remisiones' },
                { key: 'notas', label: 'Notas' },
                { key: 'promociones', label: 'Promociones' },
                { key: 'donaciones', label: 'Donaciones' },
                { key: 'details', label: 'Detalles' },
            ],
            movimientos: [],
            searchFecha: false,
            load: false,
            perPage: 25,
            currentPage: 1,
            libro: {}
        }
    },
    created: function(){
        // this.get_entradas_salidas();
    },
    methods: {
        // OBTENER ENTRADAS Y SALIDAS DE LIBROS
        get_entradas_salidas(){
            var fecha1 = moment(this.fechas.de);
            var fecha2 = moment(this.fechas.a);
            var diferencia = fecha2.diff(fecha1, 'days');
            if(diferencia >= 0 && diferencia <= 7){
                this.load = true;
                axios.get('/libro/entradas_salidas', {params: {de: this.fechas.de, a: this.fechas.a}}).then(response => {
                    this.movimientos = response.data;
                    this.searchFecha = true;
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            } else {
                this.makeToast('warning', 'Solo se puede consultar como máximo una semana.');
            }
        },
        // OBTENER DETALLES DE LA REMISION
        getDetails(libro){
            this.load = true;
            axios.get('/libro/details_entsal', {params: {
                libro_id: libro.id, de: this.fechas.de, a: this.fechas.a
            }}).then(response => {
                this.libro = response.data;
                this.$refs['show-libro'].show();
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