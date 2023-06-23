<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Remisione;
use App\Devolucione;
use App\Libro;
use App\Vendido;

class Dato extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'id', 
        'remisione_id', 
        'libro_id', 
        'costo_unitario',
        'unidades', 
        'total'
    ];

    //Uno a muchos (inversa)
    //Un dato solo puede pertencer a una remisión
    public function remisione(){
        return $this->belongsTo(Remisione::class);
    }

    //Uno a uno
    //Un dato solo puede pertenecer a una devolucion
    public function devolucione(){
        return $this->hasOne(Devolucione::class);
    }

    //Uno a muchos (Inversa)
    //Un dato solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    //Uno a uno
    //Un dato solo puede pertenecer a una vendido
    public function vendido(){
        return $this->hasOne(Vendido::class);
    }
}
