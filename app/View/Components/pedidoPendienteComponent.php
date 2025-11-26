<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class pedidoPendienteComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $venta;
    public function __construct($venta)
    {
        $this->venta=$venta;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data=[
            "venta"=>$this->venta
        ];
        return view('components.pedido-pendiente-component',$data);
    }
}
