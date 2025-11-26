<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProveedorComponent extends Component
{
    public $proveedor;
    public function __construct($proveedor)
    {
        $this->proveedor=$proveedor;
    }

    public function render(): View|Closure|string
    {
        return view('components.proveedor-component',["proveedor"=>$this->proveedor]);
    }
}
