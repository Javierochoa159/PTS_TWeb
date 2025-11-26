@extends('layouts.index')

@php
    use Brick\Math\BigDecimal;
    use Brick\Math\RoundingMode;
    $fmt4 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt4->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
    $fmt2 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt2->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
    $fmt0 = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt0->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
@endphp

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/producto.css"
    ])
@endsection

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>

    <div class="col-12 d-flex flex-column align-items-center justify-content-start mb-5 pb-5">
        <div class="primerDivProd col-11 p-3">
            <div class="producto p-2">
                <form id="addToCard" action="{{ route("producto.addtocart") }}" method="post" name="formAddToCard" novalidate>
                    @csrf
                    <input type="hidden" name="id" value="{{ $producto->id }}">
                    <div class="productoTop d-flex">
                        <div id="fotosProd" class="carousel slide col-3 d-flex align-items-center justify-content-center" data-bs-ride="carousel">
                            <div class="carousel-inner @if($producto->cantidad_disponible<=$producto->cantidad_minima) position-relative @endif">
                                @php $i=0; @endphp
                                @foreach ($producto->fotos as $fotoUrls)
                                    <div class="align-items-center justify-content-center carousel-item {{ $i==0 ? "active" : "" }} h-100">
                                        <img src="@php
                                            $foto=$fotoUrls->url_img;
                                            if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                                echo route("inicio.index").$foto;
                                            }else{
                                                echo $fotoUrls->url_img_online;
                                            }
                                        @endphp" class="d-block w-auto img-fluid" alt="fotoProd" draggable="false">
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                                <x-alert-stock-component :cantidad="$producto->cantidad_disponible" :minimo="$producto->cantidad_minima"/>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#fotosProd" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#fotosProd" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                        <div class="detallesProd col-9">
                            <div class="nombreProd mb-1 d-flex align-items-top justify-content-between">
                                <h5 class="p-0 m-0 pt-1">{{ $producto->nombre }}</h5>
                                @if(session()->has("adminSet"))
                                    <div class="btnsProd d-flex flex-column justify-content-top">
                                        <button type="button" id="btn-editProducto" class="editProducto me-2 mt-2" data-bs-toggle="modal" data-bs-target="#editProducto">
                                            <div class="d-flex align-items-center">
                                                <img class="img-fluid" src="{{ asset("build/assets/icons/edit.svg") }}" title="Editar" alt="editar producto" draggable="false">
                                            </div>
                                        </button>
                                        <button type="button" id="btn-deleteProducto" class="deleteProducto me-2 mt-2" data-bs-toggle="modal" data-bs-target="#deleteProducto">
                                            <div class="d-flex align-items-center">
                                                <img class="img-fluid" src="{{ asset("build/assets/icons/delete.svg") }}" title="Eliminar" alt="eliminar producto" draggable="false">
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="totalPreciosProd d-flex flex-column justify-content-evenly">
                                <div class="d-flex totalCodigoProd">
                                    <h6 class="p-0 m-0 pt-2 col-6">Total Disponible: @php
                                        $cantDisp=BigDecimal::of(data_get($producto,"cantidad_disponible"))->toScale(4, RoundingMode::HALF_UP);
                                        $aux=$cantDisp->toScale(0, RoundingMode::DOWN);
                                        if($aux->isLessThan($cantDisp)){
                                            echo $fmt4->format($cantDisp->__toString())." ".$producto->tipo_medida;
                                        }else{
                                            echo $fmt0->format($aux->__toString())." ".$producto->tipo_medida;
                                        }
                                    @endphp</h6>
                                    <h6 class="p-0 m-0 pt-2 col-6">Codigo: 
                                        @php
                                            if($producto->codigo==null){
                                                echo "------------";
                                            }
                                            else{
                                                echo $producto->codigo;
                                            }
                                        @endphp
                                    </h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <h6 class="p-0 m-0">Ultima Compra: @php
                                        if(isset($producto->compras) && isset($producto->compras[0])){
                                            $prodsCompKeys=$producto->compras[0]->productosCompra->pluck("producto")->toArray();
                                            $key=array_search($producto->id,$prodsCompKeys);
                                            if($key!==false){
                                                echo "$".$fmt2->format($producto->compras[0]->productosCompra[$key]->precio_compra);
                                            }else{
                                                echo "$0,00";
                                            }
                                        }else{
                                            echo "$0,00";
                                        }
                                    @endphp @switch($producto->tipo_medida) @case("Unidad") c/u @break @case("Kilogramo") por Kilo @break @case("Litro") por Litro @break @case("Metro") por Metro @break @endswitch</h6>
                                    @if (sizeof($producto->proveedores)>0)
                                        <button type="button" id="btn-newCompra" class="btn newCompra d-flex align-items-center ms-2 p-1" data-bs-toggle="modal" data-bs-target="#newCompra">
                                            Comprar
                                        </button>
                                    @endif
                                </div>
                                <div class="precioVentaProd d-flex align-items-center justify-content-start">
                                    <h6 class="p-0 m-0">Precio de Venta: {{ "$".$fmt2->format(BigDecimal::of(data_get($producto,"precio_venta"))->toScale(4, RoundingMode::HALF_UP)->__toString()) }}@switch($producto->tipo_medida) @case("Unidad") c/u @break @case("Kilogramo") por Kilo @break @case("Litro") por Litro @break @case("Metro") por Metro @break @endswitch</h6>
                                    @if ($producto->cantidad_disponible>0)
                                        <div class="cantidadesProducto mt-1 d-flex align-items-end justify-content-start">
                                            <div class="medidaProducto col-5 d-flex align-items-center justify-content-start">
                                                <div class="col-6 ms-2 d-flex">
                                                    <input class="col-12 px-2" type="number" name="cantidad" min="@if(strcmp($producto['tipo_medida'],'Unidad')==0)
                                                        1
                                                    @else
                                                        0.01
                                                    @endif" max="{{ $producto->cantidad_disponible }}" name="cantidad_producto-{{ $producto->id }}" step="1" value="1">
                                                </div>
                                                <div class="col-auto d-flex">
                                                    @if (strcmp(data_get($producto,"tipo_medida"),"Unidad")!=0)
                                                        <select name="medida">
                                                            <option value="precio" @if(!in_array(data_get($producto,"tipo_medida"),["Kilogramo","Metro","Litro"]))selected 
                                                            @endif>$</option>
                                                            <option value="{{data_get($producto,"tipo_medida")}}" @if(in_array(data_get($producto,"tipo_medida"),["Kilogramo","Metro","Litro"]))selected @endif>
                                                                @switch(data_get($producto,"tipo_medida")) @case("Kilogramo")Kg @break @case("Metro")Mt @break @case("Litro")Lt @break @endswitch
                                                            </option>
                                                        </select>
                                                    @else
                                                        <input type="hidden" name="medida" value="Unidad">
                                                        <p class="p-0 m-0">U</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="producto-btnCarrito">
                                                <button type="submit" class="btn d-flex align-items-center me-2 p-1">
                                                    <img class="img-fluid" src="{{ asset("build/assets/icons/cart.svg") }}" alt="cart" draggable="false">
                                                    <input type="submit" form="addToCard" value="Agregar">
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="productoBottom pb-2">
                        <pre class="p-1 m-0">{{ $producto->descripcion }}</pre>
                    </div>
                </form>
            </div>
        </div> 
    </div>
    @if (session()->has("adminSet"))
        <div class="modal fade" id="editProducto" aria-hidden="true" aria-labelledby="editProductoLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editProductoLabel">Editar Producto</h1>
                        <div class="btn-close btn-closeEditProducto p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-editProducto" novalidate method="post" action="{{ route("producto.editprod") }}" id="formEditProd" autocomplete="off" >
                            @csrf
                            <input type="number" value="{{ $producto->id }}" name="idProd-editProd" required hidden>
                            <div class="d-flex flex-column align-items-center">
                                <div class="col-md-11 mb-3">
                                    <label class="ps-1 form-label" for="nombre-editProd">Nombre del Producto:</label>
                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-editProd",$errors->keys())) invalid @endif @endif" id="nombre-editProd" name="nombre-editProd" placeholder="Nombre del Producto" value="{{ old("nombre-editProd") ? old("nombre-editProd") : $producto->nombre }}">
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-editProd">
                                        @error('nombre-editProd')
                                            {{ str_replace("nombre-edit prod","nombre",$message) }}
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-11 mb-3">
                                    <label class="ps-1 form-label" for="descripcion-editProd">Descripción del Producto:</label>
                                    <textarea style="height: 120px; max-height:120px" class="form-control @if ($errors->any()) @if (in_array("descripcion-editProd",$errors->keys())) invalid @endif @endif" id="descripcion-editProd" name="descripcion-editProd" placeholder="Descripcion del Producto" required>{{ old("descripcion-editProd") ? old("descripcion-editProd") : $producto->descripcion }}</textarea>
                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("descripcion-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-descripcion-editProd">
                                        @error('descripcion-editProd')
                                            {{ str_replace("descripcion-edit prod","descripcion",$message) }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2 d-flex justify-content-center">
                                <div class="col-6">
                                    <div class="d-flex">
                                        <div class="col-md-6">
                                            <div class="col-11 mb-2">
                                                <label class="ps-1 form-label">Proveedor del Producto:</label>
                                                <button id="btn-proveedores-editProd" class="col-10 btn d-flex align-items-center justify-content-between form-control @if ($errors->any()) @if (in_array("proveedor-editProd",$errors->keys())) invalid @endif @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-proveedores-editProd" aria-expanded="false">
                                                    Proveedores
                                                    <img class="ms-2 img-fluid" src="{{ asset("build/assets/icons/down.svg") }}" alt="down" draggable="false">
                                                </button>
                                                <div class="collapse" id="collapse-proveedores-editProd">
                                                    <div class="card card-body col-12 p-2">
                                                        @if (isset($proveedores))
                                                        @foreach ($proveedores as $proveedor)
                                                            <div title="{{ $proveedor->nombre }}" class="d-flex align-items-center justify-content-start ps-2 col-12">
                                                                <input class="me-2" type="checkbox" id="proveedor-editProd_{{ $proveedor->id }}" name="proveedor-editProd_{{ $proveedor->id }}" value="{{ $proveedor->id }}" @if (old("proveedor-editProd_".$proveedor->id)) checked @else @php $proveedoresChecked = $producto->proveedores->pluck("id")->toArray(); if(is_array($proveedoresChecked))if(in_array($proveedor->id,$proveedoresChecked)) echo"checked"; @endphp @endif>
                                                                <label class="p-1" for="proveedor-editProd_{{ $proveedor->id }}">{{ strlen($proveedor->nombre)>30 ? mb_substr($proveedor->nombre,0,30)."..." : $proveedor->nombre}}</label>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback @error('proveedor-editProd') is-invalid @enderror" id="invalid-proveedor-editProd">
                                                    @error('proveedor-editProd')
                                                        {{ str_replace("proveedor-edit prod","proveedor",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-11 mb-2">
                                                <div class="col-12">
                                                    <label class="ps-1 form-label" for="codigo-editProd">Codigo del Producto:</label>
                                                    <input type="text" class="form-control @if ($errors->any()) @if (in_array("codigo-editProd",$errors->keys())) invalid @endif @endif" id="codigo-editProd" name="codigo-editProd" placeholder="Ej: 87HweF2 (Opcional)" value="{{ old("codigo-editProd") ? old("codigo-editProd") : $producto->codigo }}">
                                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("codigo-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-codigo-editProd">
                                                        @error('codigo-editProd')
                                                            {{ str_replace("codigo-edit prod","codigo",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex flex-column align-items-top precios">
                                            <div class="col-md-12 mb-2">
                                                <div class="col-11">
                                                    <label class="ps-1 form-label" for="precioVenta-editProd">Precio de Venta:</label>
                                                    <div class="d-flex align-items-center">
                                                        <img class="img-fluid" src="{{ asset("build/assets/icons/money.svg") }}" alt="$" draggable="false">
                                                        <input type="number" step="0.01" maxlength="9" min="0.01" class="form-control @if ($errors->any()) @if (in_array("precioVenta-editProd",$errors->keys())) invalid @endif @endif" id="precioVenta-editProd" name="precioVenta-editProd" placeholder="0,00" value="@php
                                                            if(old("precioVenta-editProd")!=null){
                                                                echo old("precioVenta-editProd");
                                                            }else{
                                                                echo BigDecimal::of($producto->precio_venta)->toScale(2, RoundingMode::HALF_UP)->__toString();
                                                            }
                                                        @endphp">
                                                    </div>
                                                    <div class="invalid-feedback @if ($errors->any()) @if (in_array("precioVenta-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-precioVenta-editProd">
                                                        @error('precioVenta-editProd')
                                                            {{ str_replace("precio venta-edit prod","precio de venta",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mt-2">
                                                    <div class="col-md-11">
                                                        <label for="cantMinima-editProd" class="ps-1 form-label">Alertar cant. mínima:</label>
                                                        <input type="number" step="1" max="50" min="5" title="Alertar stock menor o igual al indicado." class="form-control @if ($errors->any()) @if (in_array("cantMinima-editProd",$errors->keys())) invalid @endif @endif" name="cantMinima-editProd" id="cantMinima-editProd" placeholder="1" value="{{ old("cantMinima-editProd") ? old("cantMinima-editProd") : $producto->cantidad_minima }}">
                                                        <div class="invalid-feedback @if ($errors->any()) @if (in_array("cantMinima-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-cantMinima-editProd">
                                                            @error('cantMinima-editProd')
                                                                {{ str_replace("cant minima-edit prod","candidad minima",$message) }}
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 d-flex justify-content-between">
                                        <div class="col-8 categorias-editProd">
                                            <label class="ps-1 form-label" for="categoria-editProd">Seleccione la categoria del Producto:</label>
                                            <div class="categorias py-2 d-flex flex-column align-items-start justify-content-start form-control">
                                                <x-categorias-component :categorias="$categorias" text="" type="editProd"/>
                                            </div>
                                            <input type="number" name="catego-id-editProd" value="{{ old("catego-id-editProd") ? old('catego-id-editProd') : $producto->categoria }}" hidden required>
                                            <input type="text" class="input-categoProd mt-2 form-control @if ($errors->any()) @if (in_array("catego-id-editProd",$errors->keys())) invalid @endif @endif" name="categoria-editProd" id="categoria-editProd" disabled required>
                                            <div class="invalid-feedback @if ($errors->any()) @if (in_array("catego-id-editProd",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-categoria-editProd">
                                                @error('catego-id-editProd')
                                                    {{ str_replace("catego-id-edit prod","categoria",$message) }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="div-fotos-editProd" class="fotos-editProd col-5 d-flex flex-column justify-content-center align-items-center">
                                    <div class="col-11 drop-area d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-editProd",$errors->keys())) invalid @endif @endif">
                                        <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                        <span>O</span>
                                        <button class="px-2 py-1 mt-2">Buscar imágenes</button>
                                        <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                        <select class="form-control" name="fotos-editProd[]" id="fotos-editProd" multiple hidden required>
                                            @php $oldFotos=["fotos"=>old("fotos-editProd"),"data-foto"=>[]];
                                                if(!empty($oldFotos["fotos"])){
                                                    if(is_array($oldFotos["fotos"])){
                                                        foreach($oldFotos["fotos"] as $foto){
                                                            $idOldFoto=bin2hex(random_bytes(length: 3));
                                                            $oldFotos["data-foto"][]=$idOldFoto;
                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                        }
                                                    }else{
                                                        $idOldFoto=random_bytes(length: 7);
                                                        $oldFotos["data-foto"][]=$idOldFoto;
                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                    }
                                                }else{
                                                    $fotosLocal=$producto->fotos->pluck("url_img")->toArray();
                                                    $fotosOnline=$producto->fotos->pluck("url_img_online")->toArray();
                                                    if(sizeof($fotosLocal)!=sizeof($fotosOnline)){
                                                        $oldFotos["fotos"]=[];
                                                        foreach ($oldFotos["fotos"] as $foto) {
                                                            if(isset($foto)){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotos["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                $oldFotos["fotos"][]=$foto;
                                                            }
                                                        }
                                                    }else{
                                                        $oldFotos["fotos"]=[];
                                                        for($i=0;$i<sizeof($fotosLocal);$i++) {
                                                            if(isset($fotosLocal[$i])){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotos["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$fotosLocal[$i].'" selected></option>';
                                                                $oldFotos["fotos"][]=$fotosLocal[$i];
                                                            }elseif(isset($fotosOnline[$i])){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotos["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$fotosOnline[$i].'" selected></option>';
                                                                $oldFotos["fotos"][]=$fotosOnline[$i];
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                        </select>
                                        <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-editProd') is-invalid @enderror" id="invalid-fotos-editProd">
                                            @error('fotos-editProd')
                                                @if (str_contains($message,"obligatorio"))
                                                    {{ "Ingrese al menos una foto del producto." }}
                                                @else
                                                    {{ str_replace("fotos-edit prod","fotos",$message) }}
                                                @endif
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-11 previewFotos d-flex p-0 ps-2 mt-3 @php
                                        if(!empty($oldFotos["data-foto"]) && sizeof($oldFotos["data-foto"])>3)echo "masTres";
                                    @endphp" id="preview-fotos-editProd">
                                        @for ($i=0;$i<sizeof($oldFotos["data-foto"]);$i++)
                                            <img id="{{ $oldFotos["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotos["fotos"][$i] }}" alt="{{ $oldFotos["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" id="btn-enviar-editProd" form="formEditProd" class="btn ms-3" value="Editar producto">
                        <div class="col-3 d-flex align-items-center justify-content-between mx-3">
                            <input type="reset" id="btn-limpiar-editProd" form="formEditProd" class="btn" value="Reiniciar">
                            <button type="button" class="btn" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteProducto" aria-hidden="true" aria-labelledby="deleteProductoLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteProductoLabel">Eliminar Producto</h1>
                        <div class="btn-close btn-closeDeleteProducto p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-deleteProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate method="post" action="{{ route("producto.deleteprod") }}" id="formDeleteProd" autocomplete="off">
                            @csrf
                            <input type="number" value="{{ $producto->id }}" name="idProd-deleteProd" required hidden>
                            <h2 class="mt-2 mb-3">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Eliminación</span></h2>
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deleteProd",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deleteProd" name="passAdmin-deleteProd" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deleteProd",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deleteProd">
                                @error('passAdmin-deleteProd')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete prod","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-deleteProd" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete prod","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-delete prod","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <input type="submit" name="eliminarProducto" id="btn-enviar-deleteProd" form="formDeleteProd" class="btn ms-3" value="Proceder">
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $productoEditar=null;
        if(session()->has("compra")){
            $idsCompra=Arr::pluck(session("compra")["productos"],"idProd-newCompra");
            if(in_array($producto->id,$idsCompra)){
                $key=array_search($producto->id,$idsCompra);
                $productoEditar=session("compra")["productos"][$key];
            }
        }
        $precioCompraFirtProv=null;
        if(isset($producto->compras)){
            $provsCompraKeys=$producto->compras->pluck("proveedor")->toArray();
            $provKey=array_search($producto->proveedores[0]->id,$provsCompraKeys);
            if($provKey!==false){
                $prodsCompKeys=$producto->compras[$provKey]->productosCompra->pluck("producto")->toArray();
                $prodKey=array_search($producto->id,$prodsCompKeys);
                if($prodKey!==false){
                    $precioCompraFirtProv = BigDecimal::of($producto->compras[$provKey]->productosCompra[$prodKey]->precio_compra)->toScale(2, RoundingMode::HALF_UP);
                }
            }
        }
    @endphp

    <div class="modal fade" id="newCompra" aria-hidden="true" aria-labelledby="newCompraLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newCompraLabel">{{ isset($indexIdProdCompra) ? "Modificar Compra" : "Comprar Producto" }}</h1>
                    <div class="btn-close btn-closeNewCompra p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body">
                    <form class="form-newCompra" novalidate method="post" action="{{ route("compra.newcompra") }}" id="formNewCompra" autocomplete="off" >
                        @csrf
                        <input type="number" value="{{ $producto->id }}" name="idProd-newCompra" required hidden>
                        <div class="pieNewCompra col-12 mb-2 d-flex justify-content-center">
                            <div class="col-12 d-flex flex-column align-content-center mt-3">
                                <div class="col-md-12">
                                    <div class="col-mb-12 precios d-flex flex-column justify-content-center align-items-center">
                                        <div class="col-11 mb-2 d-flex flex-column align-items-center">
                                            <div class="col-11 d-flex align-items-center mb-2">
                                                <h5 class="col-auto p-0 m-0 px-1">Anterior precio de compra:</h5>
                                                <p id="oldPrecioCompra-newCompra" class="col-7 m-0 ms-1 p-2"> @php
                                                    if(isset($indexIdProdCompra)){
                                                        $oldCompra=session("trueEditCompra")["compra"];
                                                        echo "$".$fmt2->format(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"precio_compra"));
                                                    }elseif(isset($precioCompraFirtProv)){
                                                        echo "$".$fmt2->format($precioCompraFirtProv->__toString());
                                                    }else{
                                                        echo "$0,00";
                                                    }
                                                @endphp</p>
                                            </div>
                                            <div class="col-11 d-flex align-itemns-center">
                                                <label class="px-1 col-4" for="precioCompra-newCompra">Nuevo precio de Compra:</label>
                                                <div class="d-flex align-items-center col-7 ms-2">
                                                    <img class="img-fluid" src="{{ asset("build/assets/icons/money.svg") }}" alt="$" draggable="false">
                                                    <input type="number" step="0.01" maxlength="9" min="0.01" class="form-control @if ($errors->any()) @if (in_array("precioCompra-newCompra",$errors->keys())) invalid @endif @endif" id="precioCompra-newCompra" name="precioCompra-newCompra" placeholder="0,00" value="@php
                                                        if(isset($indexIdProdCompra)){
                                                            $oldCompra=session("trueEditCompra")["compra"];
                                                            echo data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"precio_compra");
                                                        }elseif(isset($precioCompraFirtProv)){
                                                            echo $precioCompraFirtProv->toScale(2,RoundingMode::HALF_UP)->__toString();
                                                        }
                                                    @endphp"> 
                                                </div>
                                            </div>
                                            <div class="col-11 d-flex justify-content-end">
                                                <div class="col-8">
                                                    <div class="ps-2 invalid-feedback @if ($errors->any()) @if (in_array("precioCompra-newCompra",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-precioCompra-newCompra">
                                                        @error('precioCompra-newCompra')
                                                            {{ str_replace("precio compra-new compra","precio de compra",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-11 mb-2 d-flex flex-column align-items-center">
                                            <div class="col-11 d-flex align-items-center mb-2">
                                                <h5 class="col-4 p-0 m-0 px-1">Anterior precio de venta:</h5>
                                                <p class="col-7 m-0 ms-2 p-2">{{ "$".$fmt2->format(BigDecimal::of(data_get($producto,"precio_venta"))->toScale(4, RoundingMode::HALF_UP)->__toString()) }}</p>
                                            </div>
                                            <div class="col-11 d-flex align-itemns-center">
                                                <label class="px-1 col-4" for="precioVenta-newCompra">Nuevo precio de Venta:</label>
                                                <div class="d-flex align-items-center col-7 ms-2">
                                                    <img class="img-fluid" src="{{ asset("build/assets/icons/money.svg") }}" alt="$" draggable="false">
                                                    <input type="number" step="0.01" min="0.01" class="form-control @if ($errors->any()) @if (in_array("precioVenta-newCompra",$errors->keys())) invalid @endif @endif" id="precioVenta-newCompra" name="precioVenta-newCompra" placeholder="0,00" value="{{ old("precioVenta-newCompra") ? old("precioVenta-newCompra") : ($productoEditar ? ($productoEditar["precioVenta-newCompra"] ? $productoEditar["precioVenta-newCompra"]->toScale(2,RoundingMode::HALF_UP) : $producto->precio_venta) : $producto->precio_venta) }}">
                                                </div>
                                            </div>
                                            <div class="col-11 d-flex justify-content-end">
                                                <div class="col-8">
                                                    <div class="ps-2 invalid-feedback @if ($errors->any()) @if (in_array("precioVenta-newCompra",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-precioVenta-newCompra">
                                                        @error('precioVenta-newCompra')
                                                            {{ str_replace("precio venta-new compra","precio de venta",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex flex-column align-items-center">
                                    <div class="col-md-11 d-flex flex-column align-items-center">
                                        <div class="col-11 mb-2 d-flex flex-column align-items-center">
                                            <div class="col-12 d-flex justify-content-start">
                                                <label for="proveedor-newCompra" class="px-1 col-4">Proveedor:</label>
                                                <select id="proveedor-newCompra" name="proveedor-newCompra" class="ms-2 btn form-control @if ($errors->any()) @if (in_array("proveedor-newCompra",$errors->keys())) invalid @endif @endif">
                                                    <button type="button">
                                                        <selectedcontent></selectedcontent>
                                                    </button>
                                                    @if(isset($proveedores))
                                                        @php 
                                                            $proveedoresChecked = $producto->proveedores->pluck("id")->toArray();
                                                        @endphp
                                                        @if(is_array($proveedoresChecked))
                                                        @php
                                                            $provsCompraKeys=$producto->compras->pluck("proveedor")->toArray();
                                                            if(session()->has("compra")){
                                                                $compra=session("compra");
                                                                $idsNewProdsCompra=Arr::pluck($compra["productos"],"idProd-newCompra");
                                                                $indexProdNewCompra=array_search($producto->id,$idsNewProdsCompra);
                                                            }
                                                        @endphp
                                                            @foreach ($proveedores as $proveedor)
                                                                @if (in_array($proveedor->id,$proveedoresChecked))
                                                                    <option class="optProveedor-newCompra" @php
                                                                        $provKey=array_search($proveedor->id,$provsCompraKeys);
                                                                        if($provKey!==false){
                                                                            $prodsCompKeys=$producto->compras[$provKey]->productosCompra->pluck("producto")->toArray();
                                                                            $prodKey=array_search($producto->id,$prodsCompKeys);
                                                                            if($prodKey!==false){
                                                                                if(isset($indexIdProdCompra) && data_get($oldCompra,"proveedor")==$proveedor->id){
                                                                                    $precioCompraProd=BigDecimal::of(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"precio_compra"))->toScale(2, RoundingMode::HALF_UP);
                                                                                    echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                    echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                                }elseif(isset($indexProdNewCompra) && $compra["proveedor"]==$proveedor->id){
                                                                                    $precioCompraProd=BigDecimal::of($compra["productos"][$indexProdNewCompra]["precioCompra-newCompra"])->toScale(2,RoundingMode::HALF_UP);
                                                                                    echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                    echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                                }else{
                                                                                    $precioCompraProd=BigDecimal::of($producto->compras[$provKey]->productosCompra[$prodKey]->precio_compra)->toScale(2, RoundingMode::HALF_UP);
                                                                                    echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                    echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                                }
                                                                            }else{
                                                                                if(isset($indexIdProdCompra) && data_get($oldCompra,"proveedor")==$proveedor->id){
                                                                                    $precioCompraProd=BigDecimal::of(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"precio_compra"))->toScale(2, RoundingMode::HALF_UP);
                                                                                    echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                    echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                                }elseif(isset($indexProdNewCompra) && $compra["proveedor"]==$proveedor->id){
                                                                                    $precioCompraProd=BigDecimal::of($compra["productos"][$indexProdNewCompra]["precioCompra-newCompra"])->toScale(2,RoundingMode::HALF_UP);
                                                                                    echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                    echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                                }else{
                                                                                    echo "data-valuep='0'";
                                                                                    echo "data-printp='$0,00'";
                                                                                }
                                                                            }
                                                                        }else{
                                                                            if(isset($indexProdNewCompra) && $compra["proveedor"]==$proveedor->id){
                                                                                $precioCompraProd=BigDecimal::of($compra["productos"][$indexProdNewCompra]["precioCompra-newCompra"])->toScale(2,RoundingMode::HALF_UP);
                                                                                echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                            }elseif(isset($indexIdProdCompra) && data_get($oldCompra,"proveedor")==$proveedor->id){
                                                                                $precioCompraProd=BigDecimal::of(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"precio_compra"))->toScale(2, RoundingMode::HALF_UP);
                                                                                echo "data-valuep='".$precioCompraProd->__toString()."'";
                                                                                echo "data-printp='$".$fmt2->format($precioCompraProd->__toString())."'";
                                                                            }else{
                                                                                echo "data-valuep='0'";
                                                                                echo "data-printp='$0,00'";
                                                                            }
                                                                        }
                                                                    @endphp value="{{ $proveedor->id }}" {{ old("proveedor-newCompra") ? (old("proveedor-newCompra")==$proveedor->id ? "selected" : "") : (isset($indexIdProdCompra) ? ( session("trueEditCompra")["compra"]->proveedor==$proveedor->id ? "selected" : "") : (session("compra") ? (session("compra")["proveedor"] == $proveedor->id ? "selected" : "" ) : "")) }}@if(strlen($proveedor->nombre)>40)title="{{ $proveedor->nombre }}"@endif>@if(strlen($proveedor->nombre)>40){{ mb_substr($proveedor->nombre,0,40)."..." }}@else{{ $proveedor->nombre }}@endif</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-11 d-flex justify-content-end">
                                                <div class="col-8">
                                                    <div class="ps-2 invalid-feedback @error('proveedor-newCompra') is-invalid @enderror" id="invalid-proveedor-newCompra">
                                                        @error('proveedor-newCompra')
                                                            {{ str_replace("proveedor-new compra","proveedor",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="medidaProducto col-12 d-flex flex-column align-items-center">
                                            <div class="col-11 d-flex align-items-center">
                                                <label class="px-1 col-4" for="cantidad-newCompra">Cantidad a comprar: </label>
                                                <div class="col-6 d-flex align-items-center justify-content-start">
                                                    <div class="col-8 ms-2 d-flex">
                                                        <input class="form-control col-12 px-2 @if ($errors->any()) @if (in_array("cantidad-newCompra",$errors->keys())) invalid @endif @endif" type="number" id="cantidad-newCompra" name="cantidad-newCompra" min="@php
                                                            if(isset($indexIdProdCompra)){
                                                                $oldCompra=session("trueEditCompra")["compra"];
                                                                if(strcmp(data_get(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"productoRelacion"),"tipo_medida"),'Unidad')==0){
                                                                    echo 1;
                                                                }else{
                                                                    echo 0.01;
                                                                }                                                                
                                                            }else{
                                                                if(strcmp(data_get($producto,"tipo_medida"),'Unidad')==0){
                                                                    echo 1;
                                                                }else{
                                                                    echo 0.01;
                                                                }
                                                            }
                                                            
                                                        @endphp" step="1" value="@php
                                                            if(old("cantidad-newCompra")){
                                                                echo old("cantidad-newCompra");
                                                            }elseif(isset($indexIdProdCompra)){
                                                                $oldCompra=session("trueEditCompra")["compra"];
                                                                $oldCantPCompra=BigDecimal::of(data_get(data_get($oldCompra,"productosCompra")[$indexIdProdCompra],"cantidad"))->toScale(2,RoundingMode::HALF_UP);
                                                                $aux=$oldCantPCompra->toScale(0,RoundingMode::DOWN);
                                                                if($aux->isLessThan($oldCantPCompra)){
                                                                    echo $oldCantPCompra->__toString();
                                                                }else{
                                                                    echo $aux->__toString();
                                                                }
                                                            }elseif(isset($productoEditar)){
                                                                $editPrecComp=BigDecimal::of($productoEditar["cantidad-newCompra"]);
                                                                $aux=$editPrecComp->toScale(0,RoundingMode::DOWN);
                                                                if($aux->isLessThan($editPrecComp)){
                                                                    echo $editPrecComp->toScale(2,RoundingMode::HALF_UP)->__toString();
                                                                }else{
                                                                    echo $aux->__toString();
                                                                }
                                                            }else{
                                                                echo 1;
                                                            }
                                                        @endphp">
                                                    </div>
                                                    <div class="col-auto d-flex">
                                                        @if (strcmp(data_get($producto,"tipo_medida"),"Unidad")!=0)
                                                            <select name="medida-newCompra">
                                                                <option value="precio" {{ old("medida-newCompra") == "precio" ? "selected" : "" }}>$</option>
                                                                <option value="{{data_get($producto,"tipo_medida")}}"  {{ old("medida-newCompra") ? (in_array(old("medida-newCompra"),["Kilogramo","Metro","Litro"]) ? "selected" : "" ) : ($productoEditar ? (in_array($productoEditar["medida-newCompra"],["Kilogramo","Metro","Litro"]) ? "selected" : "" ) : (in_array(data_get($producto,"tipo_medida"),["Kilogramo","Metro","Litro"]) ? "selected" : "")) }}>
                                                                    @switch(data_get($producto,"tipo_medida")) @case("Kilogramo")Kg @break @case("Metro")Mt @break @case("Litro")Lt @break @endswitch
                                                                </option>
                                                            </select>
                                                        @else
                                                            <input type="hidden" name="medida-newCompra" value="Unidad">
                                                            <p class="p-0 m-0">U</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-11 d-flex justify-content-end">
                                                <div class="col-8">
                                                    <div class="ps-2 invalid-feedback @if ($errors->any()) @if (in_array("cantidad-newCompra",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-cantidad-newCompra">
                                                        @error('cantidad-newCompra')
                                                            {{ str_replace("cantidad-new compra","candidad",$message) }}
                                                        @enderror
                                                    </div>
                                                    <div class="ps-2 invalid-feedback @if ($errors->any()) @if (in_array("medida-newCompra",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-medida-newCompra">
                                                        @error('medida-newCompra')
                                                            {{ str_replace("medida-new compra","medida",$message) }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="button" id="btn-enviar-newCompra" class="btn ms-3" value="@php
                    if(isset($indexIdProdCompra)){
                        echo "Modificar compra";
                    }elseif(session()->has("compra")){
                        $compraIdProds=Arr::pluck(session("compra")["productos"],"idProd-newCompra");
                        if(in_array($producto->id,$compraIdProds)){
                            echo "Actualizar";
                        }else{
                            echo "Comprar";
                        }
                    }else{
                        echo "Comprar";
                    }
                @endphp">
                <input type="submit" id="btn-trueNewCompra" form="formNewCompra" class="btn ms-3" hidden>
                @if(!isset($indexIdProdCompra))
                    <input type="button" id="btn-añadirMas-newCompra" class="btn ms-3" value="@php
                        if(session()->has("compra")){
                            $compraIdProds=Arr::pluck(session("compra")["productos"],"idProd-newCompra");
                            if(in_array($producto->id,$compraIdProds)){
                                echo "Guardar cambios y añadir mas Productos a la Compra";
                            }else{
                                echo "Guardar y añadir mas Productos a la Compra";
                            }
                        }else{
                            echo "Guardar y añadir mas Productos a la Compra";
                        }
                    @endphp">
                    <input type="submit" name="tipoCompra" id="btn-trueAñadirMas-newCompra" form="formNewCompra" class="btn ms-3" value="añadirMas" hidden>
                @endif
                    <p id="btn-confirmMasCompra" class="d-none" data-bs-toggle="modal" data-bs-target="#confirmMasCompra"></p>
                    <p id="btn-confirmNewCompra" class="d-none" data-bs-toggle="modal" data-bs-target="#confirmNewCompra"></p>
                    <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    @if (!isset($indexIdProdCompra))
        <div class="modal fade" id="confirmMasCompra" aria-hidden="true" aria-labelledby="confirmMasCompraLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="confirmMasCompraLabel">Descartar compra</h1>
                        <div class="btn-close btn-closeDeleteProducto p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                            <h2 class="mt-2 mb-3">El proveedor seleccionado es distinto al seleccionado previamente.</h2>
                            <h2 class="mb-3">La compra anterior será <span class="text-decoration-underline">Eliminada</span>.</h2>
                            <h2 class="mb-3">¿Desea continuar?</h2>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <button type="submit" id="btnconfirmMasCompra" form="formNewCompra" class="btn ms-3" name="tipoCompra" value="añadirMas">Procesar</button>
                        <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal fade" id="confirmNewCompra" aria-hidden="true" aria-labelledby="confirmNewCompraLabel" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="confirmNewCompraLabel">@if (isset($indexIdProdCompra))Descartar modificación @else Descartar compra @endif</h1>
                    <div class="btn-close btn-closeDeleteProducto p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                    </div>
                </div>
                <div class="modal-body">
                        @if (isset($indexIdProdCompra))
                            <h2 class="mt-2 mb-3">El proveedor seleccionado es distinto al seleccionado previamente.</h2>
                            <h2 class="mb-3">La edición de la compra será <span class="text-decoration-underline">Descartada</span>.</h2>
                            <h2 class="mb-3">Se creará una nueva compra.</h2>
                            <h2 class="mb-3">¿Desea continuar?</h2>
                        @else
                            <h2 class="mt-2 mb-3">El proveedor seleccionado es distinto al seleccionado previamente.</h2>
                            <h2 class="mb-3">La compra anterior será <span class="text-decoration-underline">Eliminada</span>.</h2>
                            <h2 class="mb-3">¿Desea continuar?</h2>
                        @endif
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <input type="submit" id="btnconfirmNewCompra" form="formNewCompra" class="btn ms-3" value="Procesar">
                    <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
        

@endsection

@section('footer')
@vite([
    "resources/js/app/categorias.js",
    "resources/js/app/dropImgs.js",
    "resources/js/app/producto.js"
])
<script>
    (()=>{
        window.addEventListener("load",()=>{
            var invalidNewCompra=@php
                if($errors->any()){
                    $newCompraKeys=["precioCompra-newCompra","precioVenta-newCompra","proveedor-newCompra","cantidad-newCompra","medida-newCompra","fotos-newCompra"];
                    foreach($newCompraKeys as $key){
                        if(in_array($key,$errors->keys())){
                            echo json_encode(1);break;
                        }
                    }
                    echo json_encode(0);
                }else{
                    echo json_encode(0);
                }
            @endphp;
            if(invalidNewCompra){
                setTimeout(()=>{
                    document.querySelector("#btn-newCompra").click();
                },50);
            }
            @if (session()->has("adminSet"))
                var invalidDeleteProd=@php if($errors->any()){
                                        $deleteProd="passAdmin-deleteProd";
                                        if(in_array($deleteProd,$errors->keys())){
                                            echo json_encode(1);
                                        }else{
                                            echo json_encode(0);
                                        }
                                       }else{
                                        echo json_encode(0);
                                       }
                @endphp;
                if(invalidDeleteProd){
                    setTimeout(()=>{
                        const btnDelProd=document.querySelector("#btn-deleteProducto");
                        if(btnDelProd!=null){
                            btnDelProd.click();
                        }
                    },50);
                }

                var invalidEditProd=@php if($errors->any()){
                                        $editProdKeys=["idProd-editProd","nombre-editProd","descripcion-editProd","precioVenta-editProd","proveedor-editProd","codigo-editProd","catego-id-editProd","fotos-editProd"];
                                        foreach($editProdKeys as $key){
                                            if(in_array($key,$errors->keys())){
                                                echo json_encode(1);break;
                                            }
                                        }
                                            echo json_encode(0);
                                        }else{
                                            echo json_encode(0);
                                        }
                                    @endphp;
                if(invalidEditProd){
                    const categoEditProd=document.querySelector("[name=catego-id-editProd]");
                    oldCatego(categoEditProd);
                    const previewFotos=document.querySelector("#formEditProd .previewFotos");
                    if(previewFotos.childElementCount>0){
                        for (let foto of previewFotos.children) {
                            foto.addEventListener("click", () => deleteFoto(foto));
                        }
                    }
                    setTimeout(()=>{
                        document.querySelector("#btn-editProducto").click();
                    },50);
                }else{
                    const categoEditProd=document.querySelector("[name=catego-id-editProd]");
                    oldCatego(categoEditProd,true);
                    const previewFotos=document.querySelector("#formEditProd .previewFotos");
                    if(previewFotos.childElementCount>0){
                        for (let foto of previewFotos.children) {
                            foto.addEventListener("click", () => deleteFoto(foto));
                        }
                    }
                    const oldFotos= @php
                                        $jsonFotos=["fotos"=>[],"data-foto"=>[]];
                                        for($i=0;$i<sizeof($oldFotos["data-foto"]);$i++){
                                            $jsonFotos["fotos"][]=$oldFotos["fotos"][$i];
                                            $jsonFotos["data-foto"][]=$oldFotos["data-foto"][$i];
                                        }
                                        echo json_encode($jsonFotos);
                                    @endphp;
                    const limpiarEditProd = document.querySelector("#btn-limpiar-editProd");
                    limpiarEditProd.addEventListener("click",()=>{
                        if(oldFotos!=null){
                            const prevFotos = limpiarEditProd.closest(".modal-content").querySelector("form .fotos-editProd .previewFotos");
                            const selectFotos = limpiarEditProd.closest(".modal-content").querySelector("form #fotos-editProd");
                            while(prevFotos.childElementCount>0){
                                prevFotos.lastElementChild.click();
                            }
                            for(let i=0;i<oldFotos["fotos"].length;i++){
                                let img=new Image();
                                img.id=oldFotos["data-foto"][i];
                                img.src=oldFotos["fotos"][i];
                                img.alt=img.id;
                                img.setAttribute("title","Eliminar");
                                img.setAttribute("class","img-fluid my-2 me-2");
                                img.addEventListener("click",()=>deleteFoto(img));
                                prevFotos.appendChild(img);
                                let newOption=document.createElement("option");
                                newOption.setAttribute("data-foto",img.id);
                                newOption.setAttribute("value",img.src);
                                newOption.selected=true;
                                selectFotos.appendChild(newOption);
                            }
                        }
                        setTimeout(()=>{
                            const oldCategoId={{ json_encode($producto->categoria) }};
                            if(oldCatego!=null){
                                const catego=document.querySelector("#formEditProd .categorias-editProd .categorias [data-id='"+oldCategoId+"']");
                                catego.click();
                            }
                        },5);
                    });
                }
            @endif
        });
        function deleteFoto(foto){
            const optFoto = foto.parentElement.previousElementSibling.querySelector("option[data-foto='"+foto.id+"']");
            const select = foto.parentElement;
            optFoto.remove();
            foto.remove();
            if(select.childElementCount<4){
                select.classList.remove("masTres");
            }
        }

        @if (session()->has("adminSet"))
            function oldCatego(oldCategoId,noEdit=false){
                if(!noEdit){
                    if(oldCategoId.value.trim()!==""){
                        const catego=oldCategoId.previousElementSibling.querySelector("[data-id='"+oldCategoId.value+"']");
                        if(catego!=null){
                            catego.click();
                            oldCategoId.nextElementSibling.classList.add("valid");
                            oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-valid");
                            oldCategoId.nextElementSibling.classList.remove("invalid");
                            oldCategoId.nextElementSibling.nextElementSibling.classList.remove("is-invalid");
                        }else{
                            oldCategoId.nextElementSibling.classList.add("invalid");
                            oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-invalid");
                            oldCategoId.nextElementSibling.classList.remove("valid");
                            oldCategoId.nextElementSibling.nextElementSibling.classList.remove("is-valid");
                        }
                    }else{
                        oldCategoId.nextElementSibling.classList.add("invalid");
                        oldCategoId.nextElementSibling.nextElementSibling.classList.add("is-invalid");
                    }
                }else{
                    if(oldCategoId.value.trim()!==""){
                        const catego=oldCategoId.previousElementSibling.querySelector("[data-id='"+oldCategoId.value+"']");
                        if(catego!=null){
                            catego.click();
                        }
                    }
                }
                return null;
            }
        @endif

        const editProdCompra={{ session("editarCompra") ? json_encode(1) : json_encode(0) }};
        if(editProdCompra){
            window.addEventListener("load",()=>{
                const btn=document.querySelector("#btn-newCompra");
                if(btn!=null){
                    btn.click();
                }

            })
        }

        window.addEventListener("load",()=>{
            const oldFotosNewCompra=@php if(isset($oldFotosNewCompra)){
                                        $jsonFotos=["fotos"=>[],"data-foto"=>[]];
                                        for($i=0;$i<sizeof($oldFotosNewCompra["data-foto"]);$i++){
                                            $jsonFotos["fotos"][]=$oldFotosNewCompra["fotos"][$i];
                                            $jsonFotos["data-foto"][]=$oldFotosNewCompra["data-foto"][$i];
                                        }
                                        echo json_encode($jsonFotos);
                                    }
                                    else{
                                        echo json_encode(["fotos"=>[]]);
                                    }
                                @endphp;
            if(oldFotosNewCompra!=null && oldFotosNewCompra["fotos"].length>0){
                const prevFotosEdit = document.querySelector("#div-fotos-newCompra .previewFotos");
                if(prevFotosEdit!=null){
                    const fotosEditCompra=prevFotosEdit.children;
                    for(const fotoEditCompra of fotosEditCompra){
                        fotoEditCompra.addEventListener("click",(e)=>deleteFoto(fotoEditCompra));
                    };
                }
            }
            const limpiarEditProd = document.querySelector("#btn-limpiar-newCompra");
            if(limpiarEditProd!=null){
                limpiarEditProd.addEventListener("click",()=>{
                    if(oldFotosNewCompra!=null){
                        const prevFotos = limpiarEditProd.closest(".modal-content").querySelector("form .fotos-newCompra .previewFotos");
                        const selectFotos = limpiarEditProd.closest(".modal-content").querySelector("form #fotos-newCompra");
                        while(prevFotos.childElementCount>0){
                            prevFotos.lastElementChild.click();
                        }
                        for(let i=0;i<oldFotosNewCompra["fotos"].length;i++){
                            let img=new Image();
                            img.id=oldFotosNewCompra["data-foto"][i];
                            img.src=oldFotosNewCompra["fotos"][i];
                            img.alt=img.id;
                            img.setAttribute("title","Eliminar");
                            img.setAttribute("class","img-fluid my-2 me-2");
                            img.addEventListener("click",()=>deleteFoto(img));
                            prevFotos.appendChild(img);
                            let newOption=document.createElement("option");
                            newOption.setAttribute("data-foto",img.id);
                            newOption.setAttribute("value",img.src);
                            newOption.selected=true;
                            selectFotos.appendChild(newOption);
                        }
                    }
                });
            }
        });
    })();
    @if(!isset($indexIdProdCompra))
    (()=>{
        const btnEnviar=document.querySelector("#btn-añadirMas-newCompra");
        if(btnEnviar!=null){
            btnEnviar.addEventListener("click",(e)=>{
                e.preventDefault;
                const provInCartCompra=@php if(session()->has("compra"))echo json_encode(session("compra")["proveedor"]);else echo json_encode(0); @endphp;
                if(provInCartCompra!=0){
                    const closeModNewCompra=btnEnviar.closest(".modal").querySelector(".btn-closeNewCompra");
                    const provSelected=btnEnviar.closest(".modal").querySelector("#proveedor-newCompra");
                    if(provSelected!=null && closeModNewCompra!=null){
                        if(provSelected.value!=provInCartCompra){
                            closeModNewCompra.click();
                            const opModalConf=document.querySelector("#btn-confirmMasCompra");
                            if(opModalConf!=null){
                                opModalConf.click();
                            }
                        }else{
                            btnEnviar.nextElementSibling.click();
                        }
                    }
                }else{
                    btnEnviar.nextElementSibling.click();
                }
            });
        }
    })();
    @endif
    (()=>{
        const confirmNewCompra=document.querySelector("#btn-enviar-newCompra");
        if(confirmNewCompra!=null){
            confirmNewCompra.addEventListener("click",(e)=>{
                e.preventDefault();
                const provInCartCompra=@php if(isset($indexIdProdCompra)) echo json_encode(session("trueEditCompra")["compra"]->proveedor);elseif(session()->has("compra"))echo json_encode(session("compra")["proveedor"]);else echo json_encode(0); @endphp;
                if(provInCartCompra!=0){
                    const closeModNewCompra=confirmNewCompra.closest(".modal").querySelector(".btn-closeNewCompra");
                    const provSelected=confirmNewCompra.closest(".modal").querySelector("#proveedor-newCompra");
                    if(provSelected!=null && closeModNewCompra!=null){
                        if(provSelected.value!=provInCartCompra){
                            closeModNewCompra.click();
                            const opModalConf=document.querySelector("#btn-confirmNewCompra");
                            if(opModalConf!=null){
                                opModalConf.click();
                            }
                        }else{
                            confirmNewCompra.nextElementSibling.click();
                        }
                    }
                }else{
                    confirmNewCompra.nextElementSibling.click();
                }
            });
        }
    })();
</script>
@endsection