<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoPromociones">
            <b-row>
                <!-- BUSCAR PROMOCION POR FOLIO -->
                <b-col sm="3">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-folio">Folio</label>
                        </b-col>
                        <b-col sm="10">
                            <b-form-input 
                                style="text-transform:uppercase;"
                                id="input-folio" 
                                v-model="folio" 
                                @keyup.enter="porFolio()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- BUSCAR PROMOCION POR PLANTEL -->
                <b-col sm="5">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-plantel">Plantel</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input style="text-transform:uppercase;" 
                                v-model="queryPlantel" @keyup="porPlantel()">
                            </b-input>
                        </b-col>
                    </b-row>
                </b-col> 
                <!-- CREAR UNA PROMOCION -->
                <b-col sm="4">
                    <b-row>
                        <b-col sm="3">
                            <label for="input-inicio">De:</label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                :state="stateDate"
                                v-model="inicio"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3">
                            <label for="input-final">A: </label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                v-model="final"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                </b-col> 
            </b-row>
            <br>
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="promotionsData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col class="text-right">
                    <a 
                        v-if="promotions.length > 0"
                        class="btn btn-dark"
                        :href="'/download_promotion/' + queryPlantel + '/' + inicio + '/' + final + '/general'">
                        <i class="fa fa-download"></i> General
                    </a>
                    <a 
                        v-if="promotions.length > 0 && (role_id === 1 || role_id === 2 || role_id == 6)"
                        class="btn btn-dark"
                        :href="'/download_promotion/' + queryPlantel + '/' + inicio + '/' + final + '/detallado'">
                        <i class="fa fa-download"></i> Detallado
                    </a>
                </b-col>
                <b-col sm="3" class="text-right">
                    <b-button v-if="role_id === 1 || role_id == 2 || role_id == 5 || role_id == 6" 
                        variant="success" @click="registrarPromocion()">
                        <i class="fa fa-plus"></i> Registrar promoción
                    </b-button>
                </b-col>
            </b-row>
            <!-- LISTADO DE PROMOCIONES -->
            <div v-if="!load">
                <b-table v-if="promotions.length > 0"
                    responsive :items="promotions" :fields="fields" 
                    id="my-table">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                    <template v-slot:cell(detalles)="row">
                        <b-button variant="info" @click="detallesPromotion(row.item)">Detalles</b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="3"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </template>
                </b-table>
                <div v-else>
                    <br><b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron registros</b-alert>
                </div>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
        </div>
        <!-- MOSTRAR DETALLES DE LA PROMOCIÓN -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col>
                    <h6><b>Folio</b>: {{ promocion.folio }}</h6>
                    <h6><b>Plantel</b>: {{ promocion.plantel }}</h6>
                </b-col>
                <b-col>
                    <h6><b>Fecha</b>: {{ promocion.created_at | moment }}</h6>
                    <h6 v-if="promocion.entregado_por !== null">
                        <b>Responsable de la entrega:</b> {{ promocion.entregado_por }}
                    </h6>
                </b-col>
                <b-col sm="2">
                    <b-button variant="dark" :href="`/download_promocion/${promocion.id}`"><i class="fa fa-download"></i> Descargar</b-button>
                </b-col>
                <b-col sm="2" align="right">
                    <b-button variant="secondary" @click="listadoPromociones = true; mostrarDetalles = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <h6 v-if="promocion.descripcion !== null"><b>Descripción</b>: {{ promocion.descripcion }}</h6>
            <b-table :items="promocion.departures" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="3"></th>
                        <th>{{ promocion.unidades }}</th>
                    </tr>
                </template>
            </b-table>
        </div>
        <!-- CREAR UNA PROMOCIÓN -->
        <div v-if="mostrarRegistrar">
            <b-row>
                <b-col sm="6"><h4 style="color: #170057">Registrar promoción</h4></b-col>
                <b-col sm="3" align="right">
                    <b-button variant="success" @click="confirmarPromocion()" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </b-col>
                <b-col sm="3" align="right">
                    <b-button variant="secondary" @click="listadoPromociones = true; mostrarRegistrar = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-row>
                        <b-col sm="3"><label><b>Plantel</b>: <b id="txtObligatorio">*</b></label></b-col>
                        <b-col>
                            <b-input style="text-transform:uppercase;" type="text" autofocus v-model="promocion.plantel" :state="state"></b-input>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3"><label><b>Descripción (Opcional)</b>:</label></b-col>
                        <b-col>
                            <b-input style="text-transform:uppercase;" type="text" v-model="promocion.descripcion"></b-input>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col>
                    <b-row>
                        <b-col sm="6"><label><b>Responsable de la entrega</b>: <b id="txtObligatorio">*</b></label></b-col>
                        <b-col>
                            <b-form-select :state="stateResp" v-model="promocion.entregado_por" :options="options"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="registros" :fields="fieldsR">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                <template v-slot:cell(eliminar)="row">
                    <b-button variant="danger" @click="eliminarRegistro(row.index)">
                        <i class="fa fa-minus-circle"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                        <tr>
                            <th colspan="1"></th>
                            <th>ISBN</th>
                            <th>Libro</th>
                            <th>Unidades</th>
                        </tr>
                        <tr>
                            <th colspan="1"></th>
                            <th>
                                <b-input
                                    id="input-isbn"
                                    autofocus
                                    v-model="temporal.ISBN"
                                    @keyup.enter="buscarLibroISBN()"
                                    v-if="inputISBN"
                                    :disabled="load"
                                ></b-input>
                                <label v-if="!inputISBN">{{ temporal.ISBN }}</label>
                            </th>
                            <th>
                                <b-input
                                    style="text-transform:uppercase;"
                                    id="input-libro"
                                    autofocus
                                    v-model="temporal.titulo"
                                    @keyup="mostrarLibros()"
                                    v-if="inputLibro"
                                    :disabled="load">
                                </b-input>
                                <div class="list-group" v-if="resultslibros.length" id="listaBL">
                                    <a 
                                        href="#" 
                                        v-bind:key="i" 
                                        class="list-group-item list-group-item-action" 
                                        v-for="(libro, i) in resultslibros" 
                                        @click="datosLibro(libro)">
                                        {{ libro.titulo }}
                                    </a>
                                </div>
                                <label v-if="!inputLibro">{{ temporal.titulo }}</label>
                            </th>
                            <th>
                                <b-form-input 
                                    id="input-unidades"
                                    autofocus
                                    @keyup.enter="guardarRegistro()"
                                    v-if="inputUnidades"
                                    v-model="temporal.unidades" 
                                    type="number"
                                    required
                                    :disabled="load">
                                </b-form-input>
                            </th>
                            <th>
                                <b-button 
                                    variant="secondary"
                                    @click="eliminarTemporal()" 
                                    v-if="inputUnidades"
                                    :disabled="load">
                                    <i class="fa fa-minus-circle"></i>
                                </b-button>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="3"></th>
                            <th>{{ unidades_crear | formatNumber }}</th>
                            <th></th>
                        </tr>
                </template>
            </b-table>
            <!-- RESUMEN DE LA PROMOCIÓN -->
            <b-modal ref="modal-confirmar-promocion" size="xl" title="Resumen de la promocion">
                <label>
                    <b>Plantel: </b><label style="text-transform:uppercase;">{{ promocion.plantel }}</label>
                </label><br>
                <label v-if="promocion.descripcion !== ''">
                    <b>Descripción: </b><label style="text-transform:uppercase;">{{ promocion.descripcion }}</label><br>
                </label><br>
                <label
                    ><b>Responsable de la entrega: </b> {{ promocion.entregado_por }}
                </label>
                <b-table :items="registros" :fields="fieldsR">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="3"></th>
                            <th>{{ unidades_crear | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la promoción.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                             <b-button variant="success" @click="guardarPromocion()" :disabled="load">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
import setResponsables from '../../mixins/setResponsables';
import getLibros from '../../mixins/getLibros';
    export default {
        props: ['role_id'],
        mixins: [setResponsables,getLibros],
        data() {
            return {
                listadoPromociones: true,
                mostrarRegistrar: false,
                promotions: [],
                fields: [
                    {key: 'index', label: 'N.'}, 
                    'folio', 
                    'plantel', 
                    'unidades',
                    {key: 'created_at', label: 'Fecha de creación'},
                    {key: 'detalles', label: ''}
                ],
                load: false,
                registros: [],
                fieldsR: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades',
                    {key: 'eliminar', label: ''}
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades'
                ],
                options: [],
                temporal: {
                    id: 0,
                    ISBN: '',
                    titulo: '',
                    unidades: null,
                    piezas: 0
                },
                promocion: {
                    id: null,
                    folio: '',
                    plantel: '',
                    descripcion: '',
                    unidades: 0,
                    created_at: '',
                    departures: [],
                    entregado_por: null
                },
                inputISBN: true,
                inputLibro: true,
                inputUnidades: false,
                state: null,
                mostrarDetalles: false,
                folio: null,
                queryPlantel: null,
                perPage: 10,
                currentPage: 1,
                loadRegisters: false,
                stateDate: null,
                inicio: '0000-00-00',
                final: '0000-00-00',
                total_unidades: 0,
                unidades_crear: 0,
                stateResp: null,
                promotionsData: {},
                searchPlantel: false,
                searchFecha: false
            }
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        mounted: function(){
            this.getResults();
        },
        methods: {
            getResults(page = 1){
                if(!this.searchPlantel && !this.searchFecha) this.http_promociones(page);
                if(this.searchPlantel) this.http_plantel(page);
                if(this.searchFecha) this.http_fecha(page);
            },
            http_promociones(page = 1){
                this.load = true;
                axios.get(`/promotions/index?page=${page}`).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.acumular_unidades();
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSQUEDA POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00'){
                        this.http_fecha();
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            http_fecha(page = 1){
                this.load = true;
                axios.get(`/buscar_fecha_promo?page=${page}`, {
                    params: {inicio: this.inicio, final: this.final, plantel: this.queryPlantel}}).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.acumular_unidades();
                    this.set_search(false, true);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR PROMOCIÓN POR FOLIO
            porFolio(){
                axios.get('/buscar_folio_promo', {params: {folio: this.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.promotionsData = {};
                        this.promotions = [];
                        this.promotions.push(response.data);
                        this.acumular_unidades();
                    }
                    else{
                        this.makeToast('warning', 'El folio no existe');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR POR PLANTEL
            porPlantel(){
                if(this.queryPlantel !== null){
                    if(this.queryPlantel.length > 0){
                        this.http_plantel();
                    } else {
                        this.queryPlantel = null;
                    }
                }
            },
            http_plantel(page = 1){
                this.load = true;
                axios.get(`/buscar_plantel?page=${page}`, {params: {queryPlantel: this.queryPlantel}}).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.acumular_unidades();
                    this.set_search(true, false);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            set_search(searchPlantel, searchFecha){
                this.searchPlantel = searchPlantel;
                this.searchFecha = searchFecha;
            },
            // INICIALIZAR PARA CREAR UNA PROMOCIÓN
            registrarPromocion(){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.options = this.assign_responsables(this.options, response.data);
                    this.listadoPromociones = false;
                    this.eliminarTemporal();
                    this.promocion = {
                        id: null,
                        folio: '',
                        plantel: '',
                        descripcion: '',
                        unidades: 0,
                        created_at: '',
                        departures: [],
                        entregado_por: null
                    };
                    this.state = null;
                    this.registros = [];
                    this.mostrarRegistrar = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            // MOSTRAR DETALLES DE LA PROMOCIÓN
            detallesPromotion(promotion){
                this.promocion.departures = [];
                axios.get('/obtener_departures', {params: {promotion_id: promotion.id}}).then(response => {
                    this.promocion.id = response.data.id;
                    this.promocion.departures = response.data.departures;
                    this.promocion.folio = response.data.folio;
                    this.promocion.plantel = response.data.plantel;
                    this.promocion.unidades = response.data.unidades;
                    this.promocion.descripcion = response.data.descripcion;
                    this.promocion.created_at = response.data.created_at;
                    this.promocion.entregado_por = response.data.entregado_por;
                    this.listadoPromociones = false;
                    this.mostrarDetalles = true;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MODAL PARA CONFIRMAR LA PROMOCIÓN
            confirmarPromocion(){
                if(this.promocion.plantel.length > 3){
                    this.state = true;
                    if(this.promocion.entregado_por !== null){
                        this.stateResp = true;
                        if(this.registros.length > 0){
                            this.$refs['modal-confirmar-promocion'].show();
                        } else {
                            this.makeToast('warning', 'Aun no se ha agregado un libro a la promoción.');
                        }
                    } else {
                        this.stateResp = false;
                        this.makeToast('warning', 'Elegir el responsable de la entrega.');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, mayor a 4 caracteres.');
                }
            },
            // GUARDAR LA PROMOCIÓN
            guardarPromocion(){
                if(this.promocion.plantel.length > 3){
                    this.state = true;
                    if(this.promocion.entregado_por !== null){
                        this.stateResp = true;
                        this.load = true;
                        this.promocion.departures = this.registros;
                        axios.post('/guardar_promocion', this.promocion).then(response => {
                            this.load = false;
                            this.promotions.unshift(response.data);
                            this.acumular_unidades();
                            this.makeToast('success', 'La promoción se guardo correctamente.');
                            this.$refs['modal-confirmar-promocion'].hide();
                            this.mostrarRegistrar = false;
                            this.listadoPromociones = true;
                        })
                        .catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    } else {
                        this.stateResp = false;
                        this.makeToast('warning', 'Elegir el responsable de la entrega.');
                    }
                    
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, mayor a 4 caracteres.');
                }
            },
            // ELIMINAR REGISTRO DEL ARRAY
            eliminarRegistro(i){
                this.registros.splice(i, 1);
                this.acum_unidades_crear();
            },
            // BUSCAR LIBRO POR ISBN
            buscarLibroISBN(){
                axios.get('/buscarISBN', {params: {isbn: this.temporal.ISBN}}).then(response => {
                    this.temporal.id = response.data.id;
                    this.temporal.ISBN = response.data.ISBN;
                    this.temporal.titulo = response.data.titulo;
                    this.temporal.piezas = response.data.piezas;
                    this.inputISBN = false;
                    this.inputLibro = false;
                    this.inputUnidades = true;
                }).catch(error => {
                    this.makeToast('danger', 'ISBN incorrecto');
                });
            },
            // MOSTRAR COINCIDENCIA DE LIBROS
            mostrarLibros(){
                if(this.temporal.titulo.length > 0){
                    this.getLibros(this.temporal.titulo);
               }
            },
            // SELECCIONAR LIBRO
            datosLibro(libro){
                this.temporal = {
                    id: libro.id,
                    ISBN: libro.ISBN,
                    titulo: libro.titulo,
                    unidades: null,
                    piezas: libro.piezas
                };
                this.resultslibros = [];
                this.inputISBN = false;
                this.inputLibro = false;
                this.inputUnidades = true;
            },
            // VERIFICAR UNIDADES
            guardarRegistro(){
                if(this.temporal.unidades > 0){
                    if(this.temporal.unidades <= this.temporal.piezas){
                        this.registros.push(this.temporal);
                        this.acum_unidades_crear();
                        this.eliminarTemporal();
                    }
                    else{
                        this.makeToast('warning', `${this.temporal.piezas} unidades en existencia`);
                    }
                }
                else{
                    this.makeToast('warning', 'Unidades invalidas');
                }
            },
            // ELIMINAR REGISTRO TEMPORAL
            eliminarTemporal(){
                this.temporal = {
                    id: 0,
                    ISBN: '',
                    titulo: '',
                    unidades: null,
                    piezas: 0
                };
                this.inputUnidades = false;
                this.inputLibro = true;
                this.inputISBN = true;
            },
            acumular_unidades(){
                this.total_unidades = 0;
                this.promotions.forEach(promotion => {
                    this.total_unidades += promotion.unidades;
                });
            },
            acum_unidades_crear(){
                this.unidades_crear = 0;
                this.registros.forEach(registro => {
                    this.unidades_crear += parseInt(registro.unidades);
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            }
        }
    }
</script>

<style>
    #txtObligatorio {
        color: red;
    }
    #listaBL{
        position: absolute;
        z-index: 100
    }
</style>