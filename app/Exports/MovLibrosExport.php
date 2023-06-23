<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MovLibrosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($editorial)
    {
        $this->editorial = $editorial;
    }

    public function view(): View
    {
        $movimientos = $this->movimientos_por_edit($this->editorial);
        return view('download.excel.libros.movimientos', [
            'movimientos' => $movimientos
        ]);
    }

    // MOVIMIENTOS DE UN LIBRO
    public function movimientos_por_edit($editorial){
        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        $movimientos = $this->busqueda_unidades($libros);
        return $movimientos;
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
            'ISBN' => '',
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
        $relacion['ISBN'] = $libro->ISBN;
        $relacion['existencia'] = $libro->piezas;
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
}
