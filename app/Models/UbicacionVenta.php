<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UbicacionVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "ubicaciones_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'venta',
        'lat',
        'lng',
        'direccion',
        'casa_depto',
        'manzana_piso',
        'descripcion',
        'fecha_entrega_min',
        'fecha_entrega_max',
        'fecha_entrega'
    ];
    public $timestamps = true;

    public function newUbicacionVenta($newUbicV){
        return ($this->create($newUbicV))->id;
    }
    public function editarUbicacionVenta($newUbcVent,$idVent){
        return ($this->where("venta","=",$idVent)->update($newUbcVent));
    }

    public function UbicacionVenta(){
        return $this->belongsTo(Venta::class, 'venta');
    }
}
