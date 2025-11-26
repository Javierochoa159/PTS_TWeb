<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoriasComponentRecursivo extends Component
{
    public $categorias;
    public $type;

    public function __construct($categorias,$type)
    {
        $this->categorias=$categorias;
        $this->type=$type;
    }
    public function render(): View|Closure|string
    {
        $data["categorias"]=$this->categorias;
        $data["type"]=$this->type;
        return view('components.categorias-component-recursivo',$data);
    }
}
