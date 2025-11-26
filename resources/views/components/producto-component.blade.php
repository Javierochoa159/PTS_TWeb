@php
    use Brick\Math\BigDecimal;
    use Brick\Math\RoundingMode;
    $fmt = new \NumberFormatter('es_AR', \NumberFormatter::DECIMAL);
    $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
@endphp

<div class="producto mb-4">
    <form action="{{ route("producto.addtocart") }}" method="post" name="formAddToCard" novalidate>
        @csrf
        <input type="hidden" name="id" value="{{ data_get($producto,"id") }}">
        <a class="cabeceraProducto p-1 d-flex @if (data_get($producto,"cantidad_disponible")<=data_get($producto,"cantidad_minima"))position-relative @endif align-items-center justify-content-center text-reset text-decoration-none" href="{{ route("producto.producto",data_get($producto,"id")) }}">
            <img class="img-fluid" src="@php
                $foto=data_get(data_get($producto,"foto"),"url_img");
                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                    echo route("inicio.index"),$foto;
                }else{
                    echo data_get(data_get($producto,"foto"),"url_img_online");
                }
            $cantDisp=data_get($producto,"cantidad_disponible");
            $cantMinima=data_get($producto,"cantidad_minima");
            @endphp" alt="producto" draggable="false">
            <x-alert-stock-component :cantidad="$cantDisp" :minimo="$cantMinima"/>
        </a>
        <div class="cuerpoProducto mt-1 mb-2">
            <p class="tituloProducto p-0 m-0 px-2 mb-1">{{ data_get($producto,"nombre") }}</p>
            <p class="p-0 m-0 text-end pe-2">@php
                if(data_get($producto,"codigo")==null) echo "------------";
                else echo data_get($producto,"codigo");
            @endphp</p>
            <p class="p-0 m-0 ps-2">{{ "$".$fmt->format(BigDecimal::of(data_get($producto,"precio_venta"))->toScale(4, RoundingMode::HALF_UP)->__toString())." por ".data_get($producto,"tipo_medida") }}</p>
        </div>
        <div class="pieProducto mb-2">
            @if (data_get($producto,"cantidad_disponible")>0)
                <div class="cantidadesProducto mt-1 d-flex align-items-end justify-content-between">
                    <div class="medidaProducto col-7 d-flex align-items-center justify-content-start">
                        <div class="col-6 ms-2 d-flex">
                            <input class="col-12 px-2" type="number" name="cantidad" min="@if(strcmp(data_get($producto,"tipo_medida"),'Unidad')==0)
                                1
                            @else
                                0.01
                            @endif" max="{{ data_get($producto,"cantidad_disponible") }}" name="cantidad_producto-{{ data_get($producto,"id") }}" step="1" value="1">
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
                            <p class="p-0 m-0">Agregar</p>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>