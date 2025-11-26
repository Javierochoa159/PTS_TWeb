<?php

namespace App\View\Components;

use App\Models\Venta;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PortadaComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $ventaDB=new Venta();
        $pendientes = $ventaDB->getAllPedidosPendientes();
        $retiros = $ventaDB->getAllRetirosPendientes();
        $data=[
            "pendientes"=>$pendientes["pendientes"],
            "totalPendientes"=>$pendientes["totalPendientes"],
            "retiros"=>$retiros["retiros"],
            "totalRetiros"=>$retiros["totalRetiros"],
        ];
        return view('components.portada-component',$data);
    }
}
