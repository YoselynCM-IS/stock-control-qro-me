<?php

use App\Promotion;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

// ADMINISTRADOR
Route::name('administrador.')->prefix('administrador')->middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/remisiones', 'AdministradorController@remisiones')->name('remisiones');
    Route::get('/notas', 'AdministradorController@notas')->name('notas');
    Route::get('/promociones', 'AdministradorController@promociones')->name('promociones');
    Route::get('/entradas', 'AdministradorController@entradas')->name('entradas');
    Route::get('/libros', 'AdministradorController@libros')->name('libros');
    Route::get('/clientes', 'AdministradorController@clientes')->name('clientes');
    Route::get('/pedidos', 'AdministradorController@pedidos')->name('pedidos');
    Route::get('/donaciones', 'AdministradorController@donaciones')->name('donaciones');
    Route::get('/movimientos', 'AdministradorController@movimientos')->name('movimientos');
    Route::get('/movimientos_monto', 'AdministradorController@movimientos_monto')->name('movimientos_monto');
    Route::get('/pagos', 'AdministradorController@pagos')->name('pagos');
    Route::get('/registrar_pago', 'AdministradorController@registrar_pago')->name('registrar_pago');
    Route::get('/fecha-adeudo', 'AdministradorController@fecha_adeudo')->name('fecha-adeudo');

    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/pagos', 'AdministradorController@entradas_pagos')->name('pagos');
    });
    
    Route::get('/unidades', 'AdministradorController@unidades')->name('unidades');
    Route::get('/getUnidades', 'AdministradorController@getUnidades')->name('getUnidades');
    Route::get('/detallesUnidades', 'AdministradorController@detallesUnidades')->name('detallesUnidades');
    Route::get('/download_unidades', 'AdministradorController@download_unidades')->name('download_unidades');

    Route::get('/unidades_libro', 'AdministradorController@unidades_libro')->name('unidades_libro');
    Route::get('/getULibros', 'AdministradorController@getULibros')->name('getULibros');
    Route::get('/detallesULibro', 'AdministradorController@detallesULibro')->name('detallesULibro');
    Route::get('/download_ulibros', 'AdministradorController@download_ulibros')->name('download_ulibros');

    Route::get('/comparativa', 'AdministradorController@comparativa')->name('comparativa');

    Route::get('/majestic', 'AdministradorController@majestic')->name('majestic');
    Route::get('/entradas-salidas', 'AdministradorController@entradas_salidas')->name('entradas-salidas');
    Route::get('/cerrar', 'AdministradorController@cerrar')->name('cerrar');
});

// OFICINA
Route::name('oficina.')->prefix('oficina')->middleware(['auth', 'role:Oficina'])->group(function () {
    Route::get('/remisiones', 'OficinaController@remisiones')->name('remisiones');
    Route::get('/pedidos', 'OficinaController@pedidos')->name('pedidos');
    Route::get('/pagos', 'OficinaController@pagos')->name('pagos');
    Route::get('/clientes', 'OficinaController@clientes')->name('clientes');
    Route::get('/libros', 'OficinaController@libros')->name('libros');
    Route::get('/entradas', 'OficinaController@entradas')->name('entradas');
    Route::get('/donaciones', 'OficinaController@donaciones')->name('donaciones');
    Route::get('/fecha-adeudo', 'OficinaController@fecha_adeudo')->name('fecha-adeudo');
    Route::get('/cerrar', 'OficinaController@cerrar')->name('cerrar');

    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/pagos', 'OficinaController@entradas_pagos')->name('pagos');
    });
    
    Route::get('/promociones', 'OficinaController@promociones')->name('promociones');
    Route::get('/notas', 'OficinaController@notas')->name('notas');
    Route::get('/entradas-salidas', 'OficinaController@entradas_salidas')->name('entradas-salidas');
});

