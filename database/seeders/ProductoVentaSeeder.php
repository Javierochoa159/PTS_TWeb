<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\ProductosVenta;
use App\Models\Venta;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Randomizer;

class ProductoVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rand=new Randomizer();
        $ventas=Venta::select("id")->get();
        foreach($ventas as $venta){
            $prods=Producto::whereNotExists(function ($q) use ($venta){
                                $q->select(DB::raw(1))
                                ->from('productos_ventas')
                                ->where('productos_ventas.venta',"=", $venta->id)
                                ->whereColumn('productos_ventas.producto', "productos.id");
                            })
                            ->where("cantidad_disponible",">=",15)
                            ->select(
                                "id",
                                "tipo_medida",
                                "cantidad_disponible",
                                "precio_venta"
                                )
                            ->get();
            $newTotalVenta=BigDecimal::of("0.0000");
            $cant=rand(1,10);
            $prodsVenta=[];
            $i=0;
            while($i<$cant){
                $idProd=$prods[rand(0,sizeof($prods)-1)];
                if(!in_array($idProd,$prodsVenta)){
                    $prodsVenta[]=$idProd;
                    $i++;
                }
            }
            $cant=0;
            foreach($prodsVenta as $prod){
                switch($prod->tipo_medida){
                    case "Unidad":
                        $cantidad=BigDecimal::of($rand->getInt(1,10))->toScale(0, RoundingMode::DOWN);
                        break;
                    default:
                        $cantidad=BigDecimal::of($rand->getFloat(0.5,10))->toScale(4, RoundingMode::DOWN);
                }
                $precioVenta=BigDecimal::of($prod->precio_venta)->toScale(2, RoundingMode::HALF_UP);
                $totalProducto=BigDecimal::of($cantidad->multipliedBy($precioVenta)->toScale(4, RoundingMode::HALF_UP));
                ProductosVenta::create([
                    "producto"=>$prod->id,
                    "venta"=>$venta->id,
                    "cantidad"=>$cantidad,
                    "precio_venta"=>$precioVenta,
                    "total_producto"=>$totalProducto
                ]);
                $newCant=(BigDecimal::of($prod->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP))->minus($cantidad)->toScale(4,RoundingMode::HALF_UP);
                Producto::where("id","=",$prod->id)->update(["cantidad_disponible"=>$newCant->__tostring()]);
                $newTotalVenta=$newTotalVenta->plus($totalProducto)->toScale(4,RoundingMode::HALF_UP);
                $cant++;
            }
            if($cant==0){
                Venta::where("id","=",$venta->id)->forceDelete();
            }else{
                Venta::where("id","=",$venta->id)->update([
                    "monto_subtotal"=>$newTotalVenta->__tostring(),
                    "monto_total"=>$newTotalVenta->__tostring()
                ]);
            }
        }
    }
}
