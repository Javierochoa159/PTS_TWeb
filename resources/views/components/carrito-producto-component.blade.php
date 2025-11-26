@php
    use Brick\Math\RoundingMode;
@endphp

@props(["producto"])
<div id="producto_{{ data_get($producto,"id") }}" class="producto mb-2 mx-2 p-2 d-flex align-items-start justify-content-between">
    <div class="imgProducto">
        <img class="img-fluid" src="@php
            $foto=data_get(data_get($producto,"foto"),"url_img");
            if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                echo route("inicio.index").$foto;
            }else{
                echo data_get(data_get($producto,"foto"),"url_img_online");
            }
        @endphp" alt="producto" draggable="false">
    </div>
    <div class="textProducto d-flex flex-column p-1">
        <p class="carrito-nomProducto m-0 p-0 pb-1">{{ data_get($producto,"nombre") }}</p>
        <div class="d-flex align-items-center justify-content-between">
            <p class="m-0 p-0 precioV">{{"$".data_get($producto,"precio_venta_Print")}}@switch(data_get($producto,'tipo_medida'))@case("Unidad") c/u @break @case("Kilogramo") el Kg @break @case("Metro") el Mt @break @case("Litro") el Lt @break @endswitch</p>
            <p class="m-0 p-0 precioVT">{{ "Total: $".data_get($producto,"totalV_Print") }}</p>
            <a href="{{ route('producto.producto', data_get($producto,"id")) }}" class="m-0 p-0">Ver mas</a>
        </div>
    </div>
    <div class="cantidadesProducto mt-1 d-flex flex-column align-items-end justify-content-start col-2">
        <div class="medidaProducto d-flex flex-column align-items-center justify-content-center col-12">
            <div class="d-flex align-items-center col-12">
                <button name="refreshInput" type="button" class="col-6 minus d-flex align-items-center justify-content-center" aria-label="Menos" data-refbtn="minus">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/minus.svg") }}" alt="Menos" draggable="false">
                </button>
                <button name="refreshInput" type="button" class="col-6 plus d-flex align-items-center justify-content-center" aria-label="Más" data-refbtn="plus">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/plus.svg") }}" alt="Más" draggable="false">
                </button>
            </div>

            <div class="col-12 text-center cantProd">
                <input class="col-11" data-prod="{{ data_get($producto,"id") }}" data-medida="{{ data_get($producto,'tipo_medida') }}" type="number" step="1" title=""
                    @switch(data_get($producto,'tipo_medida'))@case('Unidad') min="1"@break @default min="0.0001"@endswitch
                    name="cantidad_producto-{{ data_get($producto,'id') }}" max="{{data_get($producto,"cantidad_disponible")}}" value="@php
                        $cant=data_get($producto,"totalC");
                        $aux=$cant->toScale(0, RoundingMode::DOWN);
                        if($aux->isLessThan($cant)){
                            echo $cant->__toString();
                        }else{
                            echo $aux->__toString();
                        }
                    @endphp">
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-end mt-2 col-12">
            <div class="carrito-eliminarProducto col-12 d-flex align-items-center justify-content-evenly">
                <div class="d-flex align-items-center ps-1 col-auto">
                    <p class="p-0 m-0 col-12 text-center">@switch(data_get($producto,'tipo_medida'))@case("Unidad") Us @break @case("Kilogramo") Kgs @break @case("Metro") Mts @break @case("Litro") Lts @break @endswitch</p>
                </div>
                <button type="button" title="Eliminar" name="deleteOfCart" data-prod="{{ data_get($producto,"id") }}" class="col-auto  d-flex align-items-center justify-content-center" aria-label="Eliminar">
                    <img class="img-fluid" src="{{ asset("build/assets/icons/delete.svg") }}" alt="Eliminar" draggable="false">
                </button>
            </div>
        </div>
    </div>
</div>