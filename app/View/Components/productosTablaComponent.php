<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class productosTablaComponent extends Component
{
    public $producto;
    public $tipo;
    public function __construct($producto,$tipo)
    {
        $this->producto=$producto;
        $this->tipo=$tipo;
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
        return view('components.productos-tabla-component',$data);
    }
}
