<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Remcliente;
use App\Foto;

class Remdeposito extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'id', 
        'remcliente_id', 
        'corte_id',
        'pago',
        'fecha',
        'nota',
        'revisado',
        'ingresado_por'
    ];

    // Uno a muchos (Inverso)
    public function remcliente(){
        return $this->belongsTo(Remcliente::class);
    }

    public function foto(){
        return $this->hasOne(Foto::class);
    }
}
