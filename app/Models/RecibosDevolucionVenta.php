<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RecibosDevolucionVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "recibos_devoluciones_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'devolucion',
        "url_img",
        "url_img_online"
    ];
    public $timestamps = true;

    public function newReciboDevVenta($recDevVent){
        return $this->create($recDevVent)->id;
    }
    public function getAllRecibosDevVenta($idVenta){
        return $this->select("recibos_devoluciones_ventas.id",
                             "recibos_devoluciones_ventas.url_img",
                             "recibos_devoluciones_ventas.url_img_online")
                    ->join("devoluciones_ventas","devoluciones_ventas.id","=","recibos_devoluciones_ventas.devolucion")
                    ->where("devoluciones_ventas.venta","=",$idVenta)
                    ->get();
    }

    public function recibosDevVentas(){
        return $this->belongsTo(DevolucionVenta::class, 'devolucion');
    }
}
