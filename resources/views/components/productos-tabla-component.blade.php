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

<tr class="productoTr">
    <td>
        <div class="producto col-12 p-1 d-flex align-items-start">
            <div class="imgProd d-flex align-items-center">
                @switch($tipo)
                    @case("comprar")
                        <div class="d-flex align-items-center justify-content-center">
                            <img class="img-fluid" src="@php
                                $foto=$producto['producto-newCompra']->foto->url_img;
                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                    echo route("inicio.index").$foto;
                                }else{
                                    echo $producto['producto-newCompra']->foto->url_img_online;
                                }
                            @endphp" alt="producto{{ $producto['producto-newCompra']->id }}" draggable="false">
                        </div>
                    @break
                    @case("comprado")
                        <a href="{{ route("producto.producto",$producto->productoRelacion->id) }}" title="abrir" class="p-0 m-0 text-reset text-decoration-none d-flex align-items-center justify-content-center">
                            <img class="img-fluid" src="@php
                                $foto=$producto->productoRelacion->foto->url_img;
                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                    echo route("inicio.index").$foto;
                                }else{
                                    echo $producto->productoRelacion->foto->url_img_online;
                                }
                            @endphp" alt="producto{{ $producto->productoRelacion->id }}" draggable="false">
                        </a>
                    @break
                    @case("editCompra")
                        <div class="d-flex align-items-center justify-content-center">
                            <img class="img-fluid" src="@php
                                $foto=$producto->productoRelacion->foto->url_img;
                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                    echo route("inicio.index").$foto;
                                }else{
                                    echo $producto->productoRelacion->foto->url_img_online;
                                }
                            @endphp" alt="producto{{ $producto->productoRelacion->id }}" draggable="false">
                        </div>
                    @break
                @endswitch
            </div>
            <div class="col-{{ strcmp($tipo,"comprar")==0 ? "8" : "9" }} nomProd">
                @switch($tipo)
                    @case("comprar")
                        <p class="ps-1 m-0">{{ $producto["producto-newCompra"]->nombre }}</p>
                    @break
                    @case("comprado")
                        <p class="ps-1 m-0">{{ $producto->productoRelacion->nombre }}</p>
                    @break        
                    @case("editCompra")
                        <p class="ps-1 m-0">{{ $producto->productoRelacion->nombre }}</p>
                    @break        
                @endswitch
            </div>
            @if(strcmp($tipo,"editCompra")==0)
            <div class="col-1 btnsProd d-flex flex-column align-items-center justify-content-top">
                <button type="button" id="btn-editProducto_{{ $producto->producto }}" data-prod="{{ $producto->producto }}" class="editProducto mt-2">
                    <div class="d-flex align-items-center">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/edit.svg") }}" title="Editar" alt="editar producto" draggable="false">
                    </div>
                </button>
            </div>
            @elseif(strcmp($tipo,"comprar")==0)
            <div class="col-1 btnsProd d-flex flex-column align-items-center justify-content-top">
                <button type="button" id="btn-editProducto_{{ $producto["producto-newCompra"]->id }}" data-prod="{{ $producto["producto-newCompra"]->id }}" class="editProducto mt-2">
                    <div class="d-flex align-items-center">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/edit.svg") }}" title="Editar" alt="editar producto" draggable="false">
                    </div>
                </button>
                <button type="button" id="btn-deleteProducto_{{ $producto["producto-newCompra"]->id }}" data-prod="{{ $producto["producto-newCompra"]->id }}" class="deleteProducto mt-2">
                    <div class="d-flex align-items-center">
                        <img class="img-fluid" src="{{ asset("build/assets/icons/delete.svg") }}" title="Eliminar" alt="eliminar producto" draggable="false">
                    </div>
                </button>
            </div>
            @endif
        </div>
    </td>
    <td>
        <div class="col-12">
            @switch($tipo)
                @case("comprar")
                    <p name="producto_{{ $producto["producto-newCompra"]->id }}" class="p-0 m-0 px-1 text-center">
                    {{ "$".$fmt2->format($producto["precioCompra-newCompra"]->__toString()) }}
                @break
                @case("comprado")
                    <p name="producto_{{ $producto->productoRelacion->id }}" class="p-0 m-0 px-1 text-center">
                    {{ "$".$fmt2->format($producto->precio_compra) }}
                @break
                @case("editCompra")
                    <p name="producto_{{ $producto->productoRelacion->id }}" class="p-0 m-0 px-1 text-center">
                    {{ "$".$fmt2->format($producto->precio_compra) }}
                @break
            @endswitch
            </p>
        </div>
    </td>
    <td>
        <div class="col-12">
            @switch($tipo)
                @case("comprar")
                    <p class="p-1 m-0 text-center">@php
                        $aux=$producto["cantidad-newCompra"]->toScale(0, RoundingMode::DOWN);
                        if($aux->isLessThan($producto["cantidad-newCompra"])){
                            echo $fmt4->format($producto["cantidad-newCompra"]->__toString());
                        }else{
                            echo $fmt0->format($producto["cantidad-newCompra"]->__toString());
                        }@endphp
                    </p>
                @break
                @case("comprado")
                    <p class="p-1 m-0 text-center">@php
                        $cantProd=BigDecimal::of($producto->cantidad)->toScale(4, RoundingMode::HALF_UP);
                        $aux=$cantProd->toScale(0, RoundingMode::DOWN);
                        if($aux->isLessThan($cantProd)){
                            echo $fmt4->format($cantProd->__toString());
                        }else{
                            echo $fmt0->format($cantProd->__toString());
                        }@endphp
                    </p>
                @break
                @case("editCompra")
                    <p class="p-1 m-0 text-center">@php
                        $cantProd=BigDecimal::of($producto->cantidad)->toScale(4, RoundingMode::HALF_UP);
                        $aux=$cantProd->toScale(0, RoundingMode::DOWN);
                        if($aux->isLessThan($cantProd)){
                            echo $fmt4->format($cantProd->__toString());
                        }else{
                            echo $fmt0->format($cantProd->__toString());
                        }@endphp
                    </p>
                @break
            @endswitch
        </div>
    </td>
    <td>
        <div class="col-12">
            <p class="p-0 m-0 ps-1 text-center medidaProd">
                @switch($tipo)
                    @case("comprar")
                        @switch($producto["producto-newCompra"]->tipo_medida)
                            @case("Unidad")
                                            Unidad
                                            @break
                            @case("Kilogramo")
                                            Kilo
                                            @break
                            @case("Metro")
                                            Metro
                                            @break
                            @case("Litro")
                                            Litro
                                            @break
                        @endswitch
                    @break
                    @case("comprado")
                        @switch($producto->productoRelacion->tipo_medida)
                            @case("Unidad")
                                            Unidad
                            @break
                            @case("Kilogramo")
                                            Kilo
                            @break
                            @case("Metro")
                                            Metro
                            @break
                            @case("Litro")
                                            Litro
                            @break
                        @endswitch
                    @break
                    @case("editCompra")
                        @switch($producto->productoRelacion->tipo_medida)
                            @case("Unidad")
                                            Unidad
                            @break
                            @case("Kilogramo")
                                            Kilo
                            @break
                            @case("Metro")
                                            Metro
                            @break
                            @case("Litro")
                                            Litro
                            @break
                        @endswitch
                    @break
                @endswitch
            </p>
        </div>
    </td>
    <td>
        <div class="col-12">
            <p class="p-0 m-0 pe-2 text-end totalProd">
                @switch($tipo)
                    @case("comprar")
                        {{ "$".$fmt4->format($producto["total-newCompra"]->__toString()) }}
                    @break
                    @case("comprado")
                        @php
                            $totalProd=BigDecimal::of($producto->total_producto)->toScale(4, RoundingMode::HALF_UP);
                        @endphp
                            {{ "$".$fmt4->format($totalProd->__toString()) }}
                    @break
                    @case("editCompra")
                        @php
                            $totalProd=BigDecimal::of($producto->total_producto)->toScale(4, RoundingMode::HALF_UP);
                        @endphp
                            {{ "$".$fmt4->format($totalProd->__toString()) }}
                    @break
                @endswitch
            </p>
        </div>
    </td>
</tr>