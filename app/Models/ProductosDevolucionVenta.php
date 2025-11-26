<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductosDevolucionVenta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "productos_devoluciones_ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'producto',
        'devolucion',
        "tipo_devolucion",
        "motivo_devolucion",
        "cantidad",
        "total_producto"
    ];
    public $timestamps = true;

    public function newProductoDevolucion($prodDev){
        return $this->create($prodDev);
    }
    public function getAllProductosDevolucion($idDevolucion){
        return $this->select("id","producto","deleted_at")->where("devolucion","=",$idDevolucion)->get();
    }

    public function deleteDevolucion($idDevolucion){
        $devolucion = $this->find($idDevolucion);
        if($devolucion){
            return $devolucion->delete();
        }
        return false;
    }

    public function productoDevoluciones(){
        return $this->belongsTo(DevolucionVenta::class, 'producto');
    }
    public function productoRelacion(){
        return $this->belongsTo(Producto::class, 'producto');
    }
    public function productosDevolucion(){
        return $this->belongsTo(ProductosDevolucionVenta::class, 'devolucion');
    }

}
