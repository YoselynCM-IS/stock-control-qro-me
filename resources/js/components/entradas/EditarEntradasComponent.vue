<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoEntradas">
            <b-row>
                <!-- BUSCAR ENTRADA POR FOLIO -->
                <b-col>
                    <b-row>
                        <b-col sm="2"><label>Folio</label></b-col>
                        <b-col sm="10">
                            <b-form-input 
                                v-model="folio"
                                @keyup.enter="porFolio()"
                                style="text-transform:uppercase;">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- MOSTRAR ENTRADAS POR EDITORIAL -->
                <b-col>
                    <b-row>
                        <b-col sm="3"><label>Editorial</label></b-col>
                        <b-col sm="9">
                            <b-form-select v-model="editorial" :options="options" @change="mostrarEditoriales()"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- MOSTRAR ENTRADAS POR FECHA -->
                <b-col>
                    <b-row>
                        <b-col sm="1"><label>De:</label></b-col>
                        <b-col sm="10">
                            <b-input type="date" v-model="inicio" @change="porFecha()" :state="stateDate"></b-input>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="1"><label>A:</label></b-col>
                        <b-col sm="10">
                            <b-input type="date" v-model="final" @change="porFecha()"></b-input>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <br>
            <b-row>
                <b-col sm="5">
                    <!-- PAGINACION -->
                    <b-pagination
                        v-model="currentPage"
                        :total-rows="entradas.length"
                        :per-page="perPage"
                        aria-controls="my-table"
                        v-if="entradas.length > 0">
                    </b-pagination>
                </b-col>
                <b-col class="text-right">
                    <b-button
                        v-if="entradas.length > 0"
                        variant="dark"
                        :href="`/downEntradasEXC/${inicio}/${final}/${editorial}/general`">
                        <i class="fa fa-download"></i> General
                    </b-button>
                    <!-- <b-button
                        v-if="entradas.length > 0"
                        variant="dark"
                        :href="`/downEntradas/${inicio}/${final}/${editorial}`">
                        <i class="fa fa-download"></i> PDF
                    </b-button> -->
                    <b-button
                        v-if="entradas.length > 0"
                        variant="dark"
                        :href="`/downEntradasEXC/${inicio}/${final}/${editorial}/detallado`">
                        <i class="fa fa-download"></i> Detallado
                    </b-button>
                </b-col>
                <!-- <b-col sm="3" class="text-right">
                    <b-button variant="success" @click="nuevaEntrada()"><i class="fa fa-plus"></i> Nueva entrada</b-button>
                </b-col> -->
            </b-row>
            <!-- LISTADO DE ENTRADAS -->
            <b-table v-if="entradas.length > 0" responsive 
                :items="entradas" :fields="fields"
                :tbody-tr-class="rowClass" id="my-table"
                :per-page="perPage" :current-page="currentPage">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(total)="row">
                    <div v-if="row.item.folio != '05'">
                        ${{ row.item.total | formatNumber }}
                    </div>
                </template>
                <template v-slot:cell(total_pagos)="row">
                    <div v-if="row.item.folio != '05'">
                        ${{ row.item.total_pagos | formatNumber }}
                    </div>
                </template>
                <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                <template v-slot:cell(total_pendiente)="row">
                    <div v-if="row.item.folio != '05'">
                        ${{ (row.item.total - (row.item.total_pagos + row.item.total_devolucion)) | formatNumber }}
                    </div>
                </template>
                <template v-slot:cell(detalles)="row">
                    <b-button variant="info" @click="detallesEntrada(row.item)">Detalles</b-button>
                </template>
                <template v-slot:cell(created_at)="row">
                    {{ row.item.created_at | moment }}
                </template>
                <template v-slot:cell(editar)="row">
                    <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 6) && row.item.total == 0"
                        @click="editarEntrada(row.item, row.index)"
                        style="color:white;" variant="warning"> 
                        <i class="fa fa-pencil"></i>
                    </b-button>
                </template>
                <template v-slot:cell(devolucion)="row">
                    <b-button
                        v-if="(role_id === 1 || role_id == 2 || role_id == 6) &&
                        (row.item.unidades_devolucion < row.item.unidades)"
                        @click="registrarDevolucion(row.item, row.index)"
                        variant="primary">Devolución
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ total_unidades | formatNumber }}</th>
                        <th>{{ total_udevolucion | formatNumber }}</th>
                        <th>${{ total | formatNumber }}</th>
                        <th>${{ total_pagos | formatNumber }}</th>
                        <th>${{ total_devolucion | formatNumber }}</th>
                        <th>${{ total_pendiente | formatNumber }}</th>
                        <th colspan="2"></th>
                    </tr>
                </template>
            </b-table>
            <div v-if="entradas.length === 0">
                <hr>
                <b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron registros</b-alert>
            </div>
        </div> 
        <!-- MOSTRAR DETALLES DE LA ENTRADA -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col sm="5">
                    <label><b>Folio:</b> {{entrada.folio}}</label><br>
                    <label><b>Fecha de creación:</b> {{entrada.created_at | moment}}</label>
                </b-col>
                <b-col>
                    <b-button variant="info" v-b-modal.modal-Mpagos v-if="entrada.total_pagos > 0">
                        Mostrar pagos
                    </b-button>
                </b-col>
                <b-col class="text-right">
                    <b-button 
                        variant="dark"
                        :href="'/downloadEntrada/' + entrada.id">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
                <b-col class="text-right">
                    <b-button 
                        variant="secondary" 
                        @click="mostrarDetalles = false; listadoEntradas = true;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <label><b>Editorial:</b> {{entrada.editorial}}</label>
            <b-table v-if="registros.length > 0" :items="registros" :fields="fieldsR">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ entrada.unidades | formatNumber }}</th>
                        <th>${{ entrada.total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div class="mt-5" v-if="entdevoluciones.length > 0">
                <b-row>
                    <b-col><h6><b>DEVOLUCIONES</b></h6></b-col>
                    <b-col sm="2" class="text-right">
                        <b-button v-if="checkBtnEnviar && (role_id === 1 || role_id == 2 || role_id == 6)" 
                            variant="primary" pill :disabled="load"
                            @click="sendDevolucion(entrada.id)">
                            <i class="fa fa-angle-right"></i> Enviar
                        </b-button>
                    </b-col>
                </b-row>
                <b-table :items="entdevoluciones" :fields="fieldsRD">
                    <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                    <template v-slot:cell(isbn)="row">{{ row.item.registro.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.registro.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.registro.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                    <template v-slot:cell(estado)="row">
                        <b-badge v-if="row.item.estado == 'proceso'" variant="secondary">
                            <i class="fa fa-close"></i>
                        </b-badge>
                        <b-badge v-else variant="success">
                            <i class="fa fa-check"></i>
                        </b-badge>
                    </template>
                    <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ entrada.unidades_devolucion | formatNumber }}</th>
                        <th>${{ entrada.total_devolucion | formatNumber }}</th>
                    </tr>
                </template>
                </b-table>
            </div>
            <!-- MODAL PARA MOSTRAR LOS PAGOS -->
            <b-modal id="modal-Mpagos" hide-footer :title="`Pagos de la entrada con folio ${entrada.folio}`">
                <label><b>Total:</b> ${{ entrada.total | formatNumber }}</label><br>
                <label><b>Total pendiente:</b> ${{ entrada.total - entrada.total_pagos | formatNumber }}</label><br>
                <b-table :items="pagos" :fields="fieldsP">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(pago)="row">
                        ${{ row.item.pago | formatNumber }}
                    </template>
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at | moment }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="1"></th>
                            <th>${{ entrada.total_pagos | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </b-modal>
        </div>
        <!-- AGREGAR COSTOS A LOS DATOS DE LA ENTRADA -->
        <div v-if="mostrarAddCostos">
            <b-row>
                <b-col sm="3">
                    <label><b>Folio:</b> {{entrada.folio}}</label><br>
                    <label><b>Editorial:</b> {{entrada.editorial}}</label>
                </b-col>
                <b-col sm="3" align="right">
                    <label><b>Unidades:</b> {{ total_unidades | formatNumber }}</label>
                </b-col>
                <b-col sm="4" class="text-right">
                    <b-button 
                        @click="confirmarAct()" 
                        variant="success"
                        :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar cambios' : 'Guardando' }}
                    </b-button>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="secondary" @click="mostrarAddCostos = false; listadoEntradas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="registros" :fields="fieldsR">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">
                    <b-input 
                        :id="`input-${row.index}`"
                        type="number" 
                        placeholder="Costo unitario"
                        @change="verificarUnidades(row.item.unidades, row.item.costo_unitario, row.index)" 
                        v-model="row.item.costo_unitario">
                    </b-input>
                </template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5">&nbsp;</th>
                        <th>${{ subtotal | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <!-- MODAL PARA CONFIRMAR LOS DATOS -->
            <b-modal ref="modal-confirmarAct" size="lg" title="Resumen de la entrada">
                <b-row>
                    <b-col sm="6">
                        <label><b>Folio:</b> {{entrada.folio}}</label><br>
                        <label><b>Editorial:</b> {{entrada.editorial}}</label>
                    </b-col>
                    <b-col sm="3" align="right">
                        <label><b>Unidades:</b> {{ total_unidades | formatNumber }}</label>
                    </b-col>
                </b-row>
                <b-table :items="registros" :fields="fieldsR">
                    <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                    <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="5">&nbsp;</th>
                            <th>${{ subtotal | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="9">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar los datos de la entrada.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después <b>no se podrán realizar cambios</b>.
                            </b-alert>
                        </b-col>
                        <b-col sm="3" align="right">
                            <b-button :disabled="load" @click="actualizarCosto()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- NUEVA REMISION-->
        <div v-if="mostrarAdd">
            <add-edit-entrada :agregar="agregar" :form="form" 
                :editoriales="editoriales" @goBack="goBack"></add-edit-entrada>
        </div>
        <!-- AGREGAR DEVOLUCION -->
        <div v-if="mostrarDevolucion">
            <b-row class="mb-2">
                <b-col><h4 style="color: #170057">Registrar devolución</h4></b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="secondary" pill @click="mostrarDevolucion = !mostrarDevolucion; listadoEntradas = true;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <devolucion-entrada :formDev="formDev"></devolucion-entrada>
        </div>
        <!-- MODALS -->
        <!-- MODAL DE AYUDA-->
        <b-modal id="modal-ayudaEG" hide-backdrop hide-footer title="Ayuda">
            <h5 id="titleA"><b>Búsqueda por folio</b></h5>
            <p>Escribir el folio (completo) y presionar <label id="ctrlS">Enter</label>.</p>
            <h5 id="titleA"><b>Búsqueda por editorial</b></h5>
            <p>Elegir la editorial que desee y aparecerán todas los entradas relacionadas a esta.</p>
            <h5 id="titleA"><b>Búsqueda por fecha</b></h5>
            <p>Elegir fecha de inicio y fecha final para buscar las entradas en ese rango de fechas.</p>
            <h5 id="titleA"><b>Búsqueda por editorial y fechas</b></h5>
            <p>Elegir la editorial entre las opciones que aparecen y después seleccionar el rango de fechas.</p>
            <h5 id="titleA"><b>Descargar reporte</b></h5>
            <p>La lista que se descargue será de acuerdo a la búsqueda realizada. Se puede descargar en PDF o EXCEL.</p>
            <h5 id="titleA"><b>Detalles de la entrada</b></h5>
            <p>Se mostraran los registros de la entrada, así como un botón para descargar dicha entrada y otro para mostrar los pagos (En caso de que tenga).</p>
        </b-modal>
    </div>
</template>

<script>
import AddEditEntrada from './partials/AddEditEntrada.vue';
import DevolucionEntrada from './partials/DevolucionEntrada.vue';
    export default {
    components: { AddEditEntrada, DevolucionEntrada },
        props: ['role_id', 'registersall', 'editoriales'],
        data() {
            return {
                entradas: this.registersall,
                registros: [],
                editorial: 'TODAS',
                fieldsP: [
                    {key: 'index', label: 'No.'},
                    'pago',
                    {key: 'created_at', label: 'Fecha de pago'},
                ],
                fields: [
                    {key: 'index', label: 'N.'}, 
                    'folio',
                    {key: 'created_at', label: 'Fecha'},
                    'editorial', 'unidades',
                    {key: 'unidades_devolucion', label: 'Unidades (Devolución)'},
                    'total',
                    {key: 'total_pagos', label: 'Pagos'},
                    {key: 'total_devolucion', label: 'Devolución'},
                    {key: 'total_pendiente', label: 'Pagar'},
                    {key: 'detalles', label: ''},
                    {key: 'devolucion', label: ''},  
                    {key: 'editar', label: ''}
                ],
                fieldsR: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'isbn', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'}
                ],
                fieldsRD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'isbn', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'},
                    {key: 'created_at', label: 'Fecha (Devolución)'},
                    {key: 'estado', label: 'Enviado'}
                ],
                fieldsRP: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'isbn', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    {key: 'unidades_pendientes', label: 'Unidades pendientes'},,
                    {key: 'unidades_base', label: 'Unidades'},
                    {key: 'total_base', label: 'Subtotal'},
                ],
                options: [],
                mostrarDetalles: false,
                entrada: {
                    id: 0,
                    folio: '',
                    editorial: '',
                    total: 0,
                    total_pagos: 0,
                    total_devolucion: 0,
                    total_pendiente: 0,
                    unidades: 0,
                    unidades_devolucion: 0,
                    created_at: '',
                    items: []
                },
                total: 0,
                total_pagos: 0,
                total_pendiente: 0,
                total_devolucion: 0,
                repayment: {
                    entrada_id: 0,
                    pago: null
                },
                mostrarAddCostos: false,
                resultslibros: [],
                unidades: 0,
                load: false,
                posicion: null,
                listadoEntradas: true,
                estado: false,
                inicio: '0000-00-00',
                final: '0000-00-00',
                state: null,
                pagosGuardados: false,
                pagos: [],
                subtotal: 0,
                perPage: 10,
                currentPage: 1,
                loadRegisters: false,
                stateDate: null,
                folio: '',
                total_unidades: 0,
                entdevoluciones: [],
                agregar: false,
                form: {
                    id: 0,
                    unidades: 0,
                    folio: null,
                    editorial: null,
                    registros: []
                },
                mostrarAdd: false,
                mostrarDevolucion: false,
                formDev: {},
                total_udevolucion: 0,
                checkBtnEnviar: false
            }
        },
        created: function(){
            this.acumular();
            this.assign_editorial();
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        }, 
        methods: {
            assign_editorial(){
                this.options.push({
                    value: 'TODAS',
                    text: 'MOSTRAR TODO'
                });
                this.editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });
            },
            confirmarAct(){
                if(this.subtotal > 0){
                    this.estado = false;
                    this.registros.forEach(registro => {
                        if(registro.costo_unitario == 0){
                            this.estado = true;
                        }
                    });
                    if(this.estado == true){
                        this.makeToast('warning', 'El costo unitario no puede ser 0');
                    }
                    else{
                        this.$refs['modal-confirmarAct'].show();
                    }
                } else {
                    this.makeToast('warning', 'El subtotal no puede ser cero. Favor de agregar costo unitario.');
                }
            },
            // BUSCAR ENTRADA POR FOLIO
            porFolio(){
                axios.get('/buscarFolio', {params: {folio: this.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.entradas = [];
                        this.entradas.push(response.data);
                        this.acumular();
                    }
                    else{
                        this.makeToast('warning', 'El folio no existe');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR ENTRADAS POR EDITORIAL
            mostrarEditoriales(){
                axios.get('/mostrarEditoriales', {params: {editorial: this.editorial}}).then(response => {
                    this.entradas = response.data;
                    this.acumular();
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR ENTRADAS POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00') {
                        this.stateDate = null;
                        axios.get('/fecha_entradas', {params: {inicio: this.inicio, final: this.final, editorial: this.editorial}}).then(response => {
                            this.entradas = response.data;
                            this.acumular();
                        }).catch(error => {
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            // MOSTRAR DETALLES DE UNA ENTRADA
            detallesEntrada(entrada){
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    this.asignar(response);
                    this.mostrarDetalles = true;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // AGREGAR COSTOS A LOS DATOS DE LA ENTRADA
            editarEntrada(entrada, i){
                this.posicion = i;
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    if(response.data.entrada.total === 0){
                        this.asignar(response);
                        this.subtotal = 0;
                        this.total_unidades = this.entrada.unidades;
                        this.mostrarAddCostos = true; 
                    } else {
                        this.makeToast('warning', 'La entrada ya fue editada. Actualizar la pagina para visualizar cambios.');
                    }
                });
            },
            // REGISTRAR PAGO DE LA ENTRADA
            // registrarPago(entrada, i){
            //     this.posicion = i;
            //     this.repayment.entrada_id = entrada.id;
            //     this.entrada.total = entrada.total;
            //     this.entrada.total_pagos = entrada.total_pagos;
            //     this.entrada.total_pendiente = this.entrada.total - this.entrada.total_pagos;
            // },
            // GUARDAR COSTOS DE LA ENTRADA
            actualizarCosto(){
                this.load = true;
                this.entrada.items = this.registros;
                axios.put('/entradas/update_costos', this.entrada).then(response => {
                    this.$refs['modal-confirmarAct'].hide();
                    this.makeToast('success', 'La entrada se ha actualizado');
                    this.entradas[this.posicion].total = response.data.total;
                    this.acumular();
                    this.load = false;
                    this.mostrarAddCostos = false;
                    this.listadoEntradas = true;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            verificarUnidades(unidades, costo_unitario, i){
                if(costo_unitario > 0){
                    this.registros[i].total = unidades * costo_unitario;
                    if(i + 1 < this.registros.length){
                        document.getElementById('input-'+(i+1)).focus();
                        document.getElementById('input-'+(i+1)).select();
                    }
                }
                else{
                    this.makeToast('warning', 'Costo unitario invalido');
                    this.registros[i].costo_unitario = 0;
                    this.registros[i].total = 0;
                }
                // SUMAR TODO LO QUE SE VAYA EDITANDO DE LA ENTRADA
                this.sumatoriaSubtotal();
            },
            // GUARDAR PAGO DE ENTRADAS
            guardarVendidos(){
                if(this.repayment.pago > 0){
                    if(this.repayment.pago <= this.entrada.total_pendiente){
                        this.state = null;
                        this.load = true;
                        axios.put('/pago_entrada', this.repayment).then(response => {
                            this.makeToast('success', 'El pago se guardo correctamente');
                            this.load = false;
                            this.repayment = {
                                entrada_id: 0,
                                pago: null
                            };
                            this.$bvModal.hide('modal-registrarPago');
                            this.entradas[this.posicion].total_pagos = response.data.total_pagos;
                            this.acumular();

                        }).catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    }
                    else{
                        this.state = false;
                        this.makeToast('warning', 'El pago es mayor al total pendiente');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'El pago tiene que ser mayor a 0');
                }
            },
            rowClass(item, type) {
                if (!item) return
                if (item.total_pagos > 0 && item.total_pagos == item.total) return 'table-success'
                if (item.total == 0) return 'table-warning'
            },
            asignar(response){
                this.entrada = {};
                this.entrada.id = response.data.entrada.id;
                this.entrada.folio = response.data.entrada.folio;
                this.entrada.editorial = response.data.entrada.editorial;
                this.entrada.total = response.data.entrada.total;
                this.entrada.total_pagos = response.data.entrada.total_pagos;
                this.entrada.total_pendiente = this.entrada.total - this.entrada.total_pagos;
                this.entrada.total_devolucion = response.data.entrada.total_devolucion;
                this.entrada.unidades = response.data.entrada.unidades;
                this.entrada.unidades_devolucion = response.data.entrada.unidades_devolucion;
                this.entrada.created_at = response.data.entrada.created_at;
                this.registros = response.data.entrada.registros;
                this.pagos = response.data.entrada.repayments;
                this.entdevoluciones = response.data.entdevoluciones;
                
                this.checkBtnEnviar = false;
                this.entdevoluciones.forEach(ed => {
                    if(ed.estado == 'proceso') this.checkBtnEnviar = true;
                });

                this.listadoEntradas = false;
            },
            acumular(){
                this.total = 0;
                this.total_pagos = 0;
                this.total_devolucion = 0;
                this.total_pendiente = 0;
                this.total_unidades = 0;
                this.total_udevolucion = 0;
                this.entradas.forEach(entrada => {
                    this.total += entrada.total;
                    this.total_pagos += entrada.total_pagos;
                    this.total_devolucion += entrada.total_devolucion;
                    this.total_pendiente += entrada.total - entrada.total_pagos;
                    this.total_unidades += entrada.unidades;
                    this.total_udevolucion += entrada.unidades_devolucion;
                });
            },
            sumatoriaSubtotal(){
                this.subtotal = 0;
                this.registros.forEach(registro => {
                    this.subtotal += registro.total;
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            // INICIALIZAR PARA CREAR UNA ENTRADA
            nuevaEntrada(){
                this.listadoEntradas = false;
                this.agregar = true;
                this.form = {
                    id: 0,
                    unidades: 0,
                    folio: null,
                    editorial: null,
                    registros: []
                };
                this.mostrarAdd = true;
            },
            goBack(){
                this.listadoEntradas = true;
                this.agregar = false;
                this.mostrarAdd = false;
            },
            registrarDevolucion(entrada, index){
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    this.listadoEntradas = false;
                    this.mostrarDevolucion = true;
                    this.formDev = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            sendDevolucion(entrada_id){
                this.load = true;
                let form = { entrada_id: entrada_id };
                axios.put('/entradas/send_devoluciones', form).then(response => {
                    swal("OK", "La devolución se envió correctamente.", "success")
                        .then((value) => { location.reload(); });
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
        }
    }
</script>