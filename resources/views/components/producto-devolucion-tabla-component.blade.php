@props(["producto","devoluciones","idsDev"])
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
    

<tr class="productoTr devProd">
    <td>
        <div class="producto col-12 p-1 d-flex align-items-start">
            <div class="imgProd d-flex align-items-center">
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
            </div>
            <div class="col-9 nomProd">
                <p class="ps-1 m-0">{{ $producto->productoRelacion->nombre }}</p>
            </div>
        </div>
    </td>
    <td>
        <div class="col-12">
            <p name="producto_{{ $producto->productoRelacion->id }}" class="p-0 m-0 px-1 text-center">{{ "$".$fmt2->format($producto->precio_venta) }}</p>
        </div>
    </td>
    <td>
        <div class="col-12">
            <p class="p-1 m-0 text-center">@php
                $cantProd=BigDecimal::of($producto->cantidad)->toScale(4, RoundingMode::HALF_UP);
                $aux=$cantProd->toScale(0, RoundingMode::DOWN);
                if($aux->isLessThan($cantProd)){
                    echo $fmt4->format($cantProd->__toString());
                }else{
                    echo $fmt0->format($cantProd->__toString());
                }@endphp
            </p>
        </div>
    </td>
    <td>
        <div class="col-12">
            <p class="p-0 m-0 ps-1 text-center medidaProd">
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
            </p>
        </div>
    </td>
    <td>
        <div class="col-12">
            <p class="p-0 m-0 pe-2 text-end totalProd">
                @php
                    $totalProd=BigDecimal::of($producto->total_producto)->toScale(4, RoundingMode::HALF_UP);
                @endphp
                {{ "$".$fmt4->format($totalProd->__toString()) }}
            </p>
        </div>
    </td>
</tr>