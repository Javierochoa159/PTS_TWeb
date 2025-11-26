<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevolucionVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "devoluciones_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'venta',
        'tipo_pago',
        'monto_total',
        'fecha_devolucion'
    ];
    public $timestamps = true;

    public function newDevVenta($devVenta){
        return ($this->create($devVenta))->id;
    }

    public function editDevVenta($devVenta,$idDevVenta){
        return $this->where("id","=",$idDevVenta)->update($devVenta);
    }

    public function productos() {
        return $this->hasMany(ProductosDevolucionVenta::class, "devolucion");
    }
    public function recibos() {
        return $this->hasMany(RecibosDevolucionVenta::class, "devolucion");
    }

    public function getAllDevolucionesVenta($idVenta){
        $devs=$this::where("venta","=",$idVenta)
                     ->with([
                        "recibos" => function($q) {
                            $q->select(
                                "recibos_devoluciones_ventas.url_img",
                                "recibos_devoluciones_ventas.url_img_online",
                                "recibos_devoluciones_ventas.devolucion"
                            );
                        },
                        "productos" => function($q){
                            $q->select(
                                "productos_devoluciones_ventas.producto",
                                "productos_devoluciones_ventas.devolucion",
                                "productos_devoluciones_ventas.motivo_devolucion",
                                "productos_devoluciones_ventas.tipo_devolucion",
                                "productos_devoluciones_ventas.cantidad",
                                "productos_devoluciones_ventas.total_producto",
                            );
                        }
                    ])
                    ->select(
                        "devoluciones_ventas.id",
                        "devoluciones_ventas.venta",
                        "devoluciones_ventas.tipo_pago",
                        "devoluciones_ventas.monto_total",
                        "devoluciones_ventas.fecha_devolucion",
                    )
                    ->get();
        return $devs;
    }
    public function getDevolucion($idDev){
        $dev = $this->where("id","=",$idDev)
                    ->with([
                        "recibos" => function($q) {
                            $q->select(
                                "recibos_devoluciones_ventas.url_img",
                                "recibos_devoluciones_ventas.url_img_online",
                                "recibos_devoluciones_ventas.devolucion"
                            );
                        },
                        "productos" => function($q){
                            $q->select(
                                "productos_devoluciones_ventas.producto",
                                "productos_devoluciones_ventas.devolucion",
                                "productos_devoluciones_ventas.motivo_devolucion",
                                "productos_devoluciones_ventas.tipo_devolucion",
                                "productos_devoluciones_ventas.cantidad",
                                "productos_devoluciones_ventas.total_producto",
                            )
                            ->with([
                                "productoRelacion" => function($q){
                                    $q->select(
                                        "productos.id",
                                        "productos.tipo_medida",
                                    )
                                    ->with([
                                        "foto" => function($q){
                                            $q->select(
                                                "fotos.producto",
                                                "fotos.url_img",
                                                "fotos.url_img_online",
                                            );
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ])
                    ->select(
                        "devoluciones_ventas.id",
                        "devoluciones_ventas.venta",
                        "devoluciones_ventas.tipo_pago",
                        "devoluciones_ventas.monto_total",
                        "devoluciones_ventas.fecha_devolucion",
                    );
        $dev=$dev->orderBy("id");
        $dev=$dev->first();
        return $dev;
    }

    protected static function booted(){
        static::deleting(function ($devolucion) {
            $devolucion->productos()->delete();
            $devolucion->recibos()->delete();
        });
    }
}