Route::name('captura.')->prefix('captura')->middleware(['auth', 'role:captura'])->group(function () {
    // Route::get('/remisiones', 'OficinaController@remisiones')->name('remisiones');
    // Route::get('/donaciones', 'OficinaController@donaciones')->name('donaciones');
    // Route::get('/promociones', 'OficinaController@promociones')->name('promociones');
    Route::get('/remisiones', function () {
        return view('captura.remisiones');
    })->name('remisiones');
    Route::get('/donaciones', function () {
        return view('captura.donaciones');
    })->name('donaciones');
    Route::get('/promociones', function () {
        return view('captura.promociones');
    })->name('promociones');
    Route::get('/libros', function () {
        return view('captura.libros');
    })->name('libros');
});

// VISITOR
Route::name('visitor.')->prefix('visitor')->middleware(['auth', 'role:visitor'])->group(function () {
    Route::get('/remisiones', 'VisitorController@remisiones')->name('remisiones');
    Route::get('/cortes', 'VisitorController@cortes')->name('cortes');
    Route::get('/fecha-adeudo', 'VisitorController@fecha_adeudo')->name('fecha-adeudo');
    Route::get('/libros', 'VisitorController@libros')->name('libros');
    Route::get('/entradas', 'VisitorController@entradas')->name('entradas');
    Route::get('/clientes', 'VisitorController@clientes')->name('clientes');
    Route::get('/notas', 'VisitorController@notas')->name('notas');
    Route::get('/pedidos', 'VisitorController@pedidos')->name('pedidos');
    Route::get('/promociones', 'VisitorController@promociones')->name('promociones');
    Route::get('/donaciones', 'VisitorController@donaciones')->name('donaciones');
});

//CLIENTES
//Agregar cliente
Route::post('/new_client', 'ClienteController@store')->name('new_client');
//Buscar cliente
Route::get('/mostrarClientes', 'ClienteController@mostrarClientes')->name('mostrarClientes');
//Editar informacion de cliente
Route::put('editar_cliente', 'ClienteController@editar')->name('editar_cliente');
// DESCARGAR LISTA DE CLIENTES
Route::get('/descargar_clientes', 'ClienteController@descargar_clientes')->name('descargar_clientes');


//REMISIONES
// OBTENER TODAS AS REMISIONES
Route::name('remisiones.')->prefix('remisiones')->group(function () {
    Route::get('/index', 'RemisionController@index')->name('index');
    Route::get('/pay_remisiones', 'RemisionController@pay_remisiones')->name('pay_remisiones');
    // Crear remision
    Route::post('/store', 'RemisionController@store')->name('store');
    // Actualizar remision
    Route::put('/update', 'RemisionController@update')->name('update');
    //Cancelar remision
    Route::put('/cancel', 'RemisionController@cancel')->name('cancel');
    //Cerrar remision
    Route::put('/close', 'RemisionController@close')->name('close');
    // Obtener remisiones pendientes
    Route::get('/obtener_pendientes', 'RemisionController@obtener_pendientes')->name('obtener_pendientes');
    // Obtener remisiones pendientes
    Route::get('/by_cliente_pendientes', 'RemisionController@by_cliente_pendientes')->name('by_cliente_pendientes');
    // ABRIR PAGINA PARA CREAR REMSION
    Route::get('/ce_remision/{remisione_id}/{editar}', 'RemisionController@ce_remision')->name('ce_remision');
    // ABRIR PAGINA PARA OBTENER DETALLES DE REMSION
    Route::get('/details/{id}', 'RemisionController@get_details')->name('details');
    // OBTENER LOS RESPONSABLES
    Route::get('/get_responsables', 'RemisionController@get_responsables')->name('get_responsables');
});

//Buscar remision
Route::get('lista_datos', 'RemisionController@show')->name('lista_datos');
// Asignar responsable de la remision
Route::put('assign_responsable', 'RemisionController@assign_responsable')->name('assign_responsable');


// OBTENER REMISION POR ID
Route::get('get_remision_id', 'RemisionController@get_remision_id')->name('get_remision_id');


