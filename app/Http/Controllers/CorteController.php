<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
// use Spatie\Dropbox\Client;
use App\Remdeposito;
use App\Remcliente;
use App\Remisione;
use App\Deposito;
use App\Cctotale;
use App\Corte;
use App\Foto;

class CorteController extends Controller
{
    public function __construct()
    {
        // Necesitamos obtener una instancia de la clase Client la cual tiene algunos métodos
        // que serán necesarios.
        $this->dropbox = Storage::disk('dropbox')->getDriver()->getAdapter()->getClient();   
    }

    // Obtener todos los cortes (PAGINADO)
    public function index() {
        $cortes = Corte::orderBy('inicio', 'desc')->paginate(50);
        return response()->json($cortes);
    }

    // OBTENER TODOS LOS CORTES
    public function get_all(){
        $cortes = Corte::orderBy('inicio', 'desc')->get();
        return response()->json($cortes);
    }

    // OBTENER CORTES DEL CLIENTE
    public function list_bycliente(Request $request){
        $cctotales = Cctotale::where('cliente_id', $request->cliente_id)
                        ->select('corte_id')->get();
        $ids = [];
        $cctotales->map(function($cctotale) use(&$ids){
            $ids[] = $cctotale->corte_id;
        });
        $cortes = Corte::whereIn('id', $ids)->orderBy('inicio', 'desc')
                        ->get();
        return response()->json($cortes);
    }

    // Obtener detalles de un corte
    public function show(Request $request){
        $cctotales = Cctotale::where('corte_id', $request->corte_id)
                                ->with('cliente', 'corte')->get();
        
        $clientes = collect($this->org_remisiones($cctotales));
        return response()->json($clientes);
    }

    // Obtener un corte
    public function show_one(Request $request){
        $cctotale = $this->get_cctotale($request->corte_id, $request->cliente_id);
        return response()->json($cctotale);
    }

    // ORGANIZAR REMISIONES POR CLIENTE
    public function org_remisiones($cctotales){
        $clientes = [];
        $cctotales->map(function($cctotale) use(&$clientes){
            $remcliente = Remcliente::where('cliente_id', $cctotale->cliente_id)->first();
            $remdepositos = Remdeposito::where('remcliente_id', $remcliente->id)
                                ->where('corte_id', $cctotale->corte_id)
                                ->with('foto')->orderBy('created_at', 'desc')->get();
            $remisiones = Remisione::where('corte_id', $cctotale->corte_id)
                            ->where('cliente_id', $cctotale->cliente_id)
                            ->where(function ($query) {
                                $query->where('estado', '=', 'Proceso')
                                    ->orWhere('estado', '=', 'Terminado');
                            })->orderBy('id', 'desc')->get();
            
            $corte_title = null;
            if($cctotale->total_favor > 0) {
                $cf = Corte::find($cctotale->corte_id_favor);
                $corte_title = $cf->tipo.': '.$cf->inicio.'/'.$cf->final;
            }
            $datos = [
                'visible' => false,
                'corte_id' => $cctotale->corte_id,
                'corte' => $cctotale->corte->tipo,
                'inicio' => $cctotale->corte->inicio,
                'final' => $cctotale->corte->final,
                'cliente_id' => $cctotale->cliente_id,
                'cliente' => $cctotale->cliente->name,
                'total' => $cctotale->total, 
                'total_devolucion' => $cctotale->total_devolucion, 
                'total_pagos' => $cctotale->total_pagos,
                'total_pagar' => $cctotale->total_pagar,
                'total_favor' => $cctotale->total_favor,
                'corte_id_favor' => $corte_title,
                'remisiones' => $remisiones,
                'remdepositos' => $remdepositos
            ];
            $clientes[] = $datos;
        });
        return $clientes;
    }

    // Obtener detalles de un corte de un cliente
    public function show_bycliente(Request $request){
        $cctotales = Cctotale::where('corte_id', $request->corte_id)
                                ->where('cliente_id', $request->cliente_id)
                                ->with('cliente', 'corte')->get();
        $clientes = collect($this->org_remisiones($cctotales));
        return response()->json($clientes);
    }

    // Crear corte
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $corte = Corte::create($request->all());

            $remclientes = Remcliente::orderBy('cliente_id', 'asc')
                            ->where('total', '>', 0)->get();
            
