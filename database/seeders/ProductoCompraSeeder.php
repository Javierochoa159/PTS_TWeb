<?php

namespace Database\Seeders;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\ProductosCompra;
use App\Models\Proveedor;
use App\Models\ProveedorProducto;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Seeder;
use Random\Randomizer;

class ProductoCompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rand=new Randomizer();
        $compras=Compra::select("id","proveedor")->get();
        foreach($compras as $compra){
            $idProds=ProveedorProducto::select('producto')->where("proveedor","=",$compra->proveedor)->get();
            $prods=Producto::whereIn("id",$idProds)->select("id","tipo_medida","cantidad_disponible")->get();
            $newTotalCompra=BigDecimal::of("0.0000");
            $cant=0;
            foreach($prods as $prod){
                if(rand(0,1)){
                    switch($prod->tipo_medida){
                        case "Unidad":
                            $cantidad=BigDecimal::of($rand->getInt(1,50))->toScale(0, RoundingMode::DOWN);
                            break;
                        default:
                            $cantidad=BigDecimal::of($rand->getFloat(0.5,50))->toScale(2, RoundingMode::DOWN);
                    }
                    $precioCompra=BigDecimal::of($rand->getFloat(100,50000))->toScale(2, RoundingMode::HALF_UP);
                    $totalProducto=BigDecimal::of($cantidad->multipliedBy($precioCompra)->toScale(4, RoundingMode::HALF_UP));
                    ProductosCompra::create([
                        "producto"=>$prod->id,
                        "compra"=>$compra->id,
                        "cantidad"=>$cantidad,
                        "precio_compra"=>$precioCompra,
                        "total_producto"=>$totalProducto
                    ]);
                    $newCant=(BigDecimal::of($prod->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP))->plus($cantidad)->toScale(4,RoundingMode::HALF_UP);
                    Producto::where("id","=",$prod->id)->update(["cantidad_disponible"=>$newCant->__tostring()]);
                    $newTotalCompra=$newTotalCompra->plus($totalProducto)->toScale(4,RoundingMode::HALF_UP);
                    $cant++;
                }
            }
            if($cant==0){
                Compra::where("id","=",$compra->id)->forceDelete();
            }else{
                Compra::where("id","=",$compra->id)->update(["monto_total"=>$newTotalCompra->__tostring()]);
            }
        }
    }
}
