<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Exports\EntradasExport;
use App\Exports\entradas\EntradaExport;
use App\Exports\EAccountExport;
use Illuminate\Http\Request;
use App\Entdevolucione;
use App\Enteditoriale;
use App\Entdeposito;
use Carbon\Carbon;
use App\Repayment;
use App\Registro;
use App\Entrada;
use App\Libro;
use Excel;
use PDF;

class EntradaController extends Controller
{
    // GUARDAR UNA NUEVA ENTRADA
    // Función utilizada en EntradasComponent
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $entrada = Entrada::create([
                'folio' => strtoupper($request->folio),
                'editorial' => $request->editorial,
                'unidades' => $request->unidades
            ]);

            $unidades = $this->save_registros($request->registros, $entrada);
            
            $entrada->update(['unidades' => $unidades]);
            $get_entrada = Entrada::whereId($entrada->id)->first();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($get_entrada);
    }

    public function save_registros($request_items, $entrada){
        $unidades = 0;
        $lista_entradas = [];
        $entradas = collect($request_items);
        $hoy = Carbon::now();
        $entradas->map(function($item) use(&$lista_entradas, $entrada, &$unidades, $hoy){
            $unidades_base = (int) $item['unidades'];
            $libro_id = $item['id'];
            $lista_entradas[] = [
                'entrada_id' => $entrada->id,
                'libro_id'  => $libro_id,
                'unidades'  => $unidades_base,
                'unidades_pendientes'  => $unidades_base,
                'created_at' => $hoy,
                'updated_at' => $hoy
            ];

            // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
            \DB::table('libros')->whereId($libro_id)
                ->increment('piezas', $unidades_base);   
            
            $unidades += $unidades_base;
        });

        // CREAR LISTA DE REGISTROS
        Registro::insert($lista_entradas);

        return $unidades;
    }
    
    // ACTUALIZAR DATOS DE ENTRADA
    // Función utilizada en EntradasComponent
    public function update(Request $request){
        $entrada = Entrada::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $this->save_registros($request->nuevos, $entrada);

            $entrada->folio = strtoupper($request->folio);
            $entrada->editorial = $request->editorial;
            $entrada->unidades = $request->unidades;
            $entrada->save();

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    // MOSTRAR ENTRADAS POR EDITORIAL
    // Función utilizada en EntradasComponent, EditarEntradasComponent, VendidosComponent
    public function mostrarEditoriales(){
        $editorial = Input::get('editorial');
        if($editorial == 'TODAS'){
            $entradas = Entrada::orderBy('id','desc')->get();
        }
        else{
            $entradas = Entrada::where('editorial','like','%'.$editorial.'%')->orderBy('id','desc')->get();
        }
        return response()->json($entradas);
    }

    // MOSTRAR ENTRADAS POR FECHA
    // Función utilizada en EditarEntradasComponent
    public function fecha_entradas(){
        $editorial 	= Input::get('editorial');
        $inicio = Input::get('inicio');
        $final = Input::get('final');
        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($editorial === null || $editorial == 'TODAS'){
            $entradas = Entrada::whereBetween('created_at', [$fecha1, $fecha2])->orderBy('id','desc')->get();
        } else {
            $entradas = Entrada::where('editorial', $editorial)
                        ->whereBetween('created_at', [$fecha1, $fecha2])
                        ->orderBy('id','desc')->get();
        }
        return response()->json($entradas);
    }

    // MOSTRAR DETALLES DE UNA ENTRADA
    // Función utilizada en EditarEntradasComponent, EntradasComponent
    public function detalles_entrada(){
        $entrada_id = Input::get('entrada_id');
        $entrada = Entrada::whereId($entrada_id)->with(['repayments', 'registros.libro'])->first();
        $entdevoluciones = Entdevolucione::where('entrada_id', $entrada_id)->with('registro.libro')->get();
        return response()->json(['entrada' => $entrada, 'entdevoluciones' => $entdevoluciones]);
    }

    // GUARDAR COSTOS DE LA ENTRADA
    // Función utilizada en EditarEntradasComponent
    public function update_costos(Request $request){
        \DB::beginTransaction();
        try {
            $total = 0;
            $lista_items = collect($request->items);
            $lista_items->map(function($item) use(&$total){
                Registro::whereId($item['id'])->update([
                    'costo_unitario' => (float) $item['costo_unitario'],
                    'total' => (double) $item['total']
                ]);
                $total += $item['total'];
            });

            $entrada = Entrada::find($request->id);
            $entrada->total = $total;
            $entrada->save();

            $editorial = Enteditoriale::where('editorial', $entrada->editorial)->first();
            if($editorial === null){
                Enteditoriale::create([
                    'editorial' => $entrada->editorial,
                    'total' => $entrada->total,
                    'total_pendiente' => $entrada->total
                ]);
            } else {
                $editorial->update([
                    'total' => $editorial->total + $entrada->total,
                    'total_pendiente' => $editorial->total_pendiente + $entrada->total
                ]);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    // GUARDAR PAGO DE ENTRADA
    // Función utilizada en EditarEntradasComponent
    public function pago_entrada(Request $request){
        try {
            \DB::beginTransaction();
            $entrada = Entrada::whereId($request->entrada_id)->first();
            $repayment = Repayment::create([
                'entrada_id'    => $entrada->id,
                'pago'          => $request->pago
            ]);
            $entrada->update([
                'total_pagos' => $entrada->total_pagos + $request->pago
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        
        return response()->json($entrada);
    }

    // BUSCAR ENTRADA POR FOLIO
    // Función utilizada en EntradasComponent
    public function buscarFolio(){
        $folio = Input::get('folio');
        $entrada = Entrada::where('folio', $folio)->first();
        return response()->json($entrada);
    }

    // IMPRIMIR REPORTE DE ENTRADA
    public function downloadEntrada($id){
        $entrada = Entrada::find($id);
        $name_archivo = 'entrada_' . $entrada->folio . '.xlsx';
        return Excel::download(new EntradaExport($entrada->id), $name_archivo);
    }

    // DESCARGAR TODAS LAS ENTRADAS
    public function downEntradas($inicio, $final, $editorial){
        $data['fecha'] = Carbon::now();
        $data['inicio'] = $inicio;
        $data['final'] = $final;

        if($final != '0000-00-00'){
            $fechas = $this->format_date($inicio, $final);
            $inicio = $fechas['inicio'];
            $final = $fechas['final'];
        }

        if($final === '0000-00-00' && $editorial === 'TODAS')
            $entradas = Entrada::orderBy('id','desc')->get(); 
        if($final !== '0000-00-00' && $editorial === 'TODAS')
            $entradas = Entrada::whereBetween('created_at', [$inicio, $final])->orderBy('id','desc')->get();
        if($final === '0000-00-00' && $editorial !== 'TODAS')
            $entradas = Entrada::where('editorial', $editorial)->orderBy('id','desc')->get();
        if($final !== '0000-00-00' && $editorial !== 'TODAS'){
            $entradas = Entrada::where('editorial', $editorial)
                        ->whereBetween('created_at', [$inicio, $final])
                        ->orderBy('id','desc')->get();
        }
        $totales = $this->acumular_totales($entradas);
        $data['total_unidades'] = $totales['total_unidades'];
        $data['total'] = $totales['total'];
        $data['total_pagos'] = $totales['total_pagos'];
        $data['total_pendiente'] = $totales['total_pendiente'];
        $data['total_devolucion'] = $totales['total_devolucion'];
        $data['editorial'] = $editorial;
        $data['entradas'] = $entradas;
        
        $pdf = PDF::loadView('download.pdf.entradas.reporte-gral', $data);
        return $pdf->download('reporte-entradas.pdf');
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

    public function acumular_totales($entradas){
        $total_unidades = 0;
        $total = 0;
        $total_pagos = 0;
        $total_pendiente = 0;
        $total_devolucion = 0;
        foreach($entradas as $entrada){
            $total_unidades += $entrada->unidades;     
            $total += $entrada->total;
            $total_pagos += $entrada->total_pagos;
            $total_devolucion += $entrada->total_devolucion;
            $total_pendiente += $entrada->total - $entrada->total_pagos;

        }
        $totales = [
            'total_unidades' => $total_unidades,
            'total' => $total,
            'total_pagos' => $total_pagos,
            'total_pendiente' => $total_pendiente,
            'total_devolucion' => $total_devolucion
        ];
        return $totales;
    }

    // DESCARGAR REPORTE DE ENTRADAS EN EXCEL
    public function downEntradasEXC($inicio, $final, $editorial, $tipo){
        return Excel::download(new EntradasExport($inicio, $final, $editorial, $tipo), 'reporte-entradas.xlsx');
    }

    // ELIMINAR REGISTRO DE ENTRADA (ELIMINADO DEL COMPONENTE)
    // Función utilizada en EntradasComponent
    public function eliminar(Request $request){
        // try {
        //     \DB::beginTransaction();

        //     $registro = Registro::whereId($request->id)->update(['estado' => 'Eliminado']);

        //     \DB::commit();
            
        //     return response()->json($registro);
        
        // } catch (Exception $e) {
        //     \DB::rollBack();
        //     return response()->json($exception->getMessage());
		// }
    }
    
    // VERIFICAR QUE EL REGISTRO ESTE EN ESTADO ELIMINADO (FUNCIÓN ELIMINADA DEL CONTROLADOR)
    public function concluir_registro($id){
        
    }

    public function devolucion(Request $request){
        $entrada = Entrada::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $total = 0;
            $lista_entdevoluciones = [];
            $items = collect($request->registros);
            $hoy = Carbon::now();
            $items->map(function($item) use(&$lista_entdevoluciones, $entrada, &$total, $hoy){
                $unidades_base = (int) $item['unidades_base'];
                $total_base = (double) $item['total_base'];
                $registro_id = $item['id'];
                if($unidades_base > 0){
                    $lista_entdevoluciones[] = [
                        'entrada_id' => $entrada->id,
                        'registro_id' => $registro_id,
                        'unidades' => $unidades_base,
                        'total' => $total_base,
                        'created_at' => $hoy,
                        'updated_at' => $hoy
                    ];

                    // DISMINUIR UNIDADES PENDIENTE
                    \DB::table('registros')->whereId($registro_id)
                        ->decrement('unidades_pendientes',  $unidades_base);

                    // DISMINUIR PIEZAS DE LOS LIBROS
                    \DB::table('libros')->whereId($item['libro']['id'])
                        ->decrement('piezas', $unidades_base);
                }
                $total += $total_base;
            });

            Entdevolucione::insert($lista_entdevoluciones);

            $entrada->update([
                'total_devolucion' => $entrada->total_devolucion + $total,
                'unidades_devolucion' => $entrada->unidades_devolucion + (int) $request->todo_unidades 
            ]);
            $editorial = Enteditoriale::where('editorial', $entrada->editorial)->first();
            $editorial->update([
                'total_devolucion' => $editorial->total_devolucion + $total,
                'total_pendiente' => $editorial->total_pendiente - $total
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    public function pagos_entrada(){
        // $entradas = \DB::table('entradas')
        //             ->select(
        //                 'editorial',
        //                 // \DB::raw('SUM(unidades) as unidades'),
        //                 \DB::raw('SUM(total) as total'),
        //                 \DB::raw('SUM(total_pagos) as total_pagos'),
        //                 \DB::raw('SUM(total_devolucion) as total_devolucion')
        //             )->groupBy('editorial')->orderBy('editorial', 'asc')->get();
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')->withCount('entdepositos')->get();
        return response()->json($editoriales);
    }

    // GUARDAR PAGO
    public function save_pago(Request $request){
        $editorial = Enteditoriale::whereId($request->enteditoriale_id)->first();
        \DB::beginTransaction();
        try {
            $monto = (double) $request->pago;
            $editorial->update([
                'total_pagos' => $editorial->total_pagos + $monto,
                'total_pendiente' => $editorial->total_pendiente - $monto
            ]);
            $deposito = Entdeposito::create([
                'enteditoriale_id' => $editorial->id,
                'pago' => $monto,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
                'ingresado_por' => auth()->user()->name
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($editorial);
    }

    // ACTUALIZAR PAGO
    public function update_pago(Request $request){
        $entdeposito = Entdeposito::find($request->id);
        $pago = (double) $request->pago;
        $enteditorial = Enteditoriale::find($entdeposito->enteditoriale_id);

        \DB::beginTransaction();
        try {
            $total_pagos = ($enteditorial->total_pagos - $entdeposito->pago) + $pago;
            $total_pendiente = ($enteditorial->total_pendiente + $entdeposito->pago) - $pago;
            $enteditorial->update([
                'total_pagos' => $total_pagos,
                'total_pendiente' => $total_pendiente
            ]);
            $entdeposito->update([
                'pago' => $pago,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($entdeposito);
    }

    // ELIMINAR DEPOSITO
    public function delete_pago(Request $request){
        $entdeposito = Entdeposito::find($request->pago_id);
        $enteditorial = Enteditoriale::find($entdeposito->enteditoriale_id);

        \DB::beginTransaction();
        try {
            $enteditorial->update([
                'total_pagos' => $enteditorial->total_pagos - $entdeposito->pago,
                'total_pendiente' => $enteditorial->total_pendiente + $entdeposito->pago
            ]);
            $entdeposito->delete();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function depositos_enteditoriale(){
        $id = Input::get('id');
        $depositos = Entdeposito::where('enteditoriale_id', $id)
                                ->orderBy('created_at', 'desc')->get();
        return response()->json($depositos);
    }

    public function descargar_gralEdit(){
        return Excel::download(new EAccountExport, 'lista-editoriales.xlsx');
    }

    public function send_devoluciones(Request $request){
        $fecha_actual = Carbon::now();
        $devoluciones = Entdevolucione::where('entrada_id', $request->entrada_id)
                        ->where('created_at', 'like', '%'.$fecha_actual->format('Y-m-d').'%')
                        ->where('estado', 'proceso')
                        ->with('registro.libro', 'entrada')->get();

        $entrada = $devoluciones[0]->entrada;

        $salida = \DB::connection('inventariocdmx')->table('salidas')
                        ->where('folio', $entrada->folio)->first();

        \DB::beginTransaction();
        try {
            $datos = [];
            $devoluciones->map(function($devolucion) use(&$datos, $fecha_actual, $salida){
                $libro = \DB::connection('inventariocdmx')->table('libros')
                        ->where('titulo', $devolucion->registro->libro->titulo)->first();
                
                $libro_id = $libro->id;
                $unidades = $devolucion->unidades;

                $dato = [
                    'salida_id' => $salida->id,
                    'libro_id' => $libro_id,
                    'unidades' => $unidades,
                    'created_at' => $fecha_actual,
                    'updated_at' => $fecha_actual
                ];

                \DB::connection('inventariocdmx')->table('libros')
                        ->where('id', $libro_id)
                        ->increment('piezas', $unidades);

                $devolucion->update([ 'estado' => 'enviado' ]);
                $datos[] = $dato;
            });

            \DB::connection('inventariocdmx')->table('saldevoluciones')
                    ->insert($datos);

            \DB::connection('inventariocdmx')->table('salidas')
                    ->where('folio', $entrada->folio)
                    ->update([
                        'unidades_devolucion' => $entrada->unidades_devolucion
                    ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }
}