// REMISIONES BUSQUEDA
// Buscar remisiones por cliente
Route::get('buscar_por_cliente', 'RemisionController@buscar_por_cliente')->name('buscar_por_cliente');
// Buscar remisiones por estado
Route::get('buscar_por_estado', 'RemisionController@buscar_por_estado')->name('buscar_por_estado');
// Buscar remisiones por fecha y cliente / estado
Route::get('buscar_por_fecha', 'RemisionController@buscar_por_fecha')->name('buscar_por_fecha');

//REMISIONES -Listado
Route::get('todos_los_clientes', 'RemisionController@todos')->name('todos_los_clientes');
Route::get('buscar_por_numero', 'RemisionController@por_numero')->name('buscar_por_numero');


//REMISIONES - Descargar
Route::get('/imprimirSalida/{id}', 'RemisionController@imprimirSalida')->name('imprimirSalida');

Route::get('/download_remision/{id}', 'RemisionController@download_remision')->name('download_remision');

// DESCARGAR TODO EN EXCEL DETALLADO
Route::get('/down_remisiones_excel/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_remisiones_excel')->name('down_remisiones_excel');
// DESCARGAR TODO EN EXCEL GENERAL
Route::get('/down_gral_excel/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_gral_excel')->name('down_gral_excel');
// DESCARGAR TODO EN PDF
Route::get('/down_remisiones_pdf/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_remisiones_pdf')->name('down_remisiones_pdf');
// DESCARGAR LA CUENTA GENERAL DEL CLIENTE
Route::get('/descargar_gralClientes', 'RemisionController@descargar_gralClientes')->name('descargar_gralClientes');


//LIBROS
//Agregar libro
Route::post('new_libro', 'LibroController@store')->name('new_libro');
//Actualizar libro
Route::put('actualizar_libro', 'LibroController@update')->name('actualizar_libro');
//Eliminar libro
Route::delete('eliminar_libro', 'LibroController@delete')->name('eliminar_libro');
//Buscar libro
Route::get('/mostrarLibros', 'LibroController@buscar')->name('mostrarLibros');
// Mostrar libros por editorial
Route::get('/libros_por_editorial', 'LibroController@libros_por_editorial')->name('libros_por_editorial');
//Datos del libro
Route::get('/buscarISBN', 'LibroController@show')->name('buscarISBN'); 
// Buscar libro por ISBN y editorial
Route::get('/isbn_por_editorial', 'LibroController@isbn_por_editorial')->name('isbn_por_editorial'); 
//Obtener todos los libros
Route::get('allLibros', 'LibroController@allLibros')->name('allLibros');
// Descargar en formato excel todos los libros
Route::get('/downloadExcel/{editorial}', 'LibroController@downloadExcel')->name('downloadExcel');
// Mostrar entradas por libro
Route::get('movimientos_todos', 'LibroController@movimientos_todos')->name('movimientos_todos');
// Mostrar entradas por libro
Route::get('movimientos_por_edit', 'LibroController@movimientos_por_edit')->name('movimientos_por_edit');
// Descargar movimientos por libro
Route::get('/download_movimientos/{editorial}', 'LibroController@download_movimientos')->name('download_movimientos');
// Mostrar libros por tipo
Route::get('obtener_movimientos', 'LibroController@obtener_movimientos')->name('obtener_movimientos');
// Detalles del movimiento
Route::get('detalles_movimientos', 'LibroController@detalles_movimientos')->name('detalles_movimientos');
// Obtener movimientos por fecha
Route::get('movimientos_por_fecha', 'LibroController@movimientos_por_fecha')->name('movimientos_por_fecha');
// Descargar movimientos por fecha y categoria
Route::get('/down_fechaCategoria/{incio}/{final}/{categoria}', 'LibroController@down_fechaCategoria')->name('down_fechaCategoria');

