<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Promotion;
use App\Entrada;
use App\Regalo;
use App\Order;
use App\Note;

class VisitorController extends Controller
{
    // REMISIONES
    public function remisiones(){
        return view('visitor.remisiones');
    }

    // CORTES
    public function cortes(){
        return view('visitor.cortes');
    }

    // FECHA DE ADEUDOS DE REMISIONES
    public function fecha_adeudo(){
        return view('visitor.fecha-adeudo');
    }

    // OBTENER LIBROS
    public function libros(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('visitor.libros', compact('editoriales'));
    }

    // OBTENER ENTRADAS
    public function entradas(){
        $entradas = Entrada::with('registros')
            ->whereNotIn('editorial', ['MAJESTIC EDUCATION'])->orderBy('id','desc')->get();
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('visitor.entradas', compact('entradas', 'editoriales'));
    }

    // VISTA CLIENTES
    public function clientes(){
        return view('visitor.clientes');
    }

    // OBTENER CLIENTES
    public function notas(){
        $notes = Note::orderBy('folio','desc')->get();
        return view('visitor.notas', compact('notes'));
    }

    // OBTENER PEDIDOS
    public function pedidos(){
        $pedidos = Order::orderBy('created_at', 'desc')->get();
        return view('visitor.pedidos', compact('pedidos'));
    }

    // OBTENER PROMOCIONES
    public function promociones(){
        $promotions = Promotion::with('departures')->orderBy('folio','desc')->get();
        return view('visitor.promociones', compact('promotions'));
    }

    // OBTENER DONACIONES
    public function donaciones(){
        return view('visitor.donaciones');
    }
}
