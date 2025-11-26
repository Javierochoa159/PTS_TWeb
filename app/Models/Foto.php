<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Foto extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "fotos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'url_img',
        'url_img_online',
        'producto'
    ];
    public $timestamps = true;

    public function getAllFotosProducto($idProd){
        return $this->where("producto","=",$idProd)->select("id","url_img","url_img_online")->get();
    }
    public function newFotoProducto($foto){
        return $this->create($foto);
    }
    public function deleteFoto($idFoto){
        return $this->where("id","=",$idFoto)->delete();
    }
}
