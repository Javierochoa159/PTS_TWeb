<?php

namespace App\View\Components;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class productoVentaTablaComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $producto;
    public $tipo;
    public $devoluciones;
    public $idsDev;
    public function __construct($producto,$tipo,$devoluciones=null,$idsDev=null)
    {
        $this->producto=$producto;
        $this->tipo=$tipo;
        $this->devoluciones=$devoluciones;
        $this->idsDev=$idsDev;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data=[
            "producto"=>$this->producto,
            "tipo"=>$this->tipo
        ];
        if(isset($this->devoluciones) && isset($this->idsDev)){
            $data["devoluciones"]=$this->devoluciones;
            $devoluciones=$this->devoluciones;
            $producto=$this->producto;
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
                $producto->cantidad=$totalCantProd->minus($totalCantProdDev)->toScale(4,RoundingMode::HALF_UP)->__tostring();
                
                $producto->total_producto=$totalPrecioProd->minus($totalPrecioProdDev)->toScale(4,RoundingMode::HALF_UP)->__tostring();
                $data["producto"]=$producto;
                return view('components.producto-venta-tabla-component',$data);
            }else{
                return "";
            }
        }else{
            return view('components.producto-venta-tabla-component',$data);
        }
    }
}
