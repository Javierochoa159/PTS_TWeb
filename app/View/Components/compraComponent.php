<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class compraComponent extends Component
{
    public $compra;
    public function __construct($compra)
    {
        $this->compra=$compra;
    }

    public function render(): View|Closure|string
    {
        $data=[
            "compra"=>$this->compra
        ];
        return view('components.compra-component',$data);
    }
}
