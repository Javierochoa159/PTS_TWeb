@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/proveedor.css"
    ])
@endsection

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

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>
    <div class="d-flex align-items-center justify-content-center">
        <div class="col-11 primerDivProv py-5 mb-5">
            <div class="col-12 d-flex flex-column align-items-center justify-content-start">
                <div class="proveedor col-9 d-flex flex-column justify-content-between">    
                    <div class="infoProv d-flex justify-content-{{ session()->has("adminSet") ? "between" : "start" }}">
                        <h5 class="col-auto p-2 m-1 me-0">Nombre:</h5>
                        <h5 class="col-10 p-2 ps-0 m-1 ms-0">{{ $proveedor->nombre }}</h5>
                        @if (session()->has("adminSet"))
                            <div class="d-flex flex-column justify-content-center">
                                <button id="btn-editProveedor" class="editProveedor me-2 mt-2" data-bs-toggle="modal" data-bs-target="#editProveedor">
                                    <div class="d-flex align-items-center">
                                        <img class="img-fluid" src="{{ asset("build/assets/icons/edit.svg") }}" title="Editar" alt="editar proveedor" draggable="false">
                                    </div>
                                </button>
                                <button id="btn-deleteProveedor" class="deleteProveedor me-2 mt-2" data-bs-toggle="modal" data-bs-target="#deleteProveedor">
                                    <div class="d-flex align-items-center">
                                        <img class="img-fluid" src="{{ asset("build/assets/icons/delete.svg") }}" title="Eliminar" alt="eliminar proveedor" draggable="false">
                                    </div>
                                </button>
                            </div>
                        @endif
                    </div>
                    <h6 class="p-2 m-1 mb-3">Dirección: {{ $proveedor->direccion }}</h6>
                    <h6 class="p-2 m-1 mb-3">Correo: {{ $proveedor->correo }}</h6>
                    <h6 class="p-2 m-1 mb-3">Teléfono: {{ str_replace(["-","(",")"],"",$proveedor->telefono) }}</h6>
                </div>
                <div class="productos col-9 d-flex flex-column align-items-center justify-content-center mt-3">
                    <button id="btn-productosProv" class="col-10 btn d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-productosProv" aria-expanded="false">
                        Productos vendidos
                        <img class="ms-2 img-fluid" src="{{ asset("build/assets/icons/down.svg") }}" alt="down" draggable="false">
                    </button>
                    <div class="col-12 mt-2 collapse" id="collapse-productosProv">
                        <div class="card card-body col-12 p-2">
                            @if (sizeof($proveedor->productos)>0)
                                @php if(session()->has("compra"))if(session("compra")["proveedor"]==$proveedor->id){
                                        $idsProdsProv=Arr::pluck(session("compra")["productos"],"idProd-newCompra");
                                    }
                                @endphp
                                @foreach ($proveedor->productos as $producto)
                                    <div class="producto col-12 d-flex align-items-center justify-content-center position-relative">
                                        <div class="cabezaProducto col-2 p-1 m-1">
                                            <a class="text-decoration-none text-reset @if ($producto->cantidad_disponible<=$producto->cantidad_minima)position-relative @endif" href="{{ route("producto.producto",$producto->id) }}">
                                                <img class="img_fluid" src="@php
                                                    $foto=$producto->foto->url_img;
                                                    if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                                        echo route("inicio.index").$foto;
                                                    }else{
                                                        echo $producto->foto->url_img_online;
                                                    }
                                                @endphp" title="Abrir" alt="producto_{{ $producto->id }}" draggable="false">
                                                <x-alert-stock-component :cantidad="$producto->cantidad_disponible" :minimo="$producto->cantidad_minima"/>
                                            </a>
                                        </div>
                                        <div class="detalleProd p-1 col-10 d-flex flex-column justify-content-between align-items-start">
                                            <h6 class="p-0 m-0">{{ $producto->nombre }}</h6>
                                            <div class="col-12">
                                                <div class="col-12 d-flex align-items-center justify-start">
                                                    <p class="cantDispo text-start p-0 m-0">Total disponible: @php 
                                                        $cantDisp=BigDecimal::of($producto->cantidad_disponible)->toScale(4,RoundingMode::HALF_UP);
                                                        $aux=BigDecimal::of($producto->cantidad_disponible)->toScale(0,RoundingMode::DOWN);
                                                        if($aux->isLessThan($cantDisp)){
                                                            echo $fmt4->format($cantDisp->__toString());
                                                        }else{
                                                            echo $fmt0->format($aux->__toString());
                                                        }
                                                        @endphp @if(strcmp($producto->tipo_medida,"Unidad")==0) {{ $producto->tipo_medida."es" }} @else {{ $producto->tipo_medida."s" }} @endif</p>
                                                    <p class="col-6 text-start p-0 m-0">Precio de Compra: {{ $producto->productoCompra ? (isset($producto->productoCompra[0]) ? "$".$fmt2->format($producto->productoCompra[0]->precio_compra) : "$0,00") : "$0,00" }}@switch ($producto->tipo_medida) @case("Unidad"){{ " c/u" }}@break @case("Kilogramo"){{ " por Kilo" }}@break @case("Litro"){{ " por Litro" }}@break @case("Metro"){{ " por Metro" }}@break @endswitch</p>
                                                </div>
                                                <div class="col-12 d-flex align-items-center">
                                                    <p class="col-6 m-0 py-1">Ultima compra: {{ $producto->productoCompra ? (isset($producto->productoCompra[0]) ? date_create($producto->productoCompra[0]->productosCompra->fecha_compra)->format("d/m/Y H:i") : "--/--/---- --:--") : "--/--/---- --:--" }}</p>
                                                    @if(isset($idsProdsProv) && in_array($producto->id,$idsProdsProv))
                                                        <p class="col-6 text-end p-0 m-0 pe-2">Comprando</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center pt-5"><h4>Sin productos a la venta</h4></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if (session()->has("adminSet"))
                <div class="modal fade" id="editProveedor" aria-hidden="true" aria-labelledby="editProveedorLabel" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="editProveedorLabel">Nuevo Proveedor</h1>
                                <div class="btn-close btn-closeEditProveedor p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                                    <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                                </div>
                            </div>
                            <div class="modal-body py-3">
                                <form class="form-editProveedor" method="post" novalidate action="{{ route("proveedor.editprov") }}" id="formEditProv" autocomplete="off">
                                    @csrf
                                    <input type="number" value="{{ $proveedor->id }}" name="id-editProv" required hidden>
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="col-9">
                                            <div class="col-md-12 mb-3">
                                                <label class="ps-1" for="nombre-editProv">Nombre del Proveedor:</label>
                                                <input type="text" class="form-control @if ($errors->any()) @if (in_array("nombre-editProv",$errors->keys())) invalid @endif @endif" id="nombre-editProv" name="nombre-editProv" placeholder="Nombre del Proveedor" value="{{ old("nombre-editProv") ? old("nombre-editProv") : $proveedor->nombre}}" required>
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("nombre-editProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-nombre-editProv">
                                                    @error('nombre-editProv')
                                                        {{ str_replace("nombre-edit prov","nombre",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="ps-1" for="direccion-editProv">Dirección del Proveedor:</label>
                                                <input type="text" class="form-control @if ($errors->any()) @if (in_array("direccion-editProv",$errors->keys())) invalid @endif @endif" id="direccion-editProv" name="direccion-editProv" placeholder="Dirección del Proveedor" value="{{ old("direccion-editProv") ? old("direccion-editProv") : $proveedor->direccion}}" required>
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("direccion-editProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-direccion-editProv">
                                                    @error('direccion-editProv')
                                                        {{ str_replace("direccion-edit prov","direccion",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="ps-1" for="correo-editProv">Correo del Proveedor:</label>
                                                <input type="email" class="form-control @if ($errors->any()) @if (in_array("correo-editProv",$errors->keys())) invalid @endif @endif" id="correo-editProv" name="correo-editProv" placeholder="Correo@Proveedor.com" value="{{ old("correo-editProv") ? old("correo-editProv") : $proveedor->correo }}" required>
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("correo-editProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-correo-editProv">
                                                    @error('correo-editProv')
                                                        {{ str_replace("correo-edit prov","correo",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="ps-1" for="telefono-editProv">Teléfono del Proveedor:</label>
                                                <input type="number" step="1" class="form-control @if ($errors->any()) @if (in_array("telefono-editProv",$errors->keys())) invalid @endif @endif" minlength="9" maxlength="14" id="telefono-editProv" name="telefono-editProv" placeholder="Teléfono del Proveedor" value="{{ old("telefono-editProv") ? old("telefono-editProv") : str_replace(["-","(",")","+"],"",$proveedor->telefono) }}" required>
                                                <div class="invalid-feedback @if ($errors->any()) @if (in_array("telefono-editProv",$errors->keys())) is-invalid @else is-valid @endif @endif" id="invalid-telefono-editProv">
                                                    @error('telefono-editProv')
                                                        {{ str_replace("telefono-edit prov","telefono",$message) }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer d-flex align-items-center justify-content-between">
                                <input type="submit" id="btn-enviar-editprov" form="formEditProv" class="btn ms-3" value="Editar proveedor"></input>
                                <div class="col-auto d-flex align-items-center justify-content-between mx-3">
                                    <input type="reset" id="btn-limpiar-editprov" form="formEditProv" class="btn me-5" value="Reiniciar">
                                    <button type="button" class="btn me-3" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="deleteProveedor" aria-hidden="true" aria-labelledby="deleteProveedorLabel" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="deleteProveedorLabel">Eliminar Proveedor</h1>
                                <div class="btn-close btn-closeDeleteProveedor p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                                    <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                                </div>
                            </div>
                            <div class="modal-body py-3">
                                <form class="form-deleteProveedor text-center" method="post" novalidate action="{{ route("proveedor.deleteprov") }}" id="formDeleteProv" autocomplete="off">
                                    @csrf
                                    <input type="number" value="{{ $proveedor->id }}" name="idProv-deleteProv" required hidden>
                                    <h2 class="mt-2 mb-3">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Eliminación</span></h2>
                                    <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deleteProv",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deleteProv" name="passAdmin-deleteProv" value="" placeholder="Su contraseña">
                                    <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deleteProv",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deleteProv">
                                        @error('passAdmin-deleteProv')
                                            @if (str_contains($message,"obligatorio"))
                                            <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                            @elseif (str_contains($message,"Formato"))
                                            <p class="p-0 m-0">{{ str_replace("pass admin-delete prod","contraseña",$message) }}</p>
                                            <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                            <div name="validEspecialsPass" id="validEspecialsPass-deleteProv" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                                <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                            </div>
                                            @elseif(str_contains($message,"incorrecta"))
                                            <p class="p-0 m-0">{{ str_replace("pass admin-delete prov","contraseña",$message) }}</p>
                                            @else
                                            <p class="p-0 m-0">{{ "La ".str_replace("pass admin-delete prov","contraseña",$message) }}</p>
                                            @endif
                                        @enderror
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer d-flex align-items-center justify-content-between">
                                <input type="submit" id="btn-enviar-deleteprov" name="eliminarProveedor" form="formDeleteProv" class="btn ms-3" value="Proceder"></input>
                                <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('footer')
<script>
    window.addEventListener("load",()=>{
        @if (session()->has("adminSet"))
            var invalidDeleteProv= @php if($errors->any()){
                                    $deleteProv="passAdmin-deleteProv";
                                    if(in_array($deleteProv,$errors->keys())){
                                        echo json_encode(1);
                                    }else{
                                        echo json_encode(0);
                                    }
                                    }else{
                                        echo json_encode(0);
                                    }
            @endphp;
            if(invalidDeleteProv){
                setTimeout(()=>{
                    const btnDelProv=document.querySelector("#btn-deleteProveedor");
                    if(btnDelProv!=null){
                        btnDelProv.click();
                    }
                },50);
            }
            var invalidEditProv= @php if($errors->any()){
                                        $editProvKeys=["idProv-editProv","nombre-editProv","direccion-editProv","correo-editProv","telefono-editProv"];
                                        foreach($editProvKeys as $key){
                                            if(in_array($key,$errors->keys())){
                                                echo json_encode(1);break;
                                            }
                                        }
                                        echo json_encode(0);
                                     }else{
                                        echo json_encode(0);
                                     }
                                @endphp;
            if(invalidEditProv){
                setTimeout(()=>{
                    const btnEditProv=document.querySelector("#btn-editProveedor");
                    if(btnEditProv!=null){
                        btnEditProv.click();
                    }
                },50);
            }
        @endif
        const compProds=@php if(session()->has("compra"))if(session("compra")["proveedor"]==$proveedor->id)echo json_encode(1);else echo json_encode(0);else echo json_encode(0) @endphp;
        if(compProds){
            const btnProds=document.querySelector("#btn-productosProv");
            if(btnProds!=null){
                btnProds.click();
                btnProds.focus();
            }
        }
    });
</script>
@endsection