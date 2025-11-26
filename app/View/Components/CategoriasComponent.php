<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategoriasComponent extends Component
{
    public $categorias;
    public $text;
    public $type;

    public function __construct($categorias,$text,$type)
    {
        $this->categorias=$this->ordenarCategorias($categorias);
        $this->text=$text;
        $this->type=$type;
    }
    public function render(): View|Closure|string
    {
        $data["categorias"]=$this->categorias;
        $data["text"]=$this->text;
        $data["type"]=$this->type;
        return view('components.categorias-component',$data);
    }

    private function ordenarCategorias($categorias, $parentId = null) {
        $branch = [];

        foreach ($categorias as $categoria) {
            if ($categoria["padre"] === $parentId) {
                $children = $this->ordenarCategorias($categorias, $categoria["id"]);
                if ($children) {
                    $categoria["hijos"]=$children;
                } else {
                    $categoria["hijos"]=[];
                }
                $branch[] = $categoria;
            }
        }

        return $branch;
    }
}
