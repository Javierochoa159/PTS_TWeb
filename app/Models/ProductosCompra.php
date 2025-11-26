<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductosCompra extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "productos_compras";
    protected $primaryKey = 'id';
    protected $fillable = [
        'producto',
        'compra',
        "cantidad",
        "precio_compra",
        "total_producto"
    ];
    public $timestamps = true;

    public function newProductoCompra($prodComp){
        return $this->create($prodComp);
    }
    public function editProductoCompra($prodComp,$idProdComp){
        return $this->where("id","=",$idProdComp)->update($prodComp);
    }
    public function getAllProductosCompra($idCompra){
        return $this->select("id","producto","deleted_at")->where("compra","=",$idCompra)->withTrashed()->get();
    }
    public function deleteProductoCompra($idProdCompra){
        return $this->where("id","=",$idProdCompra)->delete();
    }
    public function removeDeletedAt($idProdCompra){
        return $this->where("id","=",$idProdCompra)->restore();
    }
    public function productoRelacion(){
        return $this->belongsTo(Producto::class, 'producto');
    }

    public function productoCompras(){
        return $this->belongsTo(Compra::class, 'producto');
    }
    public function productosCompra(){
        return $this->belongsTo(Compra::class, 'compra');
    }
}
