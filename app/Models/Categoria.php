<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "categorias";
    protected $primaryKey = 'id';
    protected $fillable = [
        'titulo',
        'padre'
    ];

    public function getAllCategorias(){
        return $this::all()->toArray();
    }
    public function newCategoria($categoria){
        return ($this->create($categoria))->id;
    }
    public function editCategoria($categoria,$idCatego){
        return $this->where("id","=",$idCatego)->update($categoria);
    }
    public function deleteCategoria($idCatego){
        $catego = $this->find($idCatego);
        if($catego){
            return $catego->delete();
        }
        return false;
    }

    public function allChildrenIds()
    {
        $ids = collect([$this->id]);

        foreach ($this->hijos as $hijo) {
            $ids = $ids->merge($hijo->allChildrenIds());
        }

        return $ids;
    }

    public function hijos() {
        return $this->hasMany(Categoria::class, 'padre');
    }

    protected static function booted()
    {
        static::deleting(function ($categoria) {
            foreach ($categoria->hijos as $hijo) {
                $hijo->delete(); 
            }
        });
    }

}
