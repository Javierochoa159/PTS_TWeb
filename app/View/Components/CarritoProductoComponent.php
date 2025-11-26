<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CarritoProductoComponent extends Component
{
    public $producto;
    public function __construct($producto)
    {
        $this->producto=$producto;
    }

    public function render(): View|Closure|string
    {
        return view('components.carrito-producto-component',["producto",$this->producto]);
    }
}
