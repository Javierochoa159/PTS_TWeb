@extends('layouts.index')

@section('head')
    @vite([
        "resources/css/myStyles/inicio.css",
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
            <form id="formConfirmarCompra" action="{{ route("compra.savecompra") }}" method="post" novalidate>
                @csrf
                <table class="col-12">
                    <thead>
                        <tr>
                            <th class="col-5 firstTh"><p class="p-2 m-0">Producto a comprar</p></th>
                            <th class="col-2"><p class="p-2 m-0">Precio de compra</p></th>
                            <th class="col-2"><p class="p-2 m-0">Cantidad</p></th>
                            <th class="col-1"><p class="p-2 m-0">Medida</p></th>
                            <th class="col-2"><p class="p-2 m-0">Total</p></th>
                        </tr>
                    </thead>
                    @if(session()->has("trueEditCompra"))
                        <tbody id="tableProdsEditCompra">
                            @foreach (data_get($compra,"productosCompra") as $producto)
                                @php
                                    $deleted=data_get($producto,"deleted");
                                @endphp
                                @if (!isset($deleted))
                                    <x-productos-tabla-component :producto="$producto" tipo="editCompra"/>
                                @endif
                            @endforeach
                        </tbody>
                    @elseif(session()->has("compra"))
                    <tbody id="tableProdsCompra">
                        @foreach ($productos as $producto)
                            <x-productos-tabla-component :producto="$producto" tipo="comprar"/>
                        @endforeach
                    </tbody>
                    @endif
                </table>
                <table class="col-12 footerTable">
                    <thead>
                        <tr>
                            <th class="col-3">
                                <div class="col-12 d-flex align-items-center justify-between">
                                    <p class="col-auto py-2 ps-2 m-0">Cantidad de Productos:</p>
                                    <p id="totalProductos" class="col-4 py-2 pe-2 m-0 txtTh">@php
                                        if(session()->has("trueEditCompra")){
                                            $totalProds=data_get($compra,"productosCompra");
                                            $sizeProds=0;
                                            foreach($totalProds as $prod){
                                                $deleted=data_get($prod,"deleted");
                                                if(!isset($deleted)){
                                                    $sizeProds+=1;
                                                }
                                            }
                                            echo $sizeProds;
                                        }elseif(isset($productos)){
                                            echo sizeof($productos);
                                        }else{
                                            echo "0";
                                        }
                                    @endphp</p>
                                </div>
                            </th>
                            <th class="col-5 centerTable">
                                <div class="col-12 d-flex align-items-center">
                                    <p class="col-6 p-2 m-0">Fecha de Compra:</p>
                                    @if(session()->has("trueEditCompra"))
                                        <input class="form-control @if ($errors->any()) @if (in_array("fechaCompra-newCompra",$errors->keys())) invalid @endif @endif" type="datetime-local" id="fechaCompra-newCompra" name="fechaCompra-newCompra" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}" min="{{ date("Y-m-d H:i",now()->getTimestamp()-31557600) }}" value="{{ old("fechaCompra-newCompra") ? old("fechaCompra-newCompra") : data_get($compra,"fecha_compra") }}">
                                    @elseif (session()->has("compra"))
                                        <input class="form-control @if ($errors->any()) @if (in_array("fechaCompra-newCompra",$errors->keys())) invalid @endif @endif" type="datetime-local" id="fechaCompra-newCompra" name="fechaCompra-newCompra" max="{{ date("Y-m-d H:i",now()->getTimestamp()) }}" min="{{ date("Y-m-d H:i",now()->getTimestamp()-31557600) }}" value="{{ old("fechaCompra-newCompra") ? old("fechaCompra-newCompra") : now()->format("Y-m-d H:i") }}">
                                    @else
                                        <p class="col-6 p-0 m-0">--/--/---- --:--</p>
                                    @endif
                                </div>
                                @if(session()->has("trueEditCompra"))
                                    <div class="col-12 d-flex align-items-center justify-content-center">
                                        <div class="text-center invalid-feedback @error('fechaCompra-newCompra') is-invalid @enderror" id="invalid-fechaCompra-newCompra">
                                            @error('fechaCompra-newCompra')
                                                {{ str_replace("fecha compra-new compra","fecha de compra",$message) }}
                                            @enderror
                                        </div>
                                    </div>
                                @elseif(session()->has("compra"))
                                <div class="col-12 d-flex align-items-center justify-content-center">
                                    <div class="text-center invalid-feedback @error('fechaCompra-newCompra') is-invalid @enderror" id="invalid-fechaCompra-newCompra">
                                        @error('fechaCompra-newCompra')
                                            {{ str_replace("fecha compra-new compra","fecha de compra",$message) }}
                                        @enderror
                                    </div>
                                </div>
                                @endif
                            </th>
                            <th class="col-4">
                                <div class="col-12 d-flex align-items-center justify-between">
                                    <p class="col-4 py-2 ps-2 m-0">Precio final:</p>
                                    <p id="totalCompra" class="col-8 py-2 pe-2 m-0 text-end txtTh">$@php
                                        if(session()->has("trueEditCompra")){
                                            echo $fmt->format(data_get($compra,"monto_total"));
                                        }elseif(isset($totalCompra)){
                                            echo $fmt->format($totalCompra->__toString());
                                        }else{
                                            echo "0,0000";
                                        }
                                    @endphp</p>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th><p class="col-12 p-2 m-0">Proveedor</p></th>
                            <th class="centerTable"><p class="p-1 m-0 text-center">Recibos</p></th>
                            <th>
                                <div class="col-12 py-1">
                                    <div class="col-12 d-flex justify-content-evenly align-items-center">
                                        <label for="metodoPago-newCompra" class="p-0 ps-1 m-0 col-auto">Método de pago:</label>
                                        <select id="metodoPago-newCompra" name="metodoPago-newCompra" class="btn form-control @if ($errors->any()) @if (in_array("metodoPago-newCompra",$errors->keys())) invalid @endif @endif">
                                            <button type="button">
                                                <selectedcontent></selectedcontent>
                                            </button>
                                            <option value="Tarjeta" {{ old("metodoPago-newCompra") ? (old("metodoPago-newCompra") == "Tarjeta" ? "selected" : "" ) : (session()->has("trueEditCompra") ? (data_get($compra,"tipo_pago")=="Tarjeta" ? "selected" : "") : "selected") }}>Tarjeta</option>
                                            <option value="Efectivo" {{ old("metodoPago-newCompra") ? (old("metodoPago-newCompra") == "Efectivo" ? "selected" : "" ) : (session()->has("trueEditCompra") ? (data_get($compra,"tipo_pago")=="Efectivo" ? "selected" : "") : "") }}>Efectivo (¡Recibo opcional!)</option>
                                            <option value="Mixto" {{ old("metodoPago-newCompra") ? (old("metodoPago-newCompra") == "Mixto" ? "selected" : "" ) : (session()->has("trueEditCompra") ? (data_get($compra,"tipo_pago")=="Mixto" ? "selected" : "") : "") }}>Mixto</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-center invalid-feedback @error('metodoPago-newCompra') is-invalid @enderror" id="invalid-metodoPago-newCompra">
                                            @error('metodoPago-newCompra')
                                                {{ str_replace("metodo pago-new compra","metodo de pago",$message) }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td class="centerTable proveedorCompra">
                                <div class="col-12 d-flex flex-column align-items-top justify-content-start">
                                    @if(session()->has("trueEditCompra"))
                                        <a href="{{ isset($compra) ? route("proveedor.proveedor",$compra->proveedor) : route("proveedor.inicio") }}" id="proveedorCompra" class="text-reset text-decoration-none col-12 p-1 m-0 text-justify">{{ data_get($compra,"nombre") }}</a>
                                    @elseif (session()->has("compra"))
                                         <a href="{{ isset($proveedor) ? route("proveedor.proveedor",$proveedor->id) : route("proveedor.inicio") }}" id="proveedorCompra" class="text-reset text-decoration-none col-12 p-1 m-0 text-justify">{{ $proveedor ? ($proveedor->nombre ? $proveedor->nombre : "") : "" }}</a>
                                    @else
                                        <p class="col-12 p-1 m-0 text-justify"></p>
                                    @endif
                                </div>
                            </td>
                            <td class="centerTable">
                                @if(session()->has("trueEditCompra"))
                                <div id="div-fotos-newCompra" class="fotos-newCompra col-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="drop-area mt-2 d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-newCompra",$errors->keys())) invalid @endif @endif">
                                        <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                        <span>O</span>
                                        <button class="px-2 py-1 mt-2">Buscar imágenes</button>
                                        <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                        <select class="form-control" name="fotos-newCompra[]" id="fotos-newCompra" multiple hidden required>
                                            @php $oldFotosEdit=["fotos"=>old("fotos-newCompra"),"data-foto"=>[]];
                                                if(!empty($oldFotosEdit["fotos"])){
                                                    if(is_array($oldFotosEdit["fotos"])){
                                                        foreach($oldFotosEdit["fotos"] as $foto){
                                                            $idOldFoto=bin2hex(random_bytes(length: 3));
                                                            $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                        }
                                                    }else{
                                                        $idOldFoto=random_bytes(length: 7);
                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                    }
                                                }else{
                                                    $fotosLocal=data_get($compra,"recibos")->pluck("url_img")->toArray();
                                                    $fotosOnline=data_get($compra,"recibos")->pluck("url_img_online")->toArray();
                                                    if(sizeof($fotosLocal)!=sizeof($fotosOnline)){
                                                        $oldFotosEdit["fotos"]=[];
                                                        foreach ($oldFotosEdit["fotos"] as $foto) {
                                                            if(isset($foto)){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                                $oldFotosEdit["fotos"][]=$foto;
                                                            }
                                                        }
                                                    }else{
                                                        $oldFotosEdit["fotos"]=[];
                                                        for($i=0;$i<sizeof($fotosLocal);$i++) {
                                                            if(isset($fotosLocal[$i])){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$fotosLocal[$i].'" selected></option>';
                                                                $oldFotosEdit["fotos"][]=$fotosLocal[$i];
                                                            }elseif(isset($fotosOnline[$i])){
                                                                $idOldFoto=bin2hex(random_bytes(length: 3));
                                                                $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                                echo '<option data-foto="'.$idOldFoto.'" value="'.$fotosOnline[$i].'" selected></option>';
                                                                $oldFotosEdit["fotos"][]=$fotosOnline[$i];
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                        </select>
                                        <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-newCompra') is-invalid @enderror" id="invalid-fotos-newCompra">
                                            @error('fotos-newCompra')
                                                @if (str_contains($message,"obligatorio"))
                                                    {{ "Ingrese al menos un recibo de compra." }}
                                                @else
                                                    {{ str_replace("fotos-new compra","fotos",$message) }}
                                                @endif
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-11 previewFotos d-flex p-0 ps-2 my-3 @php if(!empty($oldFotosEdit["data-foto"]) && sizeof($oldFotosEdit["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newCompra">
                                        @for ($i=0;$i<sizeof($oldFotosEdit["data-foto"]);$i++)
                                            <img id="{{ $oldFotosEdit["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotosEdit["fotos"][$i] }}" alt="{{ $oldFotosEdit["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                        @endfor
                                    </div>
                                </div>
                                @elseif(session()->has("compra"))
                                <div id="div-fotos-newCompra" class="fotos-newCompra col-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="drop-area mt-2 d-flex align-items-center justify-content-center flex-column form-control @if ($errors->any()) @if (in_array("fotos-newCompra",$errors->keys())) invalid @endif @endif">
                                        <h2 class="form-label">Arrastra y suelta imágenes aquí</h2>
                                        <span>O</span>
                                        <button class="px-2 py-1 mt-2">Buscar imágenes</button>
                                        <input type="file" accept="image/png, image/jpeg, image/jpg" class="inputFotos" hidden multiple>
                                        <select class="form-control" name="fotos-newCompra[]" id="fotos-newCompra" multiple hidden required>
                                            @php $oldFotosEdit=["fotos"=>old("fotos-newCompra"),"data-foto"=>[]];
                                                if(!empty($oldFotosEdit["fotos"])){
                                                    if(is_array($oldFotosEdit["fotos"])){
                                                        foreach($oldFotosEdit["fotos"] as $foto){
                                                            $idOldFoto=bin2hex(random_bytes(length: 3));
                                                            $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                            echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                        }
                                                    }else{
                                                        $idOldFoto=random_bytes(length: 7);
                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                    }
                                                }elseif(isset($productoEditar)){
                                                    $oldFotosEdit["fotos"]=$productoEditar["fotos-newCompra"];
                                                    foreach ($oldFotosEdit["fotos"] as $foto) {
                                                        $idOldFoto=bin2hex(random_bytes(length: 3));
                                                        $oldFotosEdit["data-foto"][]=$idOldFoto;
                                                        echo '<option data-foto="'.$idOldFoto.'" value="'.$foto.'" selected></option>';
                                                    }
                                                }
                                            @endphp
                                        </select>
                                        <div class="text-center mt-3 fs-5 invalid-feedback @error('fotos-newCompra') is-invalid @enderror" id="invalid-fotos-newCompra">
                                            @error('fotos-newCompra')
                                                @if (str_contains($message,"obligatorio"))
                                                    {{ "Ingrese al menos un recibo de compra." }}
                                                @else
                                                    {{ str_replace("fotos-new compra","fotos",$message) }}
                                                @endif
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-11 previewFotos d-flex p-0 ps-2 my-3 @php if(!empty($oldFotosEdit["data-foto"]) && sizeof($oldFotosEdit["data-foto"])>3)echo "masTres"; @endphp" id="preview-fotos-newCompra">
                                        @for ($i=0;$i<sizeof($oldFotosEdit["data-foto"]);$i++)
                                            <img id="{{ $oldFotosEdit["data-foto"][$i] }}" class="img-fluid my-2 me-2" src="{{ $oldFotosEdit["fotos"][$i] }}" alt="{{ $oldFotosEdit["data-foto"][$i] }}" title="Eliminar" draggable="false">
                                        @endfor
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td>
                                @if(session()->has("trueEditCompra"))
                                    <div class="col-12 p-2 mb-2 d-flex align-items-center justify-content-center">
                                        <input type="submit" id="btnCancelarEditCompra" form="formCancelarEditCompra" value="Cancelar modificación" class="btn btn-dark">
                                    </div>
                                    <div class="col-12 p-2 mb-2 d-flex align-items-center justify-content-center">
                                        <input type="reset" id="btnResetEditCompra" form="formConfirmarCompra" data-compra="{{ $compra->id }}" value="Reiniciar" class="btn btn-dark">
                                    </div>
                                    <div class="col-12 p-2 d-flex align-items-center justify-content-center">
                                        <input type="submit" id="btnConfirmarCompra" form="formConfirmarCompra" value="Modificar Compra" class="btn btn-dark">
                                    </div>
                                @elseif (session()->has("compra"))
                                    <div class="col-12 p-2 mb-5 d-flex align-items-center justify-content-center">
                                        <a href="{{ route("proveedor.proveedor",$proveedor->id) }}" class="btn btn-dark" id="addMoreProds">Agregar más productos</a>
                                    </div>
                                    <div class="col-12 p-2 d-flex align-items-center justify-content-center">
                                        <input type="submit" id="btnConfirmarCompra" form="formConfirmarCompra" value="Registrar Compra" class="btn btn-dark">
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </thead>
                </table>
            </form>
        </div>
    </div>
    <form action="{{ route("compra.descartareditcompra") }}" id="formCancelarEditCompra" method="post" class="col-12 p-2 mb-2 d-flex align-items-center justify-content-center d-none">
        @csrf
    </form>
@endsection

@section('footer')
@vite([
    "resources/js/app/dropImgs.js",
    "resources/js/app/compra.js",
    "resources/js/app/procesarCompra.js",
])
<script>
    (()=>{
        function deleteFoto(foto){
            const optFoto = foto.parentElement.previousElementSibling.querySelector("option[data-foto='"+foto.id+"']");
            const select = foto.parentElement;
            optFoto.remove();
            foto.remove();
            if(select.childElementCount<4){
                select.classList.remove("masTres");
            }
        }

        window.addEventListener("load",()=>{
            const oldFotosEdit=@php if(isset($oldFotosEdit)){
                                        $jsonFotos=["fotos"=>[],"data-foto"=>[]];
                                        for($i=0;$i<sizeof($oldFotosEdit["data-foto"]);$i++){
                                            $jsonFotos["fotos"][]=$oldFotosEdit["fotos"][$i];
                                            $jsonFotos["data-foto"][]=$oldFotosEdit["data-foto"][$i];
                                        }
                                        echo json_encode($jsonFotos);
                                    }
                                    else{
                                        echo json_encode(["fotos"=>[]]);
                                    }
                                @endphp;
            if(oldFotosEdit!=null && oldFotosEdit["fotos"].length>0){
                const prevFotosEdit = document.querySelector("#div-fotos-newCompra .previewFotos");
                if(prevFotosEdit!=null){
                    const fotosEditCompra=prevFotosEdit.children;
                    for(const fotoEditCompra of fotosEditCompra){
                        fotoEditCompra.addEventListener("click",(e)=>deleteFoto(fotoEditCompra));
                    };
                }
            }
            const limpiarEditProd = document.querySelector("#btnResetEditCompra");
            if(limpiarEditProd!=null){
                limpiarEditProd.addEventListener("click",()=>{
                    if(oldFotosEdit!=null){
                        const prevFotos = limpiarEditProd.closest("form").querySelector(".fotos-newCompra .previewFotos");
                        const selectFotos = limpiarEditProd.closest("form").querySelector("#fotos-newCompra");
                        while(prevFotos.childElementCount>0){
                            prevFotos.lastElementChild.click();
                        }
                        for(let i=0;i<oldFotosEdit["fotos"].length;i++){
                            let img=new Image();
                            img.id=oldFotosEdit["data-foto"][i];
                            img.src=oldFotosEdit["fotos"][i];
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
</script>
@endsection

