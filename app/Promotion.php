<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Departure;

class Promotion extends Model
{
    protected $fillable = [
        'id', 
        'folio',
        'plantel',
        'descripcion', 
        'unidades',  
        'unidades_pendientes',
        'entregado_por'
    ];

    //Uno a muchos
    //Una promoción puede tener muchas salidas
    public function departures(){
        return $this->hasMany(Departure::class);
    }
}
