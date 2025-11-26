<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecibosCompra extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "recibos_compras";
    protected $primaryKey = 'id';
    protected $fillable = [
        'url_img',
        'url_img_online',
        'compra'
    ];
    public $timestamps = true;

    public function getAllRecibosCompra($idCompra){
        return $this->where("compra","=",$idCompra)->select("id","url_img","url_img_online")->get();
    }
    public function newReciboCompra($recibo){
        return $this->create($recibo);
    }
    public function deleteFoto($idRecibo){
        return $this->where("id","=",$idRecibo)->delete();
    }
}
