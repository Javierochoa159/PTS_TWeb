<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecibosVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = "recibos_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'venta',
        'url_img',
        'url_img_online',
    ];
    public $timestamps = true;

    public function newReciboVenta($newRecV){
        return ($this->create($newRecV))->id;
    }
    public function getAllRecibosVenta($idVenta){
        return $this->where("venta","=",$idVenta)->select("id","url_img","url_img_online")->get();
    }

    public function recibosVentas(){
        return $this->belongsTo(Venta::class, 'venta');
    }
}