// OBTENER TODOS LOS MOVIMIENTOS POR MONTO
Route::get('all_movmonto', 'LibroController@all_movmonto')->name('all_movmonto');
// OBTENER LOS MOVIMIENTOS POR EDITORIAL
Route::get('editorial_movmonto', 'LibroController@editorial_movmonto')->name('editorial_movmonto');
// OBTENER LOS MOVIMIENTOS POR FECHA
Route::get('fecha_movmonto', 'LibroController@fecha_movmonto')->name('fecha_movmonto');
// OBTENER DETALLES DE MONTO POR LIBRO
Route::get('detalles_monto', 'LibroController@detalles_monto')->name('detalles_monto');
// DESCARGAR EXCEL DE LOS MOVIMIENTOS 
Route::get('download_movmonto/{editorial}/{mes}', 'LibroController@download_movmonto')->name('download_movmonto');


//ENTRADAS
//Buscar editorial
Route::get('/mostrarEditoriales', 'EntradaController@mostrarEditoriales')->name('mostrarEditoriales');
//Buscar folio
Route::get('/buscarFolio', 'EntradaController@buscarFolio')->name('buscarFolio'); 
//Mostrar todas las entradas
Route::get('detalles_entrada', 'EntradaController@detalles_entrada')->name('detalles_entrada');
//Imprimir entrada
Route::get('/downloadEntrada/{id}', 'EntradaController@downloadEntrada')->name('downloadEntrada');
//Mostrarentradas por fecha
Route::get('fecha_entradas', 'EntradaController@fecha_entradas')->name('fecha_entradas');
//Mostrarentradas por fecha
Route::put('pago_entrada', 'EntradaController@pago_entrada')->name('pago_entrada');
// Descargar reporte de entradas en PDF
Route::get('/downEntradas/{inicio}/{final}/{editorial}', 'EntradaController@downEntradas')->name('downEntradas');
// Descargar reporte de entradas en EXCEL
Route::get('/downEntradasEXC/{inicio}/{final}/{editorial}/{tipo}', 'EntradaController@downEntradasEXC')->name('downEntradasEXC');
// Obtener todos los pagos de las entradas por editorial
Route::get('pagos_entrada', 'EntradaController@pagos_entrada')->name('pagos_entrada');
// Mostrar los depositos por editorial
Route::get('depositos_enteditoriale', 'EntradaController@depositos_enteditoriale')->name('depositos_enteditoriale');

Route::name('entradas.')->prefix('entradas')->group(function () {
    // Guardar deposito de entrada
    Route::post('save_pago', 'EntradaController@save_pago')->name('save_pago');
    // ACTUALIZAR DEPOSITO
    Route::put('update_pago', 'EntradaController@update_pago')->name('update_pago');
    // ELIMINAR PAGO
    Route::delete('delete_pago', 'EntradaController@delete_pago')->name('delete_pago');
});

//PAGOS
//Guardar pago por unidades
Route::post('registrar_pago', 'PagoController@store')->name('registrar_pago');
// Guardar pago de remision por monto
Route::post('deposito_remision', 'PagoController@deposito_remision')->name('deposito_remision');
//Obtener registros de vendidos
Route::get('datos_vendidos', 'PagoController@datos_vendidos')->name('datos_vendidos');
//Buscar pagos por cliente por geneeral
Route::get('/all_pagos', 'PagoController@all_pagos')->name('all_pagos');
//Buscar pagos por cliente por remision
Route::get('/pagos_remision_cliente', 'PagoController@pagos_remision_cliente')->name('pagos_remision_cliente');
// Guardar la revisión del deposito
Route::put('guardar_revision', 'PagoController@guardar_revision')->name('guardar_revision');

