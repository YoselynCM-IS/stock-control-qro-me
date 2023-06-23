<template>
    <div>
        <!-- FUNCIONES (ENCABEZADO) -->
        <b-row>
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="remclientesData" 
                    @pagination-change-page="getResults">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
            <b-col>
                <!-- BUSCAR CLIENTE -->
                <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                    style="text-transform:uppercase;" placeholder="BUSCAR CLIENTE">
                </b-input>
                <div class="list-group" v-if="clientes.length" id="listP">
                    <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                        v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                        {{ cliente.name }}
                    </a>
                </div>
            </b-col>
            <b-col sm="4" class="text-right">
                <b-button href="/descargar_gralClientes" variant="dark" pill>
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
        </b-row>
        <!-- TABLA DE CLIENTES -->
        <b-table v-if="!load" hover class="mt-3" responsive
            :items="remclientesData.data" :fields="fieldsRemCs">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
            <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
            <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
            <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
            <template v-slot:cell(ver_pagos)="row">
                <b-button v-if="row.item.total_devolucion > 0 || row.item.total_pagos > 0"
                    :href="`/cortes/details_cliente/${row.item.cliente_id}`" target="blank" 
                    variant="info" pill>Mostrar
                </b-button>
            </template>
        </b-table>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import searchCliente from '../../../mixins/searchCliente';
import toast from '../../../mixins/toast';
import LoadComponent from '../partials/LoadComponent.vue';
export default {
  components: { LoadComponent },
    mixins: [formatNumber,searchCliente,toast],
    data(){
        return {
            fieldsRemCs: [
                {key: 'index', label: 'N.'},
                {key: 'name', label: 'Cliente'}, 
                {key: 'total', label: 'Salida'}, 
                {key: 'total_pagos', label: 'Pagado'},
                {key: 'total_devolucion', label: 'Devolución'}, 
                {key: 'total_pagar', label: 'Pagar'},
                {key: 'ver_pagos', label: 'Pago(s)'},
            ],
            load: false,
            remclientesData: {},
            cliente: null
        }
    },
    mounted: function(){
        this.getResults();
    },
    methods: {
        // OBTENER REMISIONES POR PAGINA
        getResults(page = 1){
            if(this.cliente == null) this.http_all(page);
            else this.selectCliente(this.cliente);
        },
        http_all(page){
            this.load = true;
            axios.get(`/remcliente/index?page=${page}`).then(response => {
                this.remclientesData = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // SELECCIONAR CLIENTE
        selectCliente(cliente){
            this.load = true;
            this.cliente = cliente;
            this.clientes = [];
            axios.get('/remcliente/by_cliente', {params: {cliente_id: this.cliente.id}}).then(response => {
                if(response.data.data.length > 0){
                    this.remclientesData = response.data;
                    this.queryCliente = this.cliente.name;
                } else {
                    this.makeToast('warning', 'El cliente seleccionado no tiene una cuenta general registrada');
                }
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
    }
}
</script>

<style>
    #listaP{
        position: absolute;
        z-index: 100
    }
</style>