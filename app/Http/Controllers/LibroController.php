<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Enteditoriale;
use App\Libro;
use App\Entrada;
use App\Registro;
use App\Devolucione;
use PDF;
use Carbon\Carbon;
use App\Exports\LibrosExport;
use App\Exports\MovLibrosExport;
use App\Exports\MovFechasExport;
use App\Exports\MovMontoExport;
use Maatwebsite\Excel\Facades\Excel;

class LibroController extends Controller
{
    // MOSTRAR TODOS LOS LIBROS
    public function index(){
        $libros = $this->all_libros_paginate();
        return response()->json($libros);
    }

    public function all_libros_paginate(){
        return Libro::orderBy('editorial', 'asc')
                        ->where('estado', 'activo')->paginate(20);
    }

    // MOSTRAR COINCIDENCIAS DE TITULO PAGINADO
    public function by_titulo(){
        $titulo = Input::get('titulo');
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('titulo','like','%'.$titulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->paginate(20);
        return response()->json($libros);
    }

    // MOSTRAR COINCIDENCIAS DE ISBN PAGINADO
    public function by_isbn(){
        $isbn = Input::get('isbn');
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('ISBN','like','%'.$isbn.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->paginate(20);
        return response()->json($libros);
    }

    // BUSCAR LIBRO POR EDITORIAL paginado
    // Función utilizada en LibrosComponent
    public function by_editorial(){
        $editorial = Input::get('editorial');
        if($editorial === 'TODO'){
            $libros = $this->all_libros_paginate();
        }
        else{
            $libros = Libro::where('editorial','like','%'.$editorial.'%')
                ->where('estado', 'activo')
                ->orderBy('titulo', 'asc')->paginate(20);
        }
        return response()->json($libros);
    }

    // BUSCAR LIBRO POR ISBN
    // Función utilizada en
    // - AdeudosComponent - DevoluciónAdeudosComponent - EntradasComponent
    // - NewNotaComponent - PromocionesComponent - RemisionComponent
    public function show(){
        $isbn = Input::get('isbn');
        // $libro = Libro::where('ISBN', $isbn)->first();
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('ISBN','like','%'.$isbn.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        // $datos = $this->assign_registers($libro);
        return response()->json($libros);
    }

    public function isbn_por_editorial(){
        $isbn = Input::get('isbn');
        $editorial = Input::get('editorial');
        $libros = Libro::where('ISBN','like','%'.$isbn.'%')
                    ->where('editorial', $editorial)
                    ->where('estado', 'activo')
                    ->orderBy('ISBN', 'asc')->get();
        return response()->json($libros);
    }

    public function assign_registers($libro){
        return $datos = [
                'id' => $libro->id,
                'ISBN' => $libro->ISBN,
                'titulo' => $libro->titulo,
                'editorial' => $libro->editorial,
                'piezas' => $libro->piezas,
                'defectuosos' => $libro->defectuosos,
                'costo_unitario' => 0,
                'unidades' => 0,
                'total' => 0,
            ];
    }

    // MOSTRAR LIBROS POR COINCIDENCIA DE LETRAS
    // Función utilizada en
    // - AdeudosComponent - DevoluciónAdeudosComponent - EntradasComponent
    // - NewNotaComponent - PromocionesComponent - RemisionComponent - LibrosComponent
    public function buscar(){
        $queryTitulo = Input::get('queryTitulo');
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('titulo','like','%'.$queryTitulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function libros_por_editorial(){
        $queryTitulo = Input::get('queryTitulo');
        $editorial = Input::get('editorial');
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas')
                    ->where('editorial','like','%'.$editorial.'%')
                    ->where('titulo','like','%'.$queryTitulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('editorial', 'asc')->get();
        return response()->json($libros);
    }

    // DESCARGAR EN FORMATO EXCEL LOS LIBROS
    // Función utilizada en LibrosComponent
    public function downloadExcel($editorial){
        return Excel::download(new LibrosExport($editorial), 'libros.xlsx');
    }

    // GUARDAR NUEVO LIBRO
    // Función utilizada en NewLibroComponent
    public function store(Request $request){
        $this->func_validar($request);
        \DB::beginTransaction();
        try {
                $libro = Libro::create([
                    'ISBN' => $request->ISBN,
                    'titulo' => strtoupper($request->titulo),
                    'autor' => strtoupper($request->autor),
                    'editorial' => $request->editorial
                ]);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($libro);
    }

    //Función para validar los libros
    public function func_validar($request){
        $this->validate($request, [
            'titulo' => 'min:5|max:100|required|string|unique:libros',
            'ISBN' => 'required|numeric|max:9999999999999|min:1000000000000|unique:libros',
            'editorial' => 'required|min:5|max:100|string',
            'defectuosos' => 'numeric|min:0'
        ]);
    }

    // ACTUALIZAR DATOS DE LIBRO
    // Función utilizada en EditarLibroComponent
    public function update(Request $request){
        $libro = Libro::whereId($request->id)->first();
        if($request->ISBN !== $libro->ISBN || strtoupper($request->titulo) !== $libro->titulo){
            $libro->ISBN = 'ISBN-'.$libro->ISBN;
            $libro->titulo = 'TITLE-'.$libro->titulo;
            $libro->save();     
            $this->func_validar($request);
        } else {
            $this->validate($request, [
                'titulo' => 'min:5|max:100|required|string',
                'ISBN' => 'required|numeric|max:9999999999999|min:1000000000000',
                'editorial' => 'required|min:5|max:100|string',
                'defectuosos' => 'numeric|min:0'
            ]);
        }      
        \DB::beginTransaction();
        try {
                $libro->update([
                    'ISBN' => $request->ISBN,
                    'titulo' => strtoupper($request->titulo),
                    'autor' => strtoupper($request->autor),
                    'editorial' => $request->editorial,
                    'defectuosos' => $libro->defectuosos + (int) $request->defectuosos
                ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($libro);
    }

    // ELIMINAR LIBRO (FUNCIÓN ELIMINADA DE COMPONENT)
    public function delete(){
        $id = Input::get('id');
        
        try {
            \DB::beginTransaction();
            $libro = Libro::whereId($id)->delete();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(null, 200);
    }

    // MOSTRAR MOVIMIENTOS DE UN LIBRO
    public function movimientos_todos(){
        $libros = $this->get_libros();
        $movimientos = $this->busqueda_unidades($libros);
        return response()->json($movimientos);
    }

    public function movimientos_libro(Request $request){
        $libros = Libro::where('id', $request->libro_id)->get();
        $movimientos = $this->busqueda_unidades($libros);
        return response()->json($movimientos);
    }

    public function get_libros(){
        $libros = \DB::table('libros')->select('id', 'ISBN', 'titulo', 'piezas')->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function get_libros_editorial($editorial){
        $libros = \DB::table('libros')
                ->where('editorial', $editorial)
                ->select('id', 'ISBN', 'titulo', 'piezas')
                ->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function busqueda_unidades($libros){
        // ENTRADAS
        $entradas = \DB::table('registros')
                    ->select('libro_id as libro_id', \DB::raw('SUM(unidades) as entradas'))
                    ->groupBy('libro_id')
                    ->get();
        $devoluciones = \DB::table('devoluciones')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as devoluciones'))
                    ->groupBy('libro_id')
                    ->get();
        // SALIDAS
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                    ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.unidades) as entdevoluciones'))
                    ->groupBy('registros.libro_id')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as remisiones'))
                    ->groupBy('libro_id')
                    ->get();
        $notas = \DB::table('registers')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as notas'))
                    ->groupBy('libro_id')
                    ->get();
        $promociones = \DB::table('departures')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as promociones'))
                    ->groupBy('libro_id')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as donaciones'))
                    ->groupBy('libro_id')
                    ->get();

        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assign_array($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    }

    public function assign_array($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
        $relacion = [
            'ISBN' =>'',
            'libro' => '',
            'entradas' => 0,
            'devoluciones' => 0,
            'entdevoluciones' => 0,
            'remisiones' => 0,
            'notas' => 0,
            'promociones' => 0,
            'donaciones' => 0,
            'existencia' => 0,
        ];
        $relacion['existencia'] = $libro->piezas;
        $relacion['ISBN'] = $libro->ISBN;
        $relacion['libro'] = $libro->titulo;
        // ENTRADAS
        foreach($entradas as $entrada){
            if($libro->id === $entrada->libro_id)
                $relacion['entradas'] = $entrada->entradas;
        }
        foreach($devoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id)
                $relacion['devoluciones'] = $devolucion->devoluciones;
        }
        foreach($entdevoluciones as $entdevolucion){
            if($libro->id === $entdevolucion->libro_id)
                $relacion['entdevoluciones'] = $entdevolucion->entdevoluciones;
        }
        foreach($remisiones as $remision){
            if($libro->id === $remision->libro_id)
                $relacion['remisiones'] = $remision->remisiones;
        }
        foreach($notas as $nota){
            if($libro->id === $nota->libro_id)
                $relacion['notas'] = $nota->notas;
        }
        foreach($promociones as $promocion){
            if($libro->id === $promocion->libro_id)
                $relacion['promociones'] = $promocion->promociones;
        }
        foreach($donaciones as $donacion){
            if($libro->id === $donacion->libro_id)
                $relacion['donaciones'] = $donacion->donaciones;
        }
        return $relacion;
    }

    public function busqueda_monto_gral(){
        $registros = \DB::table('registros')
                ->join('libros', 'registros.libro_id', '=', 'libros.id')
                ->select(
                    'libro_id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(total) as entradas')
                )->groupBy('libro_id', 'libros.titulo')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        return $registros;
    }

    public function monto_editorial_gral($editorial){
        $registros = \DB::table('registros')
            ->join('libros', 'registros.libro_id', '=', 'libros.id')
            ->where('libros.editorial', $editorial)
            ->select(
                'libro_id as libro_id',
                'libros.titulo as libro',
                \DB::raw('SUM(total) as entradas')
            )->groupBy('libro_id', 'libros.titulo')
            ->orderBy('libros.titulo', 'asc')
            ->get();
        return $registros;
    }

    

    public function movimientos_por_edit(){
        $editorial = Input::get('queryEMov');
        if($editorial === 'TODO'){
            $registros = $this->get_libros();
        } else{
            $registros = $this->get_libros_editorial($editorial);
        }
        $movimientos = $this->busqueda_unidades($registros);
        return response()->json($movimientos);
    }

    public function download_movimientos($editorial){
        return Excel::download(new MovLibrosExport($editorial), 'movimientos-unidades.xlsx');
    }

    public function down_fechaCategoria($inicio, $final, $categoria){
        return Excel::download(new MovFechasExport($inicio, $final, $categoria), $categoria.'.xlsx');
    }

    public function movimientos_por_fecha(){
        $categoria = Input::get('categoria');
        $inicio = Input::get('inicio');
        $final = Input::get('final');

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        // ENTRADAS
        if($categoria === 'ENTRADAS'){
            $datos = \DB::table('registros')
                ->join('libros', 'registros.libro_id', '=', 'libros.id')
                ->whereBetween('registros.created_at', [$fecha1, $fecha2])
                ->select(
                    // 'libro_id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )->groupBy('libro_id', 'libros.titulo')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        }
        if($categoria === 'DEVOLUCIONES'){
            $datos = \DB::table('devoluciones')
                ->join('libros', 'devoluciones.libro_id', '=', 'libros.id')
                ->whereBetween('devoluciones.created_at', [$fecha1, $fecha2])
                ->whereNotIn('devoluciones.unidades', [0])
                ->select(
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )
                ->orderBy('libros.titulo', 'asc')
                ->groupBy('libro_id', 'libros.titulo')
                ->get();
        }
        if($categoria === 'NOTASDEV'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereNotIn('registers.unidades_devuelto', [0])
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades_devuelto) as unidades'),
                        \DB::raw('SUM(total_devuelto) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        // SALIDAS
        if($categoria === 'REMISIONES'){
            $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereBetween('datos.created_at', [$fecha1, $fecha2])
                    ->select(
                        // 'libro_id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(datos.unidades) as unidades'),
                        \DB::raw('SUM(datos.total) as total')
                    )
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'NOTAS'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades'),
                        \DB::raw('SUM(total) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'PEDIDOS'){
            $datos = \DB::table('pedidos')
                    ->join('libros', 'pedidos.libro_id', '=', 'libros.id')
                    ->select(
                        // 'libro_id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades'),
                        \DB::raw('SUM(total) as total')
                    )->whereBetween('pedidos.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'PROMOCIONES'){
            $datos = \DB::table('departures')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('departures.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'DONACIONES'){
            $datos = \DB::table('donaciones')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('donaciones.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        return response()->json($datos);
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    public function detalles_movimientos(){
        $titulo = Input::get('titulo');
        $libro = Libro::where('titulo', $titulo)
                    ->with(['registros.entrada', 'registros.entdevoluciones.entrada','registers.note', 'departures.promotion', 'donaciones.regalo'])
                    ->first();
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->where('libro_id', $libro->id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('remisiones.id as remisione_id', 'unidades')
                    ->get();
        $devoluciones = Devolucione::where('libro_id', $libro->id)
                        ->whereNotIn('unidades', [0])
                        ->with('remisione')->get();
        return response()->json(['libro' => $libro, 'datos' => $datos, 'devoluciones' => $devoluciones]);
    }

    public function all_movmonto(){
        $libros = $this->get_libros();
        $movimientos = $this->busqueda_monto($libros);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function editorial_movmonto(){
        $editorial = Input::get('editorial');
        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        $movimientos = $this->busqueda_monto($libros);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function fecha_movmonto(){
        $editorial = Input::get('editorial');
        $mes = Input::get('mes');

        $año = Carbon::now()->format('Y');
        $fecha = $año.'-'.$mes;

        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        
        $movimientos = $this->busqueda_fecha_monto($libros, $fecha);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function assign_mov($movimientos){
        $registros = array();
        foreach($movimientos as $m){
            if($m['total_entrada'] > 0 || $m['total_salida'] > 0)
                array_push($registros, $m);
        }
        return $registros;
    }

    public function detalles_monto(){
        $titulo = Input::get('titulo');
        $libro = Libro::where('titulo', $titulo)->first();
        $datos = $this->busqueda_por_libro($libro);
        return response()->json($datos);
    }

    public function busqueda_por_libro($libro){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                ->select('entradas.folio as folio', 'entradas.editorial as editorial', 'registros.total as entradas')
                ->where('libro_id', $libro->id)
                ->where('registros.total', '>', 0)
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('devoluciones')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->select('remisiones.id as folio', 'clientes.name as cliente', 'devoluciones.total as devoluciones')
                ->where('libro_id', $libro->id)
                ->where('devoluciones.total', '>', 0)
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->where('datos.libro_id', $libro->id)
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select('remisiones.id as folio', 'clientes.name as cliente', 'datos.total as remisiones')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->join('notes', 'registers.note_id', '=', 'notes.id')
                ->where('libro_id', $libro->id)
                ->select('notes.folio as folio', 'notes.cliente as cliente', 'registers.total as notas')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('entradas', 'entdevoluciones.entrada_id', '=', 'entradas.id')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->where('registros.libro_id', $libro->id)
                ->select('entradas.folio as folio', 'entradas.editorial as editorial', 'entdevoluciones.total as entdevoluciones')
                ->get();
        $datos = [
            'libro' => $libro->titulo,
            'entradas' => $entradas,
            'devoluciones' => $devoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'entdevoluciones' => $entdevoluciones
        ];
        return $datos;
    }

    public function busqueda_monto($libros){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as entradas'))
                ->where('total', '>', 0) 
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('devoluciones')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as devoluciones'))
                ->where('total', '>', 0) 
                ->groupBy('libro_id')
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select('libro_id as libro_id', \DB::raw('SUM(datos.total) as remisiones'))
                ->groupBy('libro_id')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as notas'))
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.total) as entdevoluciones'))
                ->groupBy('registros.libro_id')
                ->get();
        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    }

    // Mostrar movimientos por fecha
    public function busqueda_fecha_monto($libros, $fecha){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as entradas'))
                ->where('total', '>', 0) 
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('fechas')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as devoluciones'))
                ->where('total', '>', 0)
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->where('datos.created_at', 'like', '%'.$fecha.'%')
                ->select('libro_id as libro_id', \DB::raw('SUM(datos.total) as remisiones'))
                ->groupBy('libro_id')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as notas'))
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.total) as entdevoluciones'))
                ->where('entdevoluciones.created_at', 'like', '%'.$fecha.'%')
                ->groupBy('registros.libro_id')
                ->get();
        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    } 

    public function assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas){
        $relacion = [
            'titulo' => '',
            'entradas' => 0,
            'devoluciones' => 0,
            'total_entrada' => 0,
            'remisiones' => 0,
            'notas' => 0,
            'entdevoluciones' => 0,
            'total_salida' => 0,
            'total' => 0,
            '_cellVariants' => [ 'total' => '' ]
        ];
        $relacion['titulo'] = $libro->titulo;
        foreach($entradas as $entrada){
            if($libro->id === $entrada->libro_id){
                $relacion['entradas'] = $entrada->entradas;
                $relacion['total_entrada'] += $entrada->entradas;
            }
        }
        foreach($devoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id){
                $relacion['devoluciones'] = $devolucion->devoluciones;
                $relacion['total_entrada'] += $devolucion->devoluciones;
            }
        }
        foreach($entdevoluciones as $entdevolucion){
            if($libro->id === $entdevolucion->libro_id){
                $relacion['entdevoluciones'] = $entdevolucion->entdevoluciones;
                $relacion['total_salida'] += $entdevolucion->entdevoluciones;
            }
        }
        foreach($remisiones as $remision){
            if($libro->id === $remision->libro_id){
                $relacion['remisiones'] = $remision->remisiones;
                $relacion['total_salida'] += $remision->remisiones;
            }
        }
        foreach($notas as $nota){
            if($libro->id === $nota->libro_id){
                $relacion['notas'] = $nota->notas;
                $relacion['total_salida'] += $nota->notas;
            }
        }
        $total = $relacion['total_salida'] - $relacion['total_entrada'];
        $relacion['total'] = $total;
        $variant = '';
        if($relacion['entradas'] > 0){
            if($relacion['total_salida'] > $relacion['total_entrada']) $variant  = 'success';
            if($relacion['total_salida'] == $relacion['total_entrada']) $variant = 'warning';
            if($relacion['total_salida'] < $relacion['total_entrada']) $variant = 'danger';
        }
        $relacion['_cellVariants']['total'] = $variant;
        return $relacion;
    }

    public function download_movmonto($editorial, $mes){
        return Excel::download(new MovMontoExport($editorial, $mes), 'movimientos-monto.xlsx');
    }

    // MARCAR COMO INACTIVO EL LIBRO
    public function inactivar(Request $request){
        \DB::beginTransaction();
        try {
            Libro::whereId($request->libro_id)->update([
                'estado' => 'inactivo'
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    // OBTENER ENTRADAS Y SALIDAS
    public function entradas_salidas(Request $request){
        $inicio = $request->de.' 00:00:00';
        $final = $request->a.' 23:59:59';
        // SALIDAS
        $entradas = \DB::table('registros')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->select('registros.libro_id', 'registros.unidades')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('libros', 'fechas.libro_id', '=', 'libros.id')
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->select('fechas.libro_id', 'fechas.unidades')
                    ->get();
        // ENTRADAS
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('registros.libro_id', 'entdevoluciones.unidades')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->select('datos.libro_id', 'datos.unidades')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('registers.libro_id', 'registers.unidades')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->select('departures.libro_id', 'departures.unidades')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('donaciones.libro_id', 'donaciones.unidades')
                    ->get();

        $ids = [];
        $ids = $this->get_ids_libros($entradas, $ids);
        $ids = $this->get_ids_libros($fechas, $ids);
        $ids = $this->get_ids_libros($entdevoluciones, $ids);
        $ids = $this->get_ids_libros($remisiones, $ids);
        $ids = $this->get_ids_libros($notas, $ids);
        $ids = $this->get_ids_libros($promociones, $ids);
        $ids = $this->get_ids_libros($donaciones, $ids);
        
        $lista_datos = [];
        $libros = Libro::whereIn('id', $ids)->orderBy('titulo', 'asc')->get();
        $libros->map(function($libro) use(&$lista_datos, $entradas, $fechas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
            $ter = $this->get_datos_libros($libro->id, $entradas);
            $tdf = $this->get_datos_libros($libro->id, $fechas);
            $ted = $this->get_datos_libros($libro->id, $entdevoluciones);
            $trr = $this->get_datos_libros($libro->id, $remisiones);
            $tnr = $this->get_datos_libros($libro->id, $notas);
            $tpd = $this->get_datos_libros($libro->id, $promociones);
            $trd = $this->get_datos_libros($libro->id, $donaciones);
            
            $datos = [
                'id' => $libro->id,
                'libro' => $libro->titulo,
                'entradas' => $ter,
                'devoluciones' => $tdf,
                'entdevoluciones' => $ted,
                'remisiones' => $trr,
                'notas' => $tnr,
                'promociones' => $tpd,
                'donaciones' => $trd,
                '_cellVariants' => [
                    'entradas' => $ter > 0 ? 'success':'',
                    'devoluciones' => $tdf > 0 ? 'success':'',
                    'entdevoluciones' => $ted > 0 ? 'primary':'',
                    'remisiones' => $trr > 0 ? 'primary':'',
                    'notas' => $tnr > 0 ? 'primary':'',
                    'promociones' => $tpd > 0 ? 'primary':'',
                    'donaciones' => $trd > 0 ? 'primary':''
                ]
            ];
            
            $lista_datos[] = $datos;
        });
        return response()->json(collect($lista_datos));
    }

    // OBTENER DETALLES
    public function details_entsal(Request $request){
        $inicio = $request->de.' 00:00:00';
        $final = $request->a.' 23:59:59';
        $libro_id = $request->libro_id;
        $libro = Libro::find($libro_id);
        // SALIDAS
        $entradas = \DB::table('registros')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'entradas.folio as folio', 'registros.unidades', 'entradas.created_at')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('libros', 'fechas.libro_id', '=', 'libros.id')
                    ->where('fechas.libro_id', $libro_id)
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->select('libros.titulo', 'fechas.remisione_id as folio', 'fechas.unidades', 'fechas.fecha_devolucion')
                    ->get();
        // ENTRADAS
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'entradas.folio as folio', 'entdevoluciones.unidades', 'entdevoluciones.created_at')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->where('datos.libro_id', $libro_id)
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'remisiones.id as folio', 'datos.unidades', 'remisiones.created_at')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->where('registers.libro_id', $libro_id)
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'notes.folio as folio', 'registers.unidades', 'notes.created_at')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->where('departures.libro_id', $libro_id)
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'promotions.folio as folio', 'departures.unidades', 'promotions.created_at')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->where('donaciones.libro_id', $libro_id)
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'regalos.plantel as folio', 'donaciones.unidades', 'regalos.created_at')
                    ->get();

        $lista_datos = [
            'id' => $libro->id,
            'libro' => $libro->titulo,
            'entradas' => $entradas,
            'devoluciones' => $fechas,
            'entdevoluciones' => $entdevoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'promociones' => $promociones,
            'donaciones' => $donaciones,
        ];
        return response()->json($lista_datos);
    }

    public function get_ids_libros($array, $ids){
        $array->map(function($a) use(&$ids){
            $ids[] = $a->libro_id;
        });
        return $ids;
    }

    public function get_datos_libros($libro_id, $array){
        $dato = 0;
        foreach ($array as $a) {
            if($libro_id == $a->libro_id) $dato += $a->unidades;
        }
        return $dato;
    }
}
