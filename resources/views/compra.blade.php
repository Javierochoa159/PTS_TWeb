@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
        "resources/css/myStyles/compra.css",
        "resources/css/myStyles/procesarCompra.css"
    ])
@endsection

@php
    $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 4);
@endphp

@section("content")
    <x-portada-component/>
    <x-buscador-component type="activo"/>
    <x-mensaje-div-component/>
    
<div class="m-2 py-5 d-flex primerDivProd flex-column align-items-center justify-content-center">
        <div class="col-11 productos p-2">
            <table class="col-12">
                <thead>
                    <tr>
                        <th class="col-5 firstTh"><p class="p-2 m-0">Producto</p></th>
                        <th class="col-2"><p class="p-2 m-0">Precio de compra</p></th>
                        <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                        <th class="col-1"><p class="p-2 m-0">Medida</p></th>
                        <th class="col-2"><p class="p-2 m-0">Total</p></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compra->productosCompra as $producto)
                        <x-productos-tabla-component :producto="$producto" tipo="comprado"/>
                    @endforeach
                </tbody>
            </table>
            <table class="col-12 footerTable">
                <thead>
                    <tr>
                        <th class="col-3">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Cantidad de Productos:</p>
                                <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">{{ $compra->productosCompra ? sizeof($compra->productosCompra) : "0" }}</p>
                            </div>
                        </th>
                        <th class="col-5 centerTable">
                            <div class="col-12 d-flex align-items-center">
                                <p class="col-12 p-2 m-0">Fecha de Compra: {{ date_create($compra->fecha_compra)->format("d/m/Y H:i") }}</p>
                            </div>
                        </th>
                        <th class="col-4">
                            <div class="col-12 d-flex align-items-center justify-between">
                                <p class="col-auto py-2 ps-2 m-0">Precio final:</p>
                                <p id="totalCompra" class="col-auto py-2 pe-2 m-0 text-end txtTh">${{ $fmt->format($compra->monto_total) }}</p>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th><p class="col-12 p-2 m-0">Proveedor</p></th>
                        <th class="centerTable"><p class="p-1 m-0 text-center">Recibos</p></th>
                        <th>
                            <div class="col-12 d-flex align-items-center justify-content-evenly">
                                <p class="col-5 p-2 m-0 text-start">Método de pago:</p>
                                <p class="col-3 p-2 m-0 text-start">{{ $compra->tipo_pago }}</p>
                            </div>
                        </th>
                    </tr>
                    <tr class="lastTr">
                        <td class="proveedorCompra">
                            <div class="col-12">
                                <a href="{{ route("proveedor.proveedor",$compra->proveedor) }}" class="p-0 m-0 text-reset text-decoration-none"><p class="p-1 m-0">{{ $compra->nombre }}</p></a>
                            </div>
                        </td>
                        <td class="d-flex flex-column align-items-center justify-content-start recibosTd position-relative">
                            <div id="recibosCompra" class="carousel slide col-8 d-flex align-items-center justify-content-center" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @php $i=0; @endphp
                                    @foreach ($compra->recibos as $recibo)
                                        <div class="carousel-item {{ $i==0 ? "active" : "" }}">
                                            <img src="@php
                                                $foto=$recibo->url_img;
                                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                                    echo route("inicio.index").$foto;
                                                }else{
                                                    echo $recibo->url_img_online;
                                                }
                                            @endphp" class="img-fluid" alt="reciboCompra" draggable="false">
                                        </div>
                                        @php $i++; @endphp
                                    @endforeach
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#recibosCompra" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#recibosCompra" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </td>
                        <td class="lastTd verticalAlign">
                            <div class="col-12 py-3 ps-2 d-flex align-items-center justify-content-evenly border_bottom">
                                <h5 class="col-auto m-0 p-0 me-1">Ultima modificación:</h5>
                                <p class="col-auto p-0 m-0">{{ $compra->updated_at ? date_create($compra->updated_at)->format("d/m/Y H:i") : "--/--/---- --:--" }}</p>
                            </div>
                        </td>
                    </tr>
                </thead>
            </table>
            <table class="col-12 footerTable">
                <tbody>
                    <tr class="col-12 tdBtnsCompra">
                        <td colspan="{{ sizeof($compra->recibos)>0 ? "3" : "2" }}" class="tdBtnsCompra">
                            <div class="col-12 d-flex align-items-center justify-content-center btnsCompra">
                                <div class="col-12 py-2 d-flex align-items-center justify-content-evenly">
                                    @if (session()->has("adminSet"))
                                        @if(strcmp($compra->estado_entrega,"Completa")!=0)
                                            @php
                                                $createdAt=date_create($compra->created_at)->getTimestamp();
                                                $maxDateEdit=date_create($compra->created_at)->getTimestamp()+259200;
                                                $now=now()->getTimestamp();
                                                $validDeleteProd=date_create($compra->created_at)->getTimestamp()+1209600;
                                            @endphp
                                            @if ($createdAt<=$now && $now<=$maxDateEdit)
                                                <div class="col-auto my-2 d-flex align-items-center justify-between">
                                                    <button type="button" id="btn-editCompra" class="btn p-2 btnEditC" data-bs-toggle="modal" data-bs-target="#modalEditarCompra">Editar compra</button>
                                                </div>
                                                <div class="col-auto my-2 d-flex align-items-center justify-between">
                                                    <button type="button" id="btn-revertirCompra" class="btn p-2 btnRevertC" data-bs-toggle="modal" data-bs-target="#modalRevertirCompra">Deshacer compra</button>
                                                </div>
                                            @endif
                                            @if ($now>$validDeleteProd)
                                                <button type="button" id="btn-deleteCompra" class="btn p-2 btnDeleteC" data-bs-toggle="modal" data-bs-target="#modalEliminarCompra">Eliminar compra</button>
                                            @endif
                                        @endif
                                        <!--<div class="col-auto my-2 d-flex align-items-center justify-between">
                                            <form action="" method="post">
                                                <input type="hidden" name="idCompra" value="">
                                                <button type="submit" class="btn btnConfirmarDelete p-2" name="devolucion" value="devolucion">Editar productos</button>
                                            </form>
                                        </div>-->
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @if (session()->has("adminSet"))
        <div class="modal fade" id="modalEliminarCompra" aria-hidden="true" aria-labelledby="modalEliminarCompraLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEliminarCompraLabel">Eliminar Compra</h1>
                        <div class="btn-close btn-closeDeleteCompra p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-deleteProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate action="{{ route("compra.deletecompra") }}" method="post" id="formEliminarCompra" autocomplete="off">
                            @csrf
                            <input type="number" value="{{ $compra->id }}" name="idCompra-deleteCompra" required hidden>
                            <h2 class="mt-2 mb-3">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Eliminación</span>.</h2>
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-deleteCompra",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-deleteCompra" name="passAdmin-deleteCompra" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-deleteCompra",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-deleteCompra">
                                @error('passAdmin-deleteCompra')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete compra","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-deleteCompra" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-delete compra","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-delete compra","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <button type="submit" id="btnEliminarCompra" form="formEliminarCompra" class="btn ms-3" name="eliminarCompra" value="delete">Eliminar</button>
                        <input type="button" class="btn me-3" data-bs-dismiss="modal" value="Cancelar">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalRevertirCompra" aria-hidden="true" aria-labelledby="modalRevertirCompraLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalRevertirCompraLabel">Deshacer Compra</h1>
                        <div class="btn-close btn-closeRevertCompra p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-deleteProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate action="{{ route("compra.revertircompra") }}" method="post" id="formRevertirCompra" autocomplete="off">
                            @csrf
                            <input type="number" value="{{ $compra->id }}" name="idCompra-revertCompra" required hidden>
                            <h2 class="mt-2 mb-3">Ingrese su contraseña para poder <span class="text-decoration-underline">Deshacer</span> la compra.</h2>
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-revertCompra",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-revertCompra" name="passAdmin-revertCompra" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-revertCompra",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-revertCompra">
                                @error('passAdmin-revertCompra')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-revert compra","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-revertCompra" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-revert compra","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-revert compra","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <button type="submit" id="btnRevertirCompra" form="formRevertirCompra" class="btn ms-3" name="revertirCompra" value="revert">Deshacer</button>
                        <input type="button" class="btn me-3" data-bs-dismiss="modal" value="Cancelar">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalEditarCompra" aria-hidden="true" aria-labelledby="modalEditarCompraLabel" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEditarCompraLabel">Editar Compra</h1>
                        <div class="btn-close btn-closeEditCompra p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                            <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                        </div>
                    </div>
                    <div class="modal-body">
                        <form class="form-editProducto text-center d-flex flex-column align-items-center justify-content-center" novalidate action="{{ route("compra.editarcompra") }}" method="post" id="formEditarCompra" autocomplete="off">
                            @csrf
                            <input type="number" value="{{ $compra->id }}" name="idCompra-editCompra" required hidden>
                            <h2 class="mt-2 mb-3">Ingrese su contraseña para proceder con la <span class="text-decoration-underline">Edición</span></h2>
                            <input class="form-control @if ($errors->any()) @if (in_array("passAdmin-editCompra",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin-editCompra" name="passAdmin-editCompra" value="" placeholder="Su contraseña">
                            <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin-editCompra",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin-editCompra">
                                @error('passAdmin-editCompra')
                                    @if (str_contains($message,"obligatorio"))
                                    <p class="p-0 m-0">La contraseña es obligatoria.</p>
                                    @elseif (str_contains($message,"Formato"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-edit compra","contraseña",$message) }}</p>
                                    <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                                    <div name="validEspecialsPass" id="validEspecialsPass-editCompra" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                        <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                                    </div>
                                    @elseif(str_contains($message,"incorrecta"))
                                    <p class="p-0 m-0">{{ str_replace("pass admin-edit compra","contraseña",$message) }}</p>
                                    @else
                                    <p class="p-0 m-0">{{ "La ".str_replace("pass admin-edit compra","contraseña",$message) }}</p>
                                    @endif
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <button type="submit" id="btnEditarCompra" form="formEditarCompra" class="btn ms-3" name="editarCompra" value="edit">Editar</button>
                        <input type="button" class="btn me-3" data-bs-dismiss="modal" value="Cancelar">
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('footer')
@vite([
    "resources/js/app/dropImgs.js",
    "resources/js/app/compra.js",
])
<script>
    @if (session()->has("adminSet"))
        var invalidDeleteCompra=@php if($errors->any()){
                                $deleteCompra="passAdmin-deleteCompra";
                                if(in_array($deleteCompra,$errors->keys())){
                                    echo json_encode(1);
                                }else{
                                    echo json_encode(0);
                                }
                                }else{
                                echo json_encode(0);
                                }
        @endphp;
        if(invalidDeleteCompra){
            setTimeout(()=>{
                const btnDelCompra=document.querySelector("#btn-deleteCompra");
                if(btnDelCompra!=null){
                    btnDelCompra.click();
                }
            },50);
        }
        var invalidDeleteCompra=@php if($errors->any()){
                                $revertCompra="passAdmin-revertCompra";
                                if(in_array($revertCompra,$errors->keys())){
                                    echo json_encode(1);
                                }else{
                                    echo json_encode(0);
                                }
                                }else{
                                echo json_encode(0);
                                }
        @endphp;
        if(invalidDeleteCompra){
            setTimeout(()=>{
                const btnDelCompra=document.querySelector("#btn-revertirCompra");
                if(btnDelCompra!=null){
                    btnDelCompra.click();
                }
            },50);
        }
        var invalidEditCompra=@php if($errors->any()){
                                $editCompra="passAdmin-editCompra";
                                if(in_array($editCompra,$errors->keys())){
                                    echo json_encode(1);
                                }else{
                                    echo json_encode(0);
                                }
                                }else{
                                echo json_encode(0);
                                }
        @endphp;
        if(invalidEditCompra){
            setTimeout(()=>{
                const btnDelCompra=document.querySelector("#btn-editCompra");
                if(btnDelCompra!=null){
                    btnDelCompra.click();
                }
            },50);
        }
    @endif
    
</script>
@endsection

