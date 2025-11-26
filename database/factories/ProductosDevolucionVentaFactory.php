<?php

namespace Database\Factories;

use App\Models\DevolucionVenta;
use App\Models\Producto;
use App\Models\ProductosVenta;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductosDevolucionVenta>
 */
class ProductosDevolucionVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $devolucionId = null;
    protected $prodventId = null;

    public function withDevolucion($devolucionId): static
    {
        $this->devolucionId = $devolucionId;
        return $this;
    }

    public function withProdvent($prodventId): static
    {
        $this->prodventId = $prodventId;
        return $this;
    }
    public function definition(): array
    {
        if(!isset($this->devolucionId) || !isset($this->prodventId)){
            $devolucion=DevolucionVenta::where("monto_total","=","0")->inRandomOrder()->first();
            $prodVent=ProductosVenta::where("venta","=",$devolucion->venta)->first();
        }else{
            $devolucion=DevolucionVenta::where("id","=",$this->devolucionId)->first();
            $prodVent=ProductosVenta::where("id","=",$this->prodventId)->first();
        }
        $prod=Producto::where("id","=",$prodVent->producto)->select("tipo_medida")->first();

        $idDev=$devolucion->id;
        $idProd=$prodVent->producto;

        $medida=$prod->tipo_medida;
        $cantV=BigDecimal::of($prodVent->cantidad);
        $precioV=BigDecimal::of($prodVent->precio_venta);
        $oldTotalV=BigDecimal::of($prodVent->total_producto);

        switch($medida){
            case "Unidad":  $cantDev=BigDecimal::of($this->faker->numberBetween(1,$cantV->__toString()));
            break;

            default:        $cantDev=BigDecimal::of($this->faker->randomFloat(4,0.01,$cantV->__toString()))->toScale(4,RoundingMode::DOWN);
        }

        if($cantDev->isEqualTo($cantV)){
            $totalDev=$oldTotalV;
        }else{
            $totalDev=$cantDev->multipliedBy($precioV)->toScale(4,RoundingMode::HALF_UP);
        }
        $total=BigDecimal::of($devolucion->monto_total);
        $total=$total->plus($totalDev)->toScale(4,RoundingMode::HALF_UP);
        DevolucionVenta::where("id","=",$devolucion->id)->update(["monto_total"=>$total->__toString()]);
        return [
            "producto"=>$idProd,
            "devolucion"=>$idDev,
            "tipo_devolucion"=>$this->faker->randomElement(["Cambio","Fallado","Devolucion"]),
            "motivo_devolucion"=>$this->faker->text(75),
            "cantidad"=>$cantDev->__toString(),
            "total_producto"=>$totalDev->__toString()
        ];
    }
}
