<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Proveedor extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "proveedores";
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre',
        'direccion',
        'correo',
        'telefono'
    ];
    public $timestamps = true;

    public function getAllProveedores($orden=null){
        if($orden==null){
            switch(session("ordenProveedores")){
                case "NombreAZ": $orden="nombre"; $direccion="asc";break;
                case "NombreZA": $orden="nombre"; $direccion="desc";break;
                case "MasReciente": $orden="fecha_compra"; $direccion="desc";break;
                case "MenosReciente": $orden="fecha_compra"; $direccion="asc";break;
                default: $orden="nombre"; $direccion="asc";
            };
        }else{
            switch($orden){
                case "NombreAZ": $orden="nombre"; $direccion="asc";break;
                case "NombreZA": $orden="nombre"; $direccion="desc";break;
                case "MasReciente": $orden="fecha_compra"; $direccion="desc";break;
                case "MenosReciente": $orden="fecha_compra"; $direccion="asc";break;
                default: $orden="nombre"; $direccion="asc";
            };
        }

        $query = $this->select("id", "nombre")
                      ->addSelect(DB::raw("
                        (
                         SELECT MAX(fecha_compra)
                            FROM compras
                            WHERE compras.proveedor = proveedores.id
                        ) AS fecha_compra
                      "));
        if ($orden === 'fecha_compra') {
            $query->orderBy('fecha_compra', $direccion)
                ->orderBy('nombre', 'asc');
        } else {
            $query->orderBy($orden, $direccion);
        }
        return $query->paginate(15);
    }

    public function getAllNameProveedores(){
        return $this->select("id","nombre")->orderBy("nombre","asc")->get();
    }

    public function productos() {
        return $this->belongsToMany(Producto::class, "proveedores_productos", "proveedor", "producto");
    }

    public function compras() {
        return $this->hasMany(Compra::class, "proveedor");
    }
    public function getProveedor($idProveedor){

        return $this::with([
                    'productos' => function ($q) {
                        $q->select(
                            'productos.id',
                            'productos.nombre',
                            'productos.cantidad_disponible',
                            "cantidad_minima",
                            'productos.tipo_medida'
                        )
                        ->with(["foto" => function($q){$q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");}])
                        ->whereNull('proveedores_productos.deleted_at')
                        ->with([
                            'productoCompra' => function ($q) {
                                $q->select(
                                    'productos_compras.id',
                                    'productos_compras.producto',
                                    'productos_compras.compra',
                                    'productos_compras.precio_compra'
                                )
                                ->with([
                                    'productosCompra' => function ($q) {
                                        $q->select(
                                            'compras.id',
                                            'compras.proveedor',
                                            'compras.fecha_compra'
                                        );
                                    },
                                ])
                                ->orderByDesc('productos_compras.id')
                                ->limit(1);
                            },
                        ]);
                    },
                ])
                ->select('proveedores.id', 'proveedores.nombre', 'proveedores.direccion', 'proveedores.correo', 'proveedores.telefono')
                ->where('id', $idProveedor)
                ->first();
    }

    public function getProveedorCompra($idProv){
        return $this->select("proveedores.id","proveedores.nombre")
        ->where("id","=",$idProv)
        ->first();
    }

    public function getAllProdsProv($idProv){
        return $this::with([
                        'productos' => function ($q) {
                            $q->select(
                                'productos.id',
                            )
                            ->whereNull('proveedores_productos.deleted_at');
                        }
                    ])
                    ->select("id")
                    ->where("id","=",$idProv)
                    ->get();
    }

    public function newProveedor($proveedor){
        return ($this->create($proveedor))->id;
    }
    public function editProveedor($proveedor,$idProv){
        return $this->where("id","=",$idProv)->update($proveedor);
    }
    public function deleteProveedor($idProv){
        $prov = $this->find($idProv);
        if($prov){
            return $prov->delete();
        }
        return false;
    }

    public function proveedorProductos(){
        return $this->hasMany(ProveedorProducto::class, 'proveedor');
    }

    protected static function booted(){
        static::deleting(function ($proveedor) {
            $proveedor->proveedorProductos()->delete();
        });
    }
}