//NOTA
//Guardar nota
Route::post('guardar_nota', 'NoteController@store')->name('guardar_nota');
//Actualizar nota
Route::post('actualizar_nota', 'NoteController@update')->name('actualizar_nota');
//Mostrar detalles de nota
Route::get('detalles_nota', 'NoteController@detalles_nota')->name('detalles_nota');
//Guardar pago de la nota
Route::post('guardar_pago', 'NoteController@guardar_pago')->name('guardar_pago');
//Guardar devolucion
Route::post('guardar_devolucion', 'NoteController@guardar_devolucion')->name('guardar_devolucion');
// Buscar nota por folio
Route::get('buscar_folio_note', 'NoteController@buscar_folio')->name('buscar_folio_note');
// Buscar por cliente
Route::get('buscar_cliente_notes', 'NoteController@buscar_cliente_notes')->name('buscar_cliente_notes');
// Buscar notas por fecha
Route::get('buscar_fecha_notes', 'NoteController@buscar_fecha_notes')->name('buscar_fecha_notes');
// Descargar reporte de notas
Route::get('/download_note/{cliente}/{inicio}/{final}/{tipo}', 'NoteController@download_note')->name('download_note');
// Descargar nota
Route::get('/download_nota/{id}', 'NoteController@download_nota')->name('download_nota');

//PROMOCION
//Guardar promocion
Route::post('guardar_promocion', 'PromotionController@store')->name('guardar_promocion');
//Mostrar departures
Route::get('obtener_departures', 'PromotionController@obtener_departures')->name('obtener_departures');
// Buscar promocion por folio
Route::get('buscar_folio_promo', 'PromotionController@buscar_folio')->name('buscar_folio_promo');
// Buscar promocion por plantel
Route::get('buscar_plantel', 'PromotionController@buscar_plantel')->name('buscar_plantel');
// Buscar promociones por fecha
Route::get('buscar_fecha_promo', 'PromotionController@buscar_fecha_promo')->name('buscar_fecha_promo');
// Descargar el reporte de promoción
Route::get('download_promotion/{plantel}/{inicio}/{final}/{tipo}', 'PromotionController@download_promotion')->name('download_promotion');
// Descargar nota de la promocion
Route::get('/download_promocion/{id}', 'PromotionController@download_promocion')->name('download_promocion');

// DONACIONE
// Obtener detalles de la donación
Route::get('detalles_donacion', 'DonacioneController@detalles_donacion')->name('detalles_donacion');
// Obtener donaciones por plantel
Route::get('buscar_plantel_regalo', 'DonacioneController@buscar_plantel_regalo')->name('buscar_plantel_regalo');
// Obtener donaciones por fecha
Route::get('buscar_fecha_regalo', 'DonacioneController@buscar_fecha_regalo')->name('buscar_fecha_regalo');
// Descargar el reporte de promoción
Route::get('download_donacion/{plantel}/{inicio}/{final}/{tipo}', 'DonacioneController@download_donacion')->name('download_donacion');
// Marcar como entregada la donación
Route::put('entrega_donacion', 'DonacioneController@entrega_donacion')->name('entrega_donacion');
// Descargar nota de la donacion
Route::get('/download_regalo/{id}', 'DonacioneController@download_regalo')->name('download_regalo');

// Guardar comentario de la remisión
Route::post('guardar_comentario', 'RemisionController@guardar_comentario')->name('guardar_comentario');

// VENDIDO
// Obtener todas los libros vendidos
Route::get('obtener_vendidos', 'VendidoController@obtener_vendidos')->name('obtener_vendidos');
// Obtener por fecha
Route::get('obtener_por_fecha', 'VendidoController@obtener_por_fecha')->name('obtener_por_fecha');
// Obtener unidades vendidas por libro
Route::get('obtener_libro', 'VendidoController@obtener_libro')->name('obtener_libro');
//Obtener por libros y fecha
Route::get('libro_por_fecha', 'VendidoController@libro_por_fecha')->name('libro_por_fecha');
// Obtener libros vendidos por cliente
Route::get('obtener_cliente', 'VendidoController@obtener_cliente')->name('obtener_cliente');
// Obtener libros vendidos por cliente y fecha
Route::get('cliente_por_fecha', 'VendidoController@cliente_por_fecha')->name('cliente_por_fecha');
// Obtener libros vendidos por editorial
Route::get('obtener_editorial', 'VendidoController@obtener_editorial')->name('obtener_editorial');
// Obtener por editorial y fecha
Route::get('editorial_por_fecha', 'VendidoController@editorial_por_fecha')->name('editorial_por_fecha');
// Obtener detalles de vendidos
Route::get('detalles_vendidos', 'VendidoController@detalles_vendidos')->name('detalles_vendidos');

