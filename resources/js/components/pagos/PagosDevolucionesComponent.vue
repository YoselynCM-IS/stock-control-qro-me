<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="!mostrarDevolucion && !mostrarPagos">
            <b-row>
                <!-- BUSCAR REMISION POR NUMERO -->
                <b-col sm="4"> 
                    <b-row class="my-1">
                        <b-col sm="4">
                            <label for="input-numero">Remisión</label>
                        </b-col>
                        <b-col sm="8">
                            <b-form-input 
                                id="input-numero" 
                                type="number" 
                                v-model="num_remision" 
                                @keyup.enter="porNumero()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- BUSCAR REMISION POR CLIENTE -->
                <b-col sm="6">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-cliente">Cliente</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                                style="text-transform:uppercase;"
                            ></b-input>
                            <div class="list-group" v-if="resultsClientes.length" id="listaD">
                                <a 
                                    href="#" 
                                    v-bind:key="i" 
                                    class="list-group-item list-group-item-action" 
                                    v-for="(result, i) in resultsClientes" 
                                    @click="porCliente(result)">
                                    {{ result.name }}
                                </a>
                            </div>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col sm="2">
                    <!-- <b-button variant="info" pill v-b-modal.modal-ayudaAP><i class="fa fa-info-circle"></i> Ayuda</b-button> -->
                </b-col>
            </b-row> 
            <hr>
            <!-- PAGINACIÓN -->
            <pagination size="default" :limit="1" :data="remisionesData" 
                @pagination-change-page="getResults">
                <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
            </pagination>
            <div v-if="!load">
                <!-- TABLA DE REMISIONES -->
                <b-table v-if="remisiones.length > 0" responsive hover
                    :items="remisiones" :fields="fields">
                    <template v-slot:cell(cliente)="row">{{ row.item.cliente.name }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                    <template v-slot:cell(pagos)="row">${{ row.item.pagos | formatNumber }}</template>
                    <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
                    <!-- <template v-slot:cell(registrar_pago)="row">
                        <b-button 
                            v-if="row.item.total_pagar > 0 && role_id == 6"
                            variant="primary" 
                            @click="registrarPago(row.item, row.index)">Pago
                        </b-button>
                    </template> -->
                    <template v-slot:cell(registrar_devolucion)="row">
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id == 1 || role_id == 2 || role_id == 6)" 
                            variant="dark" 
                            @click="registrarDevolucion(row.item, row.index)">Devolución
                        </b-button>
                    </template>
                    <template v-slot:cell(cerrar_remision)="row">
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id == 1 || role_id == 2 || role_id == 6)" 
                            @click="cerrarRemision(row.item, row.index)"
                            variant="secondary">Cerrar
                        </b-button> 
                    </template>
                </b-table>
                <b-alert v-else show variant="secondary">
                    <i class="fa fa-warning"></i> No se encontraron registros.
                </b-alert>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
            <b-modal ref="modal-registrar-deposito" title="Registrar pago">
                <b-form @submit.prevent="guardarMonto()">
                    <b-row>
                        <b-col class="text-right" sm="3">
                            <label>Pago</label>
                        </b-col>
                        <b-col sm="7">
                            <b-form-input v-model="deposito.monto" autofocus :state="stateM" :disabled="load" required></b-form-input>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col class="text-right" sm="3">
                            <label>Recibí de</label>
                        </b-col>
                        <b-col sm="7">
                            <b-form-select :state="stateR" :disabled="load" v-model="deposito.entregado_por" :options="options"></b-form-select>
                        </b-col>
                    </b-row>
                    <hr>
                    <div class="text-center">
                        <b-button type="submit" variant="success" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                    </div>
                </b-form>
                <div slot="modal-footer">
                    <b-alert show variant="info">
                        <i class="fa fa-exclamation-circle"></i> Verificar el pago antes de presionar <b>Guardar</b>, ya que después <b>no se podrán realizar cambios</b>.
                    </b-alert>
                </div>
            </b-modal>
        </div>
        <!-- REGISTRAR PAGO -->
        <div v-if="mostrarPagos">
            <h4 style="color: #170057">Registro de pago</h4>
            <hr>
            <b-row>
                <b-col sm="6"><h5><b>Remisión No. {{ remision.id }}</b></h5></b-col>
                <b-col sm="3">
                    <div class="text-right">
                        <b-button 
                            :disabled="load" 
                            variant="success"
                            @click="confirmarPagoU()">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-col>
                <b-col sm="3">
                    <div class="text-right">
                        <b-button variant="secondary" @click="mostrarPagos = false">
                            <i class="fa fa-mail-reply"></i> Regresar
                        </b-button>
                    </div>
                </b-col>
            </b-row>
            <label><b>Cliente:</b> {{ remision.cliente.name }}</label>
            <hr>
            <b-row>
                <b-col sm="2">Pago entregado por</b-col>
                <b-col sm="4"><b-form-select :state="state" v-model="entregado_por" :options="options"></b-form-select></b-col>
            </b-row><br>
            <b-table :items="remision.vendidos" :fields="fieldsRP">
                <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(unidades_base)="row">
                    <b-input 
                        :id="`inpVend-${row.index}`"
                        type="number" 
                        :disabled="load"
                        @change="verificarUnidades(row.item.unidades_base, row.item.unidades_resta, row.item.dato.costo_unitario, row.index)" 
                        v-model="row.item.unidades_base"> 
                    </b-input>
                </template>
                <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5"></th>
                        <th>${{ total_vendido | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <!-- MODAL PARA CONFIRMAR PAGO -->
            <b-modal ref="modal-confirmarPagoU" size="xl" title="Resumen del pago">
                <h5><b>Remisión No. {{ remision.id }}</b></h5>
                <label><b>Cliente:</b> {{ remision.cliente.name }}</label><br>
                <label><b>Pago entregado por:</b> {{ entregado_por }}</label>
                <b-table :items="remision.vendidos" :fields="fieldsR">
                    <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>${{ total_vendido | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="9">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar el pago.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="3" align="right">
                            <b-button 
                                :disabled="load" 
                                variant="success"
                                @click="guardarPago()">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- REGISTRAR DATOS DE DEVOLUCION -->
        <div v-if="mostrarDevolucion">
            <h4 style="color: #170057">Registro de devolución</h4>
            <hr>
            <div class="row">
                <div class="col-md-6"><h5><b>Remisión No. {{ remision.id }}</b></h5></div>
                <div class="col-md-3 text-right">
                    <b-button 
                        :disabled="load" 
                        variant="success" 
                        @click="confirmarDevolucion()">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                    <!-- MODAL -->
                    <b-modal ref="modal-confirmarDevolucion" size="xl" title="Resumen de la devolución">
                        <h5><b>Remisión No. {{ remision.id }}</b></h5>
                        <label><b>Cliente:</b> {{ remision.cliente.name }}</label><br>
                        <label><b>Devolución entregada por:</b> {{ entregado_por }}</label>
                        <b-table :items="devoluciones" :fields="fieldsRP">
                            <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                            <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                            <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                            <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                            <template #thead-top="row">
                                <tr>
                                    <th colspan="5"></th>
                                    <th>${{ total_devolucion | formatNumber }}</th>
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
                                    <b-button 
                                        :disabled="load" 
                                        variant="success"
                                        @click="guardar()">
                                        <i class="fa fa-check"></i> Confirmar
                                    </b-button>
                                </b-col>
                            </b-row>
                        </div>
                    </b-modal>
                </div>
                <div class="col-md-3 text-right">
                    <b-button variant="secondary" @click="mostrarDevolucion = false">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </div>
            </div>
            <label><b>Cliente:</b> {{ remision.cliente.name }}</label>
            <hr>
            <b-row>
                <b-col sm="3">Devolución entregada por</b-col>
                <b-col sm="4"><b-form-select :state="state" v-model="entregado_por" :options="options"></b-form-select></b-col>
            </b-row><br>
            <table class="table">
                <thead>
                    <tr>
                        <td></td><td></td>
                        <td></td><td></td><td></td>
                        <td><h6><b>${{ total_devolucion | formatNumber }}</b></h6></td>
                    </tr>
                    <tr>
                        <th scope="col">ISBN</th>
                        <th scope="col">Libro</th>
                        <th scope="col">Costo unitario</th>
                        <th scope="col">Unidades pendientes</th>
                        <th scope="col">Unidades</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(devolucion, i) in devoluciones" v-bind:key="i">
                        <td>{{ devolucion.libro.ISBN }}</td>
                        <td>{{ devolucion.libro.titulo }}</td>
                        <td>$ {{ devolucion.dato.costo_unitario | formatNumber }}</td>
                        <td>{{ devolucion.unidades_resta | formatNumber }}</td>
                        <td>
                            <b-input 
                                :id="`inpDev-${i}`"
                                type="number" 
                                v-model="devolucion.unidades_base"
                                :disabled="load"
                                @change="guardarUnidades(devolucion, i)"/>
                        </td>
                        <td>$ {{ devolucion.total_base | formatNumber }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- INFORMACIÓN ACERCA DEL APARTADO -->
        <b-modal id="modal-ayudaAP" hide-backdrop hide-footer title="Ayuda">
            En este apartado solo aparecerán las remisiones que ya fueron marcadas como entregadas y que aun no han sido terminadas de pagar.
            <hr>
            <h5 id="titleA"><b>Búsqueda de remisiones</b></h5>
            <p>
                <ul>
                    <li>
                        <b>Búsqueda por remisión: </b>
                        Ingresar el número de folio de la remisión que se desea buscar y presionar <label id="ctrlS">Enter</label>.
                    </li>
                    <li><b>Búsqueda por cliente: </b> Escribir el nombre del cliente y elegir entre las opciones que aparezcan.</li>
                </ul>
            </p>
            <hr>
            <h5 id="titleA"><b>Pago</b></h5>
            <p>
                Pago en efectivo, ingresando las unidades que se pagaron.<br>
                En <b id="titleA">Pago entregado por</b> tendrá que seleccionar de quien lo recibió.
            </p>
            <hr>
            <h5 id="titleA"><b>Devolución</b></h5>
            <p>
                Devolución de libros, ingresando las unidades que se regresaron.<br>
                En <b id="titleA">Devolución entregada por</b> tendrá que seleccionar de quien la recibió.
            </p>
            <hr>
            <h5 id="titleA"><b>Donación</b></h5>
            <p>
                Donación de libros, ingresando las unidades que se donaron.<br>
                En <b id="titleA">Donación entregada por</b> tendrá que seleccionar quien la entrego.
            </p>
            <hr>
            <p>Al presionar <b id="titleA">Guardar</b> ya sea en Pago, Devolución o Donación, aparecerá una ventana emergente, donde aparecerán los datos de la remisión y las unidades que ingreso.</p>
            <b><i class="fa fa-info-circle"></i> Nota: </b>Verificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: ['listresponsables', 'role_id'],
        data() {
            return {
                fields: [
                    {key: 'id', label: 'Folio'}, 
                    'cliente', 
                    {key: 'total', label: 'Salida'}, 
                    {key: 'pagos', label: 'Pagado'},
                    {key: 'total_devolucion', label: 'Devolución'},
                    {key: 'total_pagar', label: 'Pagar'},
                    // {key: 'registrar_pago', label: ''},
                    {key: 'registrar_devolucion', label: ''},
                    {key: 'cerrar_remision', label: ''}
                ], // Columnas de la tabla principal donde se muestran las remisiones
                fieldsRP: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades_resta', label: 'Unidades pendientes'},
                    {key: 'unidades_base', label: 'Unidades'}, 
                    'subtotal'
                ], // Columnas donde se muestran los datos de las remisiones
                fieldsR: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    {key: 'unidades_base', label: 'Unidades'}, 
                    'subtotal'
                ],
                mostrarDevolucion: false, // Indicar si se muestra el apartado para registrar devolución
                remision: {}, //Datos de la remision
                devoluciones: [], //Array de las devoluciones
                total_devolucion: 0,
                remisiones: [],
                num_remision: null,
                queryCliente: '',
                resultsClientes: [],
                pos_remision: 0,
                mostrarPagos: false,
                load: false,
                total_vendido: 0,
                pagoRemision: {},
                devolucionRemision: {},
                options: [],
                entregado_por: null,
                state: null,
                stateM: null,
                deposito: {
                    remision_id: null,
                    monto: 0,
                    total_pagar: 0,
                    pagar_restante: 0,
                    entregado_por: null,
                    posicion: null
                },
                stateR: null,
                stateU: null,
                remisionesData: {},
                cliente_id: null
            }
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        created: function(){
            this.getResults();
            this.assign_responsables();
        },
        methods: {
            // OBTENER REMISIONES POR PAGINA
            getResults(page = 1){
                if(this.cliente_id == null)
                    this.http_remisiones(page);
                else
                    this.http_cliente(page); 
            },
            // HTTP REMISIONES
            http_remisiones(page = 1){
                this.load = true;
                axios.get(`/remisiones/pay_remisiones?page=${page}`).then(response => {
                    this.remisionesData = response.data; 
                    this.remisiones = response.data.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // HTTP CLIENTE
            http_cliente(page = 1){
                this.load = true;
                axios.get(`/pagos_remision_cliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                    this.remisionesData = response.data;
                    this.remisiones = response.data.data;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR LOS PAGOS QUE HA REALIZADO EL CLIENTE
            porCliente(cliente){
                this.cliente_id = cliente.id;
                this.resultsClientes = [];
                this.queryCliente = cliente.name;
                this.http_cliente();
            },
            assign_responsables(){
                this.options.push({
                    value: null,
                    text: 'Selecciona una opción',
                    disabled: true
                });
                this.options.push({
                    value: 'CLIENTE',
                    text: 'CLIENTE'
                });
                this.listresponsables.forEach(responsable => {
                    if(responsable.responsable !== 'ARTURO'){
                        this.options.push({
                            value: responsable.responsable,
                            text: responsable.responsable
                        });
                    }
                });
            },
            // BUSCAR REMISIÓN POR NUMERO
            porNumero(){
                if(this.num_remision > 0){
                    axios.get('/buscar_por_numero', {params: {num_remision: this.num_remision}}).then(response => {
                        // if(response.data.remision.estado == 'Iniciado')
                        //     this.makeToast('warning', 'La remisión aún no ha sido marcada como entregada.');
                        if(response.data.remision.estado == 'Cancelado')
                            this.makeToast('warning', 'La remisión esta cancelada.');
                        if(response.data.remision.total_pagar == 0 && (response.data.remision.estado == 'Proceso' || response.data.remision.estado == 'Terminado'))
                            this.makeToast('warning', 'La remisión ya se encuentra pagada. Consultar en el apartado de remisiones.');
                        if(response.data.remision.total_pagar > 0 && response.data.remision.estado != 'Cancelado'){
                            this.remisionesData = {};
                            this.remisiones = [];
                            this.remisiones.push(response.data.remision);
                        }
                    }).catch(error => {
                        this.makeToast('danger', 'Error al consultar el numero de remisión ingresado.');
                    });
                }
            },
            // MOSTRAR CLIENTES
            mostrarClientes(){
                if(this.queryCliente.length > 0){
                    axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                        this.resultsClientes = response.data;
                    }); 
                }
                else{
                    this.resultsClientes = [];
                }
            },
            
            ini_entregado_por(){
                this.state = null;
                this.entregado_por = null;
            },
            // REGISTRAR PAGO DE LA REMISIÓN
            registrarPago(remision, index){
                // RUTA ANTERIOR /get_remcliente
                axios.get('/cortes/show_one', {params: {cliente_id: remision.cliente_id, corte_id: remision.corte_id}}).then(response => {
                    this.deposito = {
                        remision_id: remision.id,
                        total_pagar: remision.total_pagar,
                        pagar_restante: response.data.total_pagar,
                        monto: 0,
                        entregado_por: null,
                        posicion: index
                    };

                    this.stateR = null;
                    this.stateU = null;
                    this.stateM = null;
                    this.$refs['modal-registrar-deposito'].show();
                });
            },
            // GUARDAR EL DEPOSITO DE LA RMEISION
            guardarMonto(){
                if(this.deposito.monto > 0){
                    if((this.deposito.monto <= this.deposito.pagar_restante) && (this.deposito.monto <= this.deposito.total_pagar)){
                        this.stateM = true;
                        if(this.deposito.entregado_por !== null){
                            this.stateR = true;
                            this.load = true; 
                            axios.post('/deposito_remision', this.deposito).then(response => {
                                this.load = false;
                                this.remisiones[this.deposito.posicion].pagos = response.data.pagos;
                                this.remisiones[this.deposito.posicion].total_pagar = response.data.total_pagar;
                                this.makeToast('success', 'El pago se guardo correctamente. Actualizar pagina para poder visualizar los cambios.');
                                this.$refs['modal-registrar-deposito'].hide();
                            }).catch(error => {
                                this.load = false;
                                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                            });
                        } else {
                            this.stateR = false;
                            this.makeToast('warning', 'Seleccionar de quien se recibio el pago.');
                        }
                    } else {
                        this.stateM = false;
                        this.makeToast('warning', 'El pago no puede ser mayor al total a pagar.');
                    }
                } else{
                    this.stateM = false;
                    this.makeToast('warning', 'El pago tiene que ser mayor a 0');
                }
            },
            // MOSTRAR RESUMEN DEL PAGO PARA CONFIRMAR
            confirmarPagoU() {
                if(this.entregado_por != null){
                    this.state = true;
                    if(this.total_vendido > 0){
                        if(this.total_vendido <= this.remisiones[this.pos_remision].total_pagar){
                            this.$refs['modal-confirmarPagoU'].show();
                        } else {    
                            this.makeToast('warning', 'El pago no puede ser guardada. El total es mayor al total por pagar.');
                        }
                    } else {
                        this.makeToast('warning', 'El total debe ser mayor a cero.');
                    }
                } else {
                    this.state = false;
                    this.makeToast('warning', 'Seleccionar la opción de pago entregado por, para poder continuar.');
                }
            },
            // GUARDAR PAGO
            guardarPago () {
                this.ini_vendidos();
                axios.post('/registrar_pago', this.pagoRemision).then(response => {
                    this.remisiones[this.pos_remision].estado = response.data.estado;
                    this.remisiones[this.pos_remision].pagos = response.data.pagos;
                    this.remisiones[this.pos_remision].total_pagar = response.data.total_pagar;
                    this.$refs['modal-confirmarPagoU'].hide();
                    this.makeToast('success', 'El pago se guardo correctamente.');
                    this.mostrarPagos = false;
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // REGISTRAR DEVOLUCIÓN DE LA REMISIÓN
            registrarDevolucion(remision, i){
                this.devoluciones = [];
                this.pos_remision = i;
                this.total_devolucion = 0;
                this.ini_entregado_por();
                axios.get('/lista_datos', {params: {numero: remision.id}}).then(response => {
                    this.devoluciones = response.data.remision.devoluciones;
                    this.remision = remision;
                    this.acumularFinal();
                    this.mostrarDevolucion = true;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR RESUMEN DE LA DEVOLUCIÓN PARA CONFIRMAR
            confirmarDevolucion(){
                if(this.entregado_por != null){
                    this.state = true;
                    if(this.total_devolucion > 0){
                        if(this.total_devolucion <= this.remisiones[this.pos_remision].total_pagar){
                            this.$refs['modal-confirmarDevolucion'].show();
                        } else {    
                            this.makeToast('warning', 'La devolución no puede ser guardada. El total de la devolución es mayor al total por pagar.');
                        }
                    } else {
                        this.makeToast('warning', 'El total debe ser mayor a cero.');
                    }
                } else{
                    this.state = false;
                    this.makeToast('warning', 'Seleccionar la opción de devolución entregada por, para poder continuar.');
                }
            },
            // GUARDAR DEVOLUCIÓN
            guardar(){
                this.load = true;
                this.devolucionRemision.id = this.remision.id;
                this.devolucionRemision.devoluciones = this.devoluciones;
                this.devolucionRemision.entregado_por = this.entregado_por;
                axios.put('/devoluciones/update', this.devolucionRemision).then(response => {
                    this.remisiones[this.pos_remision].estado = response.data.estado;
                    this.remisiones[this.pos_remision].total_devolucion = response.data.total_devolucion;
                    this.remisiones[this.pos_remision].total_pagar = response.data.total_pagar;
                    this.$refs['modal-confirmarDevolucion'].hide();
                    this.mostrarDevolucion = false;
                    this.load = false;
                    this.makeToast('success', 'La devolución se guardo correctamente. Actualizar pagina para poder visualizar los cambios.');
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            ini_vendidos(){
                this.state = null;
                this.load = true;
                this.pagoRemision = {
                    id: this.remision.id,
                    vendidos: this.remision.vendidos,
                    entregado_por: this.entregado_por
                }
            },
            // VERIFICAR LAS UNIDADES INGRESADAS PARA OBTENER EL SUBTOTAL
            verificarUnidades (base, resta, costo, i) {
                if(base < 0){
                    this.makeToast('warning', 'Las unidades no pueden ser menores a cero.');
                    this.remision.vendidos[i].unidades_base = 0;
                    this.remision.vendidos[i].total_base = 0;
                }
                if(base > resta){
                    this.makeToast('warning', 'Las unidades son mayores a las unidades pendientes.');
                    this.remision.vendidos[i].unidades_base = 0;
                    this.remision.vendidos[i].total_base = 0;
                }
                if(base <= resta && base >= 0){
                    this.remision.vendidos[i].total_base = base * costo;
                    if(i + 1 < this.remision.vendidos.length){
                        document.getElementById('inpVend-'+(i+1)).focus();
                        document.getElementById('inpVend-'+(i+1)).select();
                    }
                }
                this.total_vendido = 0;
                this.remision.vendidos.forEach(vendido => {
                    this.total_vendido += vendido.total_base;
                });
            },
            // VERIFICAR LAS UNIDADES INGRESADAS PARA OBTENER EL SUBTOTAL
            guardarUnidades(devolucion, i){
                if(devolucion.unidades_base >= 0){
                    if(devolucion.unidades_base <= devolucion.unidades_resta){
                        this.devoluciones[i].total_base = devolucion.dato.costo_unitario * devolucion.unidades_base;
                        if(i + 1 < this.devoluciones.length){
                            document.getElementById('inpDev-'+(i+1)).focus();
                            document.getElementById('inpDev-'+(i+1)).select();
                        }
                    }
                    else{
                        this.item = devolucion.id;
                        this.makeToast('warning', 'Unidades mayores a unidades pendientes.');
                        this.devoluciones[i].unidades_base = 0;
                        this.devoluciones[i].total_base = 0;
                    }
                }
                else{
                    this.makeToast('warning', 'Las unidades no pueden ser menores a cero');
                    this.devoluciones[i].unidades_base = 0;
                    this.devoluciones[i].total_base = 0;
                }
                this.acumularFinal();
            },
            acumularFinal(){
                this.total_devolucion = 0;
                this.total_pagar = 0;
                this.devoluciones.forEach(devolucion => {
                    this.total_devolucion += devolucion.total_base;
                    this.total_pagar += devolucion.total_resta;
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            // CERARR REMSIÓN
            cerrarRemision(remision, pos){
                this.load = true;
                let form_close = {id: remision.id};
                axios.put('/remisiones/close', form_close).then(response => {
                    this.load = false;
                    this.makeToast('success', 'La remisión se actualizo correctamente.');
                    this.remisiones.splice(pos, 1);
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
        },
    }
</script>

<style>
    #listaD{
        position: absolute;
        z-index: 100
    }
    #listaD a {
        /* background-color: #f2f8ff; */
    }
</style>