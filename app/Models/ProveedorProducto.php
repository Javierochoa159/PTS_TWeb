<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProveedorProducto extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "proveedores_productos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'proveedor',
        'producto'
    ];
    public $timestamps = true;

    public function newProveedorProducto($provProd){
        return ($this->create($provProd))->id;
    }
    public function getAllProveedoresProducto($idProd){
        return $this->select("id","proveedor","deleted_at")->where("producto","=",$idProd)->withTrashed()->get();
    }
    public function deleteProveedorProducto($idProvProd){
        return $this->where("id","=",$idProvProd)->delete();
    }
    public function removeDeletedAt($idProvProd){
        return $this->where("id","=",$idProvProd)->restore();
    }
}