// DESCARGAR REPORTES
// Descargar reporte de libros vendidos por cliente
Route::get('/downClienteEX/{cliente_id}/{fecha1}/{fecha2}', 'VendidoController@downClienteEX')->name('downClienteEX');
// Descargar reporte por libro
Route::get('/downLibroEX/{libro_id}/{fecha1}/{fecha2}', 'VendidoController@downLibroEX')->name('downLibroEX');
// Descargar reporte de libros vendidos por editorial
Route::get('/downEditorialEX/{editorial}/{fecha1}/{fecha2}', 'VendidoController@downEditorialEX')->name('downEditorialEX');
// Descargar reporte detallado de libros vendidos
Route::get('/downDetalladoEX/{fecha1}/{fecha2}', 'VendidoController@downDetalladoEX')->name('downDetalladoEX');


// PEDIDOS
// Mostrar detalles de la compra
Route::get('detalles_compra', 'CompraController@detalles_compra')->name('detalles_compra');
// Buscar compra por numero de pedido
Route::get('buscar_n_pedido', 'CompraController@buscar_n_pedido')->name('buscar_n_pedido');
// Buscar compras por usuario
Route::get('buscar_usuario_p', 'CompraController@buscar_usuario_p')->name('buscar_usuario_p');
// Buscar compras por fecha
Route::get('buscar_fecha_p', 'CompraController@buscar_fecha_p')->name('buscar_fecha_p');
// Descargar reporte
Route::get('/download_compra/{usuario}/{inicio}/{final}/{tipo}', 'CompraController@download_compra')->name('download_compra');
// Marcar la entrega del pedido
Route::put('marcar_pedido', 'CompraController@marcar_pedido')->name('marcar_pedido');
// Descargar nota
Route::get('/download_pedido/{id}', 'CompraController@download_pedido')->name('download_pedido');


// DESCARGAR LA CUENTA GENERAL DE LAS EDITORIALES
Route::get('/descargar_gralEdit', 'EntradaController@descargar_gralEdit')->name('descargar_gralEdit'); 


// OBTENER REMCLIENTE PARA REALIZAR PAGO
Route::get('get_remcliente', 'RemisionController@get_remcliente')->name('get_remcliente');



// MOSTRAR EDITORIALES
Route::get('show_editoriales', 'OficinaController@show_editoriales')->name('show_editoriales');
// GUARDAR PEDIDO
Route::name('pedido.')->prefix('pedido')->group(function () {
    Route::post('guardar', 'PedidoController@store')->name('guardar');
    Route::put('change_status', 'PedidoController@change_status')->name('change_status');
    Route::put('cancelar_pedido', 'PedidoController@cancelar_pedido')->name('cancelar_pedido');
    Route::get('detalles', 'PedidoController@show')->name('detalles');
    Route::get('get_provider', 'PedidoController@get_provider')->name('get_provider');
    Route::get('get_date', 'PedidoController@get_date')->name('get_date');
});

//REMCLIENTE
Route::name('remcliente.')->prefix('remcliente')->group(function () {
    // OBTENER TODAS LAS REMCLIENTE
    Route::get('/index', 'RemclienteController@index')->name('index');
    Route::get('/by_cliente', 'RemclienteController@by_cliente')->name('by_cliente');
    Route::get('/get_totales', 'RemclienteController@get_totales')->name('get_totales');
    Route::get('/get_gralcortes', 'RemclienteController@get_gralcortes')->name('get_gralcortes');
    Route::get('/gc_bycliente', 'RemclienteController@gc_bycliente')->name('gc_bycliente');
});

