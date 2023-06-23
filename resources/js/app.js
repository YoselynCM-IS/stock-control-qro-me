/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.moment = require('moment');
window.numeral = require('numeral');

// Vue.use(require('vue-resource'));

import Vue from 'vue'
import BootstrapVue from 'bootstrap-vue'

Vue.use(BootstrapVue)
Vue.component('pagination', require('laravel-vue-pagination'));


// app.js
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

// import Datepicker from 'vuejs-datepicker';

import VueResource from 'vue-resource';
Vue.use(VueResource);

//necesario para http post, put, delete channel routes
Vue.http.interceptors.push((request, next) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    request.headers.set('X-CSRF-TOKEN', csrfToken);
    next();
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

// REMISIONES
Vue.component('listado-component', require('./components/remisiones/ListadoComponent.vue').default);
Vue.component('remision-component', require('./components/remisiones/RemisionComponent.vue').default);
Vue.component('fecha-adeudo-component', require('./components/remisiones/FechaAdeudoComponent.vue').default);
Vue.component('details-remision-component', require('./components/remisiones/DetailsRemisionComponent.vue').default);

// PAGOS
Vue.component('pagos-component', require('./components/pagos/PagosComponent.vue').default);
Vue.component('pagos-devoluciones-component', require('./components/pagos/PagosDevolucionesComponent.vue').default);

// LIBROS
Vue.component('libros-component', require('./components/libros/LibrosComponent.vue').default);
Vue.component('editar-libro-component', require('./components/libros/EditarLibroComponent.vue').default);
Vue.component('new-libro-component', require('./components/libros/NewLibroComponent.vue').default);

// PEDIDOS
Vue.component('pedidos-component', require('./components/pedidos/PedidosComponent.vue').default);

// CLIENTES
Vue.component('clientes-component', require('./components/clientes/ClientesComponent.vue').default);
Vue.component('new-client-component', require('./components/clientes/NewClientComponent.vue').default);

// ENTRADAS
Vue.component('entradas-component', require('./components/entradas/EntradasComponent.vue').default);
Vue.component('editar-entradas-component', require('./components/entradas/EditarEntradasComponent.vue').default);
Vue.component('pagos-entradas-component', require('./components/entradas/PagosEntradasComponent.vue').default);

// NOTAS
Vue.component('new-nota-component', require('./components/notas/NewNotaComponent.vue').default);

// PROMOCIONES
Vue.component('promociones-component', require('./components/promociones/PromocionesComponent.vue').default);

// DONACIONES
Vue.component('donaciones-component', require('./components/donaciones/DonacionesComponent.vue').default);

// MOVIMIENTOS
Vue.component('movunidades-component', require('./components/movimientos/MovUnidadesComponent.vue').default);
Vue.component('movmonto-component', require('./components/movimientos/MovMontoComponent.vue').default);
Vue.component('unidades-component', require('./components/movimientos/UnidadesComponent.vue').default);
Vue.component('unidades-libro-component', require('./components/movimientos/UnidadesLibroComponent.vue').default);
Vue.component('entrada-salida-component', require('./components/movimientos/EntradaSalidaComponent.vue').default);

// // CLIENTES
Vue.component('mov-clientes-component', require('./components/movimientos/clientes/MovClientesComponent.vue').default);


// CORTES
Vue.component('lista-cortes-component', require('./components/cortes/ListaCortesComponent.vue').default);
Vue.component('new-edit-corte-component', require('./components/cortes/NewEditCorteComponent.vue').default);
Vue.component('classify-cortes-component', require('./components/cortes/ClassifyCortesComponent.vue').default);
Vue.component('classify-pagos-component', require('./components/cortes/ClassifyPagosComponent.vue').default);
Vue.component('select-corte-component', require('./components/cortes/SelectCorteComponent.vue').default);
Vue.component('select-corte-pagos-component', require('./components/cortes/SelectCortePagosComponent.vue').default);
Vue.component('details-corte-component', require('./components/cortes/DetailsCorteComponent.vue').default);
// ** PAGOS
Vue.component('lista-pagos-component', require('./components/cortes/pagos/ListaPagosComponent.vue').default);
Vue.component('details-pagos-component', require('./components/cortes/pagos/DetailsPagosComponent.vue').default);

// FUNCIONES
Vue.component('check-connection-component', require('./components/funciones/CheckConnectionComponent.vue').default);
Vue.component('load-component', require('./components/funciones/LoadComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
