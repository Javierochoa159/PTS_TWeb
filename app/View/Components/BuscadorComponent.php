<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BuscadorComponent extends Component
{
    public $type;
    public function __construct($type)
    {
        $this->type=$type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.buscador-component',["type"=>$this->type]);
    }
}