//LIBROS
Route::name('libro.')->prefix('libro')->group(function () {
    Route::get('/index', 'LibroController@index')->name('index');
    Route::get('/by_titulo', 'LibroController@by_titulo')->name('by_titulo');
    Route::get('/by_isbn', 'LibroController@by_isbn')->name('by_isbn');
    //Buscar libro por editorial
    Route::get('/by_editorial', 'LibroController@by_editorial')->name('by_editorial');
    // OBTENER MOVIMIENTOS DEL LIBRO
    Route::get('/movimientos_libro', 'LibroController@movimientos_libro')->name('movimientos_libro');
    // MARCAR COMO INACTIVO EL LIBRO
    Route::put('/inactivar', 'LibroController@inactivar')->name('inactivar');
    // OBTENER ENTRADAS Y SALIDAS
    Route::get('/entradas_salidas', 'LibroController@entradas_salidas')->name('entradas_salidas');
    // OBTENER DETALLES DE ENTRADAS Y SALIDAS, DE UN LIBRO
    Route::get('/details_entsal', 'LibroController@details_entsal')->name('details_entsal');
});

// PAGOS
Route::name('pagos.')->prefix('pagos')->group(function () {
    // Guardar pago
    //Guardar deposito de cuenta general
    Route::post('store_gral', 'PagoController@store_gral')->name('store_gral');
    // Mostrar los depositos por cliente
    Route::get('/depositos_cliente', 'PagoController@depositos_cliente')->name('depositos_cliente');
    // Descargar el estado de cuenta del cliente
    Route::get('/download_edocuenta/{cliente_id}', 'PagoController@download_edocuenta')->name('download_edocuenta');
});

//DEVOLUCIONES
Route::name('devoluciones.')->prefix('devoluciones')->group(function () {
    //Concluir remision
    Route::put('update', 'DevolucioneController@update')->name('update');
});

//DONACIONES
Route::name('donaciones.')->prefix('donaciones')->group(function () {
    //Obtener todas las donaciones
    Route::get('/index', 'DonacioneController@index')->name('index');
    // GUARDAR DONACIONES
    Route::post('store', 'DonacioneController@store')->name('store');
});

//ENTRADAS
Route::name('entradas.')->prefix('entradas')->group(function () {
    ///Crear entrada
    Route::post('store', 'EntradaController@store')->name('store');
    //Actualizar entrada
    Route::put('update', 'EntradaController@update')->name('update');
    //Actualizar costos unitarios
    Route::put('update_costos', 'EntradaController@update_costos')->name('update_costos');
    ///Guardar devolución de la entrada
    Route::post('devolucion', 'EntradaController@devolucion')->name('devolucion');
    // ENVIAR DEVOLUCIÓN
    Route::put('send_devoluciones', 'EntradaController@send_devoluciones')->name('send_devoluciones');
});

//Obtener todos los cliente
Route::get('/getTodo', 'ClienteController@getTodo')->name('getTodo');
//CLIENTES
Route::name('clientes.')->prefix('clientes')->group(function () {
    //Obtener todos los cliente
    Route::get('/index', 'ClienteController@index')->name('index');
    //Obtener los clientes por coincidencia de nombre
    Route::get('/by_name', 'ClienteController@by_name')->name('by_name');
    // Detalles del cliente
    Route::get('/show', 'ClienteController@show')->name('show'); 
});

