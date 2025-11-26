<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class alertStockComponent extends Component
{
    public $cantidad;
    public $minimo;
    public function __construct($cantidad,$minimo)
    {
        $this->cantidad=$cantidad;
        $this->minimo=$minimo;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data=[
            "cantidad"=>$this->cantidad,
            "minimo"=>$this->minimo,
        ];
        return view('components.alert-stock-component',$data);
    }
}
