<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Producto extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "productos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'precio_venta',
        'tipo_medida',
        'cantidad_disponible',
        'cantidad_minima',
        'proveedor',
        'categoria'
    ];
    public $timestamps = true;

    public function getAllProductos($orden,$pagina="")
    {
        $query = $this->select(
                        "productos.id",
                        "nombre",
                        "codigo",
                        "precio_venta",
                        "tipo_medida",
                        "cantidad_disponible",
                        "cantidad_minima",
                        "categoria"
                        )
                        ->with(["foto" => function($q) {
                            $q->select("fotos.id", "fotos.url_img", "fotos.url_img_online", "fotos.producto");
                        }]);
        if(strcmp($pagina,"todo")!=0){
            $query = $query->where("cantidad_disponible", ">", 0);
        } 
        if ($orden['orden'] === 'mas_vendidos') {
            $now = Carbon::now();
            $añoAnterior = $now->copy()->year - 1;
            $haceUnMes = $now->copy()->subDays(30)->setTimeFromTimeString("00:00");
            [$inicio, $fin] = $this->getRangoEstacion($now->month, $añoAnterior);
            $query = $query->addSelect([
                "mas_vendidos" => DB::table('productos_ventas')
                                    ->selectRaw('COALESCE(COUNT(DISTINCT productos_ventas.venta), 0)')
                                    ->join('ventas', 'productos_ventas.venta', '=', 'ventas.id')
                                    ->whereColumn('productos_ventas.producto', 'productos.id')
                                    ->where(function($q) use ($inicio, $fin, $haceUnMes, $now) {
                                        $q->whereBetween('ventas.fecha_venta', [$inicio, $fin])
                                            ->orWhereBetween('ventas.fecha_venta', [$haceUnMes, $now]);
                                    })
                                    ->where("ventas.venta_invalida","=","0")
            ]);
            $query = $query->orderBy("mas_vendidos", $orden['direccion'])
                  ->orderBy('precio_venta', 'asc');
        } else {
            $query = $query->orderBy($orden['orden'], $orden['direccion']);
        }
        $productos = $query->paginate(24);
        return $productos;
    }

    public function getProductoToCart($idProd){
        return $this::select("productos.id","productos.nombre","productos.codigo","productos.cantidad_disponible","cantidad_minima","productos.precio_venta","productos.tipo_medida")
        ->with(["foto" => function($q){$q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");}])
        ->where("productos.id","=",$idProd)
        ->first()
        ->toArray();
    }

    public function buscarProductos($orden,$pagina,$catego=null,$nomCod=null){
        if($catego){
            $categoria = Categoria::with('hijos')->find($catego);
            $allIds = $categoria->allChildrenIds();
        }
        if(isset($allIds)){
            $producto = Producto::whereIn('categoria', $allIds)
                        ->with(["foto" => function($q){$q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");}]);
        }else{
            $producto = $this->with(["foto" => function($q){$q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");}]);
        }
        $producto = $producto->select(
                                "productos.id",
                                "productos.nombre",
                                "productos.precio_venta",
                                "productos.tipo_medida",
                                "productos.cantidad_disponible",
                                "cantidad_minima",
                                "productos.codigo"
                            );
        if(strcmp($pagina,"todo")!=0){
            $producto = $producto->where("productos.cantidad_disponible",">","0");
        }
        if($nomCod!=null){
            $producto = $producto->where(function($q) use ($nomCod) {
                                    $q->whereLike("productos.nombre", "%{$nomCod}%",true)
                                    ->orWhereLike("productos.codigo", "%{$nomCod}%",true);
                                });
        }
        if ($orden['orden'] === 'mas_vendidos') {
            $now = Carbon::now();
            $añoAnterior = $now->copy()->year - 1;
            $haceUnMes = $now->copy()->subDays(30)->setTimeFromTimeString("00:00");
            [$inicio, $fin] = $this->getRangoEstacion($now->month, $añoAnterior);
            $producto = $producto->addSelect([
                "mas_vendidos" => DB::table('productos_ventas')
                                    ->selectRaw('COALESCE(COUNT(DISTINCT productos_ventas.venta), 0) + 0')
                                    ->join('ventas', 'productos_ventas.venta', '=', 'ventas.id')
                                    ->whereColumn('productos_ventas.producto', 'productos.id')
                                    ->where(function($q) use ($inicio, $fin, $haceUnMes, $now) {
                                        $q->whereBetween('ventas.fecha_venta', [$inicio, $fin])
                                            ->orWhereBetween('ventas.fecha_venta', [$haceUnMes, $now]);
                                    })
                                    ->where("ventas.venta_invalida","=","0")
            ]);
            $producto = $producto->orderBy("mas_vendidos",$orden['direccion'])
                ->orderBy('precio_venta', 'asc');
        } else {
            $producto = $producto->orderBy($orden['orden'], $orden['direccion']);
        }
        $producto = $producto->paginate(24);
        return $producto;
    }

    public function newProducto($producto){
        return ($this->create($producto))->id;
    }

    public function editProducto($producto,$idProd){
        return $this->where("id","=",$idProd)->update($producto);
    }

    public function getProducto($idProducto){
        //DB::enableQueryLog();
        $sub = DB::table('productos_compras')
                   ->selectRaw('MAX(productos_compras.compra) as ultima_compra')
                   ->join('compras', 'compras.id', '=', 'productos_compras.compra')
                   ->where('productos_compras.producto', $idProducto)
                   ->where('compras.compra_deshecha','=','0')
                   ->groupBy('compras.proveedor');
        
        $producto = $this::with([
            'fotos' => function($q) {
                $q->select('url_img','url_img_online', 'producto');
            },
            'compras' => function($q) use ($sub) {
                $q->select('compras.id', 'compras.proveedor', 'compras.fecha_compra')
                ->whereIn('compras.id', $sub)
                ->with(['productosCompra' => function($q) {
                    $q->select(
                        'productos_compras.compra',
                        'productos_compras.producto',
                        'productos_compras.precio_compra'
                    )
                    ->withTrashed();
                }])
                ->where("compras.compra_deshecha","=",0)
                ->withTrashed()
                ->orderBy('compras.fecha_compra', 'desc');
            },
            'proveedores' => function($q) {
                $q->select('proveedores.id', 'proveedores.nombre')
                ->withTrashed()
                ->orderBy('proveedores.nombre', 'asc');
            }
        ])
        ->select(
            'productos.id',
            'productos.nombre',
            'productos.descripcion',
            'productos.codigo',
            'productos.cantidad_disponible',
            "cantidad_minima",
            'productos.tipo_medida',
            'productos.precio_venta',
            'productos.categoria'
        )
        ->where('productos.id', $idProducto)
        ->first();
        //dd(DB::getQueryLog());
        if(!isset($producto)){
            return null;
        }else{
            $producto->compras = $producto->compras->sortByDesc('fecha_compra')->values();
            $producto->proveedores = $producto->proveedores->sortBy('nombre')->values();
            return $producto;
        }
    }

    public function getProductoCompra($idProd){
        return $this->select("productos.id","productos.nombre","cantidad_disponible","cantidad_minima","productos.tipo_medida")
        ->with(["foto" => function($q){$q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");}])
        ->where("productos.id","=",$idProd)
        ->first();
    }

    public function proveedores() {
        return $this->belongsToMany(Proveedor::class, "proveedores_productos", "producto", "proveedor");
    }
    public function compras() {
        return $this->belongsToMany(Compra::class, "productos_compras", "producto", "compra");
    }
    public function ventas() {
        return $this->belongsToMany(Venta::class, "productos_ventas", "producto", "venta");
    }

    public function deleteProducto($idProducto){
        $prod = $this->find($idProducto);
        if($prod){
            return $prod->delete();
        }
        return false;
    }

    public function foto() {
        return $this->hasOne(Foto::class, "producto")->oldest();
    }

    public function fotos() {
        return $this->hasMany(Foto::class, "producto");
    }

    public function proveedoresProducto(){
        return $this->hasMany(ProveedorProducto::class, 'producto');
    }

    public function productoCompra(){
        return $this->hasMany(ProductosCompra::class, 'producto');
    }
    
    public function productoVenta(){
        return $this->hasMany(ProductosVenta::class, 'producto');
    }
    public function productoDevolucionVenta(){
        return $this->hasMany(ProductosDevolucionVenta::class, 'producto');
    }

    protected static function booted(){
        static::deleting(function ($producto) {
            $producto->proveedoresProducto()->delete();
            $producto->fotos()->delete();
        });
    }

    private function getRangoEstacion($mesActual, $año)
    {
        if ($mesActual >= 9 && $mesActual <= 11) { // Primavera
            return [Carbon::create($año, 9, 1), Carbon::create($año, 11, 30)];
        } elseif ($mesActual >= 12 || $mesActual <= 2) { // Verano
            return [Carbon::create($año, 12, 1), Carbon::create($año + 1, 2, 28)];
        } elseif ($mesActual >= 3 && $mesActual <= 5) { // Otoño
            return [Carbon::create($año, 3, 1), Carbon::create($año, 5, 31)];
        } else { // Invierno
            return [Carbon::create($año, 6, 1), Carbon::create($año, 8, 31)];
        }
    }
}