// MANAGER
Route::name('manager.')->prefix('manager')
    ->middleware(['auth', 'role:manager'])->group(function () {
    Route::name('cortes.')->prefix('cortes')->group(function () {
        Route::get('/lista', 'ManagerController@lista_cortes')->name('lista');
        Route::get('/pagos', 'ManagerController@cortes_pagos')->name('pagos');
    });
    Route::name('movimientos.')->prefix('movimientos')->group(function () {
        Route::get('/clientes', 'ManagerController@movimientos_clientes')->name('clientes');
        Route::get('/libros', 'ManagerController@movimientos_libros')->name('libros');
        Route::get('/entradas-salidas', 'ManagerController@entradas_salidas')->name('entradas-salidas');
    });
    Route::name('remisiones.')->prefix('remisiones')->group(function () {
        Route::get('/lista', 'ManagerController@lista_remisiones')->name('lista');
        Route::get('/pago_devolucion', 'ManagerController@pago_devolucion')->name('pago_devolucion');
        Route::get('/fecha_adeudo', 'ManagerController@fecha_adeudo')->name('fecha_adeudo');
    });
    Route::name('otros.')->prefix('otros')->group(function () {
        Route::get('/notas', 'ManagerController@notas')->name('notas');
        Route::get('/pedidos', 'ManagerController@pedidos')->name('pedidos');
        Route::get('/promociones', 'ManagerController@promociones')->name('promociones');
        Route::get('/donaciones', 'ManagerController@donaciones')->name('donaciones');
    });
    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/lista', 'ManagerController@lista_entradas')->name('lista');
        Route::get('/pagos', 'ManagerController@entradas_pagos')->name('pagos');
    });
    Route::get('/libros', 'ManagerController@libros')->name('libros');
    Route::get('/clientes', 'ManagerController@clientes')->name('clientes');
});

Route::name('cortes.')->prefix('cortes')->group(function () {
    Route::get('/index', 'CorteController@index')->name('index');
    Route::get('/get_all', 'CorteController@get_all')->name('get_all');
    Route::post('/store', 'CorteController@store')->name('store');
    Route::put('/update', 'CorteController@update')->name('update');
    Route::get('/show', 'CorteController@show')->name('show');
    Route::get('/show_one', 'CorteController@show_one')->name('show_one');
    Route::get('/show_bycliente', 'CorteController@show_bycliente')->name('show_bycliente');
    Route::get('/get_remisiones', 'CorteController@get_remisiones')->name('get_remisiones');
    Route::get('/rems_bycliente', 'CorteController@rems_bycliente')->name('rems_bycliente');
    Route::put('/clasificar_rems', 'CorteController@clasificar_rems')->name('clasificar_rems');
    Route::put('/move_rem', 'CorteController@move_rem')->name('move_rem');
    Route::get('/get_pagos', 'CorteController@get_pagos')->name('get_pagos');
    Route::get('/pagos_bycliente', 'CorteController@pagos_bycliente')->name('pagos_bycliente');
    Route::put('/clasificar_pagos', 'CorteController@clasificar_pagos')->name('clasificar_pagos');
    Route::put('/move_pago', 'CorteController@move_pago')->name('move_pago');
    Route::put('/verify_totales', 'CorteController@verify_totales')->name('verify_totales');
    Route::post('/save_payment', 'CorteController@save_payment')->name('save_payment');
    Route::get('/by_cliente', 'CorteController@by_cliente')->name('by_cliente');
    Route::put('/edit_payment', 'CorteController@edit_payment')->name('edit_payment');
    Route::delete('/delete_payment', 'CorteController@delete_payment')->name('delete_payment');
    Route::get('/list_bycliente', 'CorteController@list_bycliente')->name('list_bycliente');

    // ABRIR EN PAGINA NUEVA
    Route::get('/details_cliente/{cliente_id}', 'CorteController@details_cliente')->name('details_cliente');

    Route::post('/upload_payment', 'CorteController@upload_payment')->name('upload_payment');
});

Route::name('promotions.')->prefix('promotions')->group(function () {
    Route::get('index', 'PromotionController@index')->name('index');
});


// ********** NO UTILIZADO **********
//Llenar tabla de vendidos
Route::put('vendidos_remision', 'RemisionController@registrar_vendidos')->name('vendidos_remision');
// Guardar nuevo pedido
Route::post('guardar_compra', 'CompraController@store')->name('guardar_compra');
// CATEGORIES
Route::name('categories.')->prefix('categories')->group(function () {
    Route::post('/store', 'CategorieController@store')->name('store');
});