<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Donacione;

class Regalo extends Model
{
    protected $fillable = [
        'id', 
        'plantel',
        'descripcion', 
        'unidades',  
        'entregado_por'
    ];

    //Uno a muchos
    //Una promoción puede tener muchas salidas
    public function donaciones(){
        return $this->hasMany(Donacione::class);
    }
}