            $remclientes->map(function($remcliente) use($corte){
                Cctotale::create([
                    'corte_id' => $corte->id, 
                    'cliente_id' => $remcliente->cliente_id
                ]);
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    // Actualizar corte
    public function update(Request $request){
        $corte = Corte::find($request->id);
        \DB::beginTransaction();
        try {
            $corte->update([
                'tipo' => $request->tipo,
                'inicio' => $request->inicio,
                'final' => $request->final
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($corte);
    }

    // OBTENER REMISIONES DISPONIBLES
    public function get_remisiones(){
        $remisiones = Remisione::where('corte_id', 0)
                        ->where(function ($query) {
                            $query->where('estado', '=', 'Proceso')
                                ->orWhere('estado', '=', 'Terminado');
                        })->with('cliente')
                        ->orderBy('id', 'desc')->paginate(20);
        return response()->json($remisiones);
    }

    // OBTENER REMISIONES DISPONIBLES POR CLIENTE
    public function rems_bycliente(Request $request){
        $remisiones = Remisione::where('cliente_id', $request->cliente_id)
                            ->where('corte_id', 0)
                            ->where(function ($query) {
                                $query->where('estado', '=', 'Proceso')
                                    ->orWhere('estado', '=', 'Terminado');
                            })->with('cliente')
                            ->orderBy('id', 'desc')->paginate(20);
        return response()->json($remisiones);
    }

    // GUARDAR CLASIFICACIÓN DE REMISIONES
    public function clasificar_rems(Request $request){
        $remisiones = collect($request->remisiones);
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;

        $totales = [
            'total' => 0, 
            'total_devolucion' => 0, 
            'total_pagos' => 0,
            'total_pagar' => 0
        ];

        \DB::beginTransaction();
        try {
            $remisiones->map(function($remision) use(&$totales, $corte_id){
                $remision = Remisione::find($remision['id']);
                $depositos = Deposito::where('remisione_id', $remision->id)->get();
        
                $total_pagos = 0;
                $total_pagar = $remision->total;
                $total_devolucion = $remision->total_devolucion;
 
                if($depositos->count() > 0) $total_pagos = $depositos->sum('pago');
                if($depositos->count() > 0 || $total_devolucion > 0)
                    $total_pagar = $remision->total - ($total_pagos + $total_devolucion);

                $totales['total'] += $remision->total;
                $totales['total_devolucion'] += $total_devolucion;
                $totales['total_pagos'] += $total_pagos;
                $totales['total_pagar'] += $total_pagar;
                $remision->update([
                    'corte_id' => $corte_id
                ]);
            });
    
            $cctotale = $this->get_cctotale($corte_id, $cliente_id);

            $cctotale->update([
                'total' => $cctotale->total + $totales['total'], 
                'total_devolucion' => $cctotale->total_devolucion + $totales['total_devolucion'], 
                'total_pagos' => $cctotale->total_pagos + $totales['total_pagos'],
                'total_pagar' => $cctotale->total_pagar + $totales['total_pagar']
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json();
    }

    // Obtener cctotale
    public function get_cctotale($corte_id, $cliente_id){
        return Cctotale::where([
            'corte_id' => $corte_id,
            'cliente_id' => $cliente_id
        ])->first();
    }

    // OBTENER PAGOS GRALS DISPONIBLES
    public function get_pagos(){
        $remdepositos = Remdeposito::where('corte_id', 0)
                        ->with('remcliente.cliente')
                        ->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($remdepositos);
    }

    // OBTENER PAGOS DE UN CLIENTE
    public function pagos_bycliente(Request $request){
        $remcliente = Remcliente::where('cliente_id', $request->cliente_id)->first();
        if($remcliente != null){
            $remdepositos = Remdeposito::where('corte_id', 0)
                        ->where('remcliente_id', $remcliente->id)
                        ->with('remcliente.cliente')
                        ->orderBy('created_at', 'desc')->paginate(20);
            return response()->json($remdepositos);
        }
        return response()->json(false);
    }

    // GUARDAR PAGOS SELECCIONADOS
    public function clasificar_pagos(Request $request){
        $pagos = collect($request->pagos);
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;
        $total_pagos = 0;

        \DB::beginTransaction();
        try {
            $pagos->map(function($pago) use(&$total_pagos, $corte_id){
                $remdeposito = Remdeposito::find($pago['id']);
                $total_pagos += $remdeposito->pago;
                $remdeposito->update([
                    'corte_id' => $corte_id
                ]);
            });

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $total_pagos);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json(1);
    }

    // VALIDAR CORTE A FAVOR
    public function validate_favor($corte_id, $cliente_id, $corte_id_favor, $total_pagos){
        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        if($corte_id_favor == null) {
            $this->update_cctotale($cctotale, $total_pagos);
        } else {
            $total_favor = $total_pagos - $cctotale->total_pagar;
            $cctotale->update([
                'corte_id_favor' => $corte_id_favor,
                'total_favor' => $cctotale->total_favor + $total_favor,
                'total_pagos' => $cctotale->total_pagos + $cctotale->total_pagar,
                'total_pagar' => 0
            ]);

            $cctotale_favor = $this->get_cctotale($corte_id_favor, $cliente_id);
            $this->update_cctotale($cctotale_favor, $total_favor);
        }
    }

    // Actualizar cctotale
    public function update_cctotale($cctotale, $total_pagos){
        $cctotale->update([
            'total_pagos' => $cctotale->total_pagos + $total_pagos,
            'total_pagar' => $cctotale->total_pagar - $total_pagos
        ]);
    }

    // verificar si es mayor o menor
    public function verify_totales(Request $request){
        $cctotale = $this->get_cctotale($request->corte_id, $request->cliente_id);
        if($cctotale->total_pagar == 0) return response()->json(2);
        if($cctotale->total_pagar > 0 && ((double)$request->total_selected > $cctotale->total_pagar)) return response()->json(3);
        
        return response()->json(1);
    }

    // MANDAR A LA VISTA DE DETALLES DE CORTES DEL CLIENTE
    public function details_cliente($cliente_id){
        return view('information.cortes.details-cliente', compact('cliente_id'));
    } 

    // OBTENER CORTES DEL CLIENTE
    public function by_cliente(Request $request){
        $cliente_id = $request->cliente_id;
        $remcliente = Remcliente::where('cliente_id', $cliente_id)
                            ->with('cliente')->first();
        $cctotales = Cctotale::where('cliente_id', $cliente_id)
                            ->with('cliente', 'corte')
                            ->orderBy('corte_id', 'desc')->get();
        $cortes = $this->org_remisiones($cctotales);
        $data = [
            'cliente_id' => $cliente_id,
            'name'  => $remcliente->cliente->name,
            'total' => $remcliente->total,
            'total_pagos' => $remcliente->total_pagos,
            'total_devolucion' => $remcliente->total_devolucion,
            'total_pagar' => $remcliente->total_pagar,
            'cortes' => $cortes
        ];    
        return response()->json($data);
    }

    // PAGO AL CORTE
    public function save_payment(Request $request){
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;
        
        $remcliente = Remcliente::where('cliente_id', $cliente_id)->first();
        
        try{
            \DB::beginTransaction();

            $monto = (float) $request->pago;
            
            Remdeposito::create([
                'remcliente_id' => $remcliente->id,
                'corte_id' => $corte_id,
                'pago' => $monto,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
                'ingresado_por' => auth()->user()->name
            ]);

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $monto);

            $total_pagar = $remcliente->total_pagar - $monto;
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos + $monto, 
                'total_pagar' => $total_pagar
            ]);

            if((float) $total_pagar <= 0){
                $this->cerrar_remisiones($cliente_id, $corte_id);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    // CERRAR REMISIONES
    public function cerrar_remisiones($cliente_id, $corte_id){
        $remisiones = Remisione::where([
            'cliente_id' => $cliente_id, 
            'corte_id' => $corte_id,
            'estado' => 'Proceso'
        ])->get();

        $remisiones->map(function($remision){
            $remision->update([
                'pagos' => $remision->pagos + $remision->total_pagar,
                'total_pagar'   => 0,
                'estado' => 'Terminado'
            ]);
        });
    }

    // MOVER REMISION
    public function move_rem(Request $request){
        $remision = Remisione::find($request->remisione_id);
        $cliente_id = $remision->cliente_id;
        $corte_id = $request->corte_id;

        if($corte_id != $remision->corte_id){
            $total = $remision->total;
            $total_pagar = $remision->total_pagar;

            $anterior = $this->get_cctotale($remision->corte_id, $cliente_id);
            $anterior->update([
                'total' => $anterior->total - $total,
                'total_pagar' => $anterior->total_pagar - $total_pagar
            ]);

            $nuevo = $this->get_cctotale($corte_id, $cliente_id);
            $nuevo->update([
                'total' => $nuevo->total + $total,
                'total_pagar' => $nuevo->total_pagar + $total_pagar
            ]);

            $remision->update([ 'corte_id' => $corte_id ]);
            return response()->json(true);
        }
        return response()->json(false);
    }

    // MOVER PAGO
    public function move_pago(Request $request){
        $remdeposito = Remdeposito::find($request->pago_id);
        $pago = $remdeposito->pago;
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;

        if($corte_id != $remdeposito->corte_id){
            $anterior = $this->get_cctotale($remdeposito->corte_id, $cliente_id);
            $anterior->update([
                'total_pagos' => $anterior->total_pagos - $pago,
                'total_pagar' => $anterior->total_pagar + $pago
            ]);

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $pago);
            $remdeposito->update([ 'corte_id' => $corte_id ]);
            return response()->json(true);
        }
        return response()->json(false);
    }

    // EDITAR PAGO
    public function edit_payment(Request $request){
        $remdeposito = Remdeposito::find($request->id);
        $remcliente = Remcliente::find($remdeposito->remcliente_id);

        $pago = (float) $request->pago;
        $cliente_id = $remcliente->cliente_id;
        $corte_id = $remdeposito->corte_id;

        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        try{
            \DB::beginTransaction();
            // CCTOTALE
            $c_total_pagos = ($cctotale->total_pagos - $remdeposito->pago) + $pago;
            $c_total_pagar = ($cctotale->total_pagar + $remdeposito->pago) - $pago;
            $cctotale->update([
                'total_pagos' => $c_total_pagos,
                'total_pagar' => $c_total_pagar
            ]);
            // REMCLIENTE
            $r_total_pagos = ($remcliente->total_pagos - $remdeposito->pago) + $pago;
            $r_total_pagar = ($remcliente->total_pagar + $remdeposito->pago) - $pago;
            $remcliente->update([
                'total_pagos' => $r_total_pagos,
                'total_pagar' => $r_total_pagar
            ]);

            // REMDEPOSITO
            $remdeposito->update([
                'pago' => $pago,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
            ]);

            // CERRAR REMISIONES
            if((float) $cctotale->total_pagar == 0){
                $this->cerrar_remisiones($cliente_id, $corte_id);
            }
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remdeposito);
    }

    // ELIMINAR PAGO
    public function delete_payment(Request $request){
        $remdeposito = Remdeposito::find($request->pago_id);
        $remcliente = Remcliente::find($remdeposito->remcliente_id);

        $cliente_id = $remcliente->cliente_id;
        $corte_id = $remdeposito->corte_id;

        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        try{
            \DB::beginTransaction();
            // CCTOTALE
            $cctotale->update([
                'total_pagos' => $cctotale->total_pagos - $remdeposito->pago,
                'total_pagar' => $cctotale->total_pagar + $remdeposito->pago
            ]);
            // REMCLIENTE
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos - $remdeposito->pago,
                'total_pagar' => $remcliente->total_pagar + $remdeposito->pago
            ]);

            // REMDEPOSITO
            $remdeposito->delete();
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function upload_payment(Request $request){
        // VALIDACIÓN DE DATOS
        $this->validate($request, [
            'file' => ['required', 'mimes:jpg,png,jpeg', 'max:3072']
        ]);

        \DB::beginTransaction();
        try {
            $remdeposito = Remdeposito::find($request->pagoid);
            // SUBIR IMAGEN
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $name_file = "id-".$remdeposito->id."_".time().".".$extension;
    
            $image = Image::make($request->file('file'));
            $image->resize(1280, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
    
            Storage::disk('dropbox')->put(
                '/stock2/'.$name_file, (string) $image->encode('jpg', 30)
            );
            
            $response = $this->dropbox->createSharedLinkWithSettings(
                '/stock2/'.$name_file, 
                ["requested_visibility" => "public"]
            );
    
            $foto = Foto::create([
                'remdeposito_id' => $remdeposito->id,
                'name' => $response['name'],
                'extension' => $extension,
                'size' => $response['size'],
                'public_url' => $response['url']
            ]);
            \DB::commit();
        }  catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($foto);
    }
}
