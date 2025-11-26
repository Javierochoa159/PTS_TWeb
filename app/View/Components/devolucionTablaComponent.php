<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class devolucionTablaComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $producto;
    public function __construct($producto)
    {
        $this->producto=$producto;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data=[
            "prodcuto"=>$this->producto
        ];
        return view('components.devolucion-tabla-component',$data);
    }
}
