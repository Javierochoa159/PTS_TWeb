<?php

namespace App\View\Components;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class productoDevolucionTablaComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $producto;
    public $devoluciones;
    public $idsDev;

    public function __construct($producto,$devoluciones,$idsDev)
    {
        $this->producto=$producto;
        $this->devoluciones=$devoluciones;
        $this->idsDev=$idsDev;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $producto=$this->producto;
        $devoluciones=$this->devoluciones;
        $idsDev=$this->idsDev;
        $indexDevs=array_keys(array_filter($idsDev["prodDevId"],function($prod) use($producto){
            return $prod==$producto->producto;
        }));
        $totalCantProd=BigDecimal::of($producto->cantidad)->toScale(4,RoundingMode::HALF_UP);
        $totalPrecioProd=BigDecimal::of($producto->total_producto)->toScale(4,RoundingMode::HALF_UP);
        $totalCantProdDev=BigDecimal::of("0.0000");
        $totalPrecioProdDev=BigDecimal::of("0.0000");
        foreach($indexDevs as $indexDev){
            $prod=$devoluciones[$idsDev["devIndex"][$indexDev]]->productos[$idsDev["prodDevIndex"][$indexDev]];
            $cantProdDev=BigDecimal::of($prod->cantidad)->toScale(4,RoundingMode::HALF_UP);
            $totalProdDev=BigDecimal::of($prod->total_producto)->toScale(4,RoundingMode::HALF_UP);
            $totalCantProdDev=$totalCantProdDev->plus($cantProdDev)->toScale(4,RoundingMode::HALF_UP);
            $totalPrecioProdDev=$totalPrecioProdDev->plus($totalProdDev)->toScale(4,RoundingMode::HALF_UP);
        }

        if($totalCantProd->isGreaterThan($totalCantProdDev)){
            $producto->cantidad=$totalCantProd->minus($totalCantProdDev)->toScale(4,RoundingMode::HALF_UP);
            $producto->total_producto=$totalPrecioProd->minus($totalPrecioProdDev)->toScale(4,RoundingMode::HALF_UP);
            $data=[
                "producto"=>$producto,
                "devoluciones"=>$devoluciones,
                "idsDev"=>$idsDev
            ];
            return view('components.producto-devolucion-tabla-component',$data);
        }else{
            return "";
        }
    }
}
