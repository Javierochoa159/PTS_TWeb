<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductosVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "productos_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'producto',
        'venta',
        'cantidad',
        'precio_venta',
        'total_producto',
    ];
    public $timestamps = true;

    public function newProductoVenta($newProdV){
        return ($this->create($newProdV))->id;
    }

    public function productoRelacion(){
        return $this->belongsTo(Producto::class, 'producto');
    }

    public function productoVentas(){
        return $this->belongsTo(Venta::class, 'producto');
    }
    public function productosVenta(){
        return $this->belongsTo(Venta::class, 'venta');
    }

    public function getProductoVentaDev($idProd,$idVenta){
        return $this::where("venta","=",$idVenta)
                     ->where("producto","=",$idProd)
                     ->with([
                        "productoRelacion" => function($q){
                            $q->select(
                                "productos.id",
                                "productos.nombre",
                                "productos.tipo_medida"
                            )
                            ->with([
                                "foto" => function($q){
                                    $q->select(
                                        "fotos.id",
                                        "fotos.url_img",
                                        "fotos.url_img_online",
                                        "fotos.producto"
                                    );
                                }
                            ]);
                        }
                     ])
                     ->select(
                        "productos_ventas.id",
                        "productos_ventas.venta",
                        "productos_ventas.producto",
                        "productos_ventas.cantidad",
                        "productos_ventas.total_producto"
                     )
                     ->first();  
    }
}
