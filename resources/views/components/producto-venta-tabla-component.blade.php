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
    @if (strcmp($tipo,"devolucion")==0)
        <td class="col-auto">
            <div class="d-flex align-items-center justify-content-center">
                <input type="checkbox" name="producto-devVenta[]" value="{{ $producto->productoRelacion->id }}" id="producto_{{ $producto->productoRelacion->id }}" {{ old('producto-devVenta') ? (is_array(old('producto-devVenta')) ? ((in_array($producto->productoRelacion->id,old('producto-devVenta'))) ? "checked" : "") : "") : ($producto->productoRelacion->id==old('producto-devVenta') ? "checked" : "") }}>
            </div>
        </td>
    @endif
    <td>
        @if(strcmp($tipo,"devolucion")==0)<div class="col-12 d-flex flex-column align-items-center justify-content-center position-relative firstDivDevVent"> @endif
        <div class="producto col-12 p-1 d-flex align-items-start">
            <div class="imgProd d-flex align-items-center">
                @switch($tipo)
                    @case("devolucion")
                        <div class="d-flex align-items-center justify-content-center"><img class="img-fluid" src="@php
                                $foto=$producto->productoRelacion->foto->url_img;
                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                    echo route("inicio.index").$foto;
                                }else{
                                    echo $producto->productoRelacion->foto->url_img_online;
                                }
                            @endphp" alt="producto{{ $producto->productoRelacion->id }}" draggable="false"></div>
                    @break
                    @case("vender")
                        <a href="{{ route("producto.producto",data_get($producto,"id")) }}" title="abrir" class="p-0 m-0 text-reset text-decoration-none d-flex align-items-center justify-content-center"><img class="img-fluid" src="@php
                            $foto=data_get(data_get($producto,"foto"),"url_img");
                            if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                echo route("inicio.index").$foto;
                            }else{
                                echo data_get(data_get($producto,"foto"),"url_img_online");
                            }
                        @endphp" alt="producto{{ data_get($producto,"id") }}" draggable="false"></a>
                    @break
                    @case("vendido")
                        <a href="{{ route("producto.producto",$producto->productoRelacion->id) }}" title="abrir" class="p-0 m-0 text-reset text-decoration-none d-flex align-items-center justify-content-center"><img class="img-fluid" src="@php
                                $foto=$producto->productoRelacion->foto->url_img;
                                if($foto!=null && Storage::disk('public')->exists(str_replace("/storage","",$foto))){
                                    echo route("inicio.index").$foto;
                                }else{
                                    echo $producto->productoRelacion->foto->url_img_online;
                                }
                            @endphp" alt="producto{{ $producto->productoRelacion->id }}" draggable="false"></a>
                    @break
                @endswitch
            </div>
            <div class="col-9 nomProd">
                @switch($tipo)
                    @case("vender")
                        <p class="ps-1 m-0">{{ data_get($producto,"nombre") }}</p>
                    @break
                    @case("vendido")
                        <p class="ps-1 m-0">{{ $producto->productoRelacion->nombre }}</p>
                    @break 
                    @case("devolucion")
                        <p class="ps-1 m-0">{{ $producto->productoRelacion->nombre }}</p>
                    @break 
                @endswitch
            </div>
        </div>
        @if(strcmp($tipo,"devolucion")==0)
            <div class="col-12 d-none flex-column align-items-center justify-content-center position-absolute top-0 h-100 p-2 devVentDiv">
                <div class="col-12 d-flex flex-column align-items-center justify-content-start infDevProd">
                    @php
                        $venta=session("devVenta");
                    @endphp
                    <textarea name="motivoDevProd-{{$producto->productoRelacion->id}}-devVenta" id="motivoDevProd-{{$producto->productoRelacion->id}}-devVenta" placeholder="Motivo de la devolución." class="form-control">{{ old('motivoDevProd-'.$producto->productoRelacion->id.'-devVenta') ? old('motivoDevProd-'.$producto->productoRelacion->id.'-devVenta') : (strcmp($venta->tipo_pago,"Pendiente")==0 ? "Producto devuelto antes de pagar." : "") }}</textarea>
                    <div class="text-center invalid-feedback @error('motivoDevProd-'.$producto->productoRelacion->id.'-devVenta') is-invalid @enderror" id="invalid-motivoDevProd-{{ $producto->productoRelacion->id }}-devVenta">
                        @error('motivoDevProd-'.$producto->productoRelacion->id.'-devVenta')
                            {{ str_replace("motivo dev prod-".$producto->productoRelacion->id."-dev venta","motivo",$message) }}
                        @enderror
                    </div>
                </div>
            </div>
            </div>
        @endif
    </td>
    <td>
        @if(strcmp($tipo,"devolucion")==0)<div class="col-12 d-flex flex-column align-items-center justify-content-center position-relative firstDivDevVent"> @endif
        <div class="col-12">
            @switch($tipo)
                @case("vender")
                    <p name="producto_{{ data_get($producto,"id") }}" class="p-0 m-0 px-1 text-center">
                    {{ "$".data_get($producto,"precio_venta_Print") }}
                @break
                @case("vendido")
                    <p name="producto_{{ $producto->productoRelacion->id }}" class="p-0 m-0 px-1 text-center">
                    {{ "$".$fmt2->format($producto->precio_venta) }}
                @break
                @case("devolucion")
                    <p name="producto_{{ $producto->productoRelacion->id }}" class="p-0 m-0 px-1 text-center precioVDevProd">
                    {{ "$".$fmt2->format($producto->precio_venta) }}
                @break
            @endswitch
            </p>
        </div>
        @if(strcmp($tipo,"devolucion")==0)
            <div class="col-12 d-none flex-column align-items-center justify-content-center position-absolute top-0 h-100 p-2 devVentDiv">
                <div class="col-12 d-flex flex-column align-items-center justify-content-center infDevProd">
                    @php
                        $venta=session("devVenta");
                    @endphp
                    @if(strcmp($venta->tipo_pago,"Pendiente")!=0)
                            <label for="tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta">Tipo devolución</label>
                            <select name="tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta" id="tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta" class="form-control">
                                <button type="button">
                                    <selectedcontent></selectedcontent>
                                </button>
                                <option value="Cambio" {{ old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") ? (old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") == "Cambio" ? "selected" : "" ) : "selected"  }}>Cambio</option>
                                <option value="Fallado" {{ old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") ? (old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") == "Fallado" ? "selected" : "" ) : "" }}>Prod. fallado</option>
                                <option value="Devolucion" {{ old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") ? (old("tipoDevProd-".$producto->productoRelacion->id."-devVenta") == "Devolucion" ? "selected" : "" ) : "" }}>Devolución</option>
                            </select>
                    @else
                            <p class="m-0 p-0">Tipo devolución:</p>
                            <input type="hidden" name="tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta" id="tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta" value="Devolucion">
                            <p class="m-0 py-1 px-3 devProdFijo">Devolución</p>
                    @endif
                    <div class="text-center invalid-feedback @error('tipoDevProd-'.$producto->productoRelacion->id.'-devVenta') is-invalid @enderror" id="invalid-tipoDevProd-{{ $producto->productoRelacion->id }}-devVenta">
                        @error('tipoDevProd-'.$producto->productoRelacion->id.'-devVenta')
                            {{ str_replace("tipo dev prod-".$producto->productoRelacion->id."-dev venta","tipo",$message) }}
                        @enderror
                    </div>
                </div>
            </div>
            </div>
        @endif
    </td>
    <td>
        @if(strcmp($tipo,"devolucion")==0)<div class="col-12 d-flex flex-column align-items-center justify-content-center position-relative firstDivDevVent"> @endif
        <div class="col-12">
            @switch($tipo)
                @case("vender")
                    <p class="p-1 m-0 text-center">{{ data_get($producto,"totalC_Print") }}</p>
                @break
                @case("vendido")
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
                @case("devolucion")
                    <p class="p-1 m-0 text-center cantProd">@php
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
        @if(strcmp($tipo,"devolucion")==0)
            <div class="col-12 d-none flex-column align-items-center justify-content-center position-absolute top-0 h-100 p-2 devVentDiv">
                <div class="col-12 d-flex flex-column align-items-center justify-content-center infDevProd">
                    <label for="cantDevProd-{{ $producto->productoRelacion->id }}-devVenta">Cantidad devuelta</label>
                    <input type="number" name="cantDevProd-{{ $producto->productoRelacion->id }}-devVenta" id="cantDevProd-{{ $producto->productoRelacion->id }}-devVenta" @php
                        switch($producto->productoRelacion->tipo_medida){
                            case "Unidad": echo "min='1'"; break;
                            default: echo "min='0.01'";
                        }
                    @endphp max="{{ $producto->cantidad }}" value="@php
                        $oldCant=old("cantDevProd-".$producto->productoRelacion->id."-devVenta");
                        if(isset($oldCant)){
                            switch($producto->productoRelacion->tipo_medida){
                                case "Unidad":  if($oldCant<1){
                                                    echo "1";
                                                }elseif($oldCant>$producto->cantidad){
                                                    echo $producto->cantidad;
                                                }else{
                                                    echo $oldCant;
                                                }
                                break;
                                default:    if($oldCant<0.01){
                                                echo "0.01";
                                            }elseif($oldCant>$producto->cantidad){
                                                echo $producto->cantidad;
                                            }else{
                                                echo $oldCant;
                                            }
                            }
                        }else{
                            echo $producto->cantidad;
                        }
                    @endphp" class="form-control cantDevProd">
                    <div class="text-center invalid-feedback @error('cantDevProd-'.$producto->productoRelacion->id.'-devVenta') is-invalid @enderror" id="invalid-cantDevProd-{{ $producto->productoRelacion->id }}-devVenta">
                        @error('cantDevProd-'.$producto->productoRelacion->id.'-devVenta')
                            {{ str_replace("cant dev prod-".$producto->productoRelacion->id."-dev venta","cantidad",$message) }}
                        @enderror
                    </div>
                </div>
            </div>
            </div>
        @endif
    </td>
    <td>
        <div class="col-12">
            <p class="p-0 m-0 ps-1 text-center medidaProd">
                @switch($tipo)
                    @case("vender")
                        @switch(data_get($producto,"tipo_medida"))
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
                    @case("vendido")
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
                    @case("devolucion")
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
        @if(strcmp($tipo,"devolucion")==0)<div class="col-12 d-flex flex-column align-items-center justify-content-center position-relative firstDivDevVent"> @endif
        <div class="col-12">
            <p class="p-0 m-0 pe-2 text-end totalProd">
                @switch($tipo)
                    @case("vender")
                            {{ "$".data_get($producto,"totalV_Print") }}
                    @break
                    @case("vendido")
                        @php
                            $totalProd=BigDecimal::of($producto->total_producto)->toScale(4, RoundingMode::HALF_UP);
                        @endphp
                            {{ "$".$fmt4->format($totalProd->__toString()) }}
                    @break
                    @case("devolucion")
                        @php
                            $totalProd=BigDecimal::of($producto->total_producto)->toScale(4, RoundingMode::HALF_UP);
                        @endphp
                            {{ "$".$fmt4->format($totalProd->__toString()) }}
                    @break
                @endswitch
            </p>
        </div>
        @if(strcmp($tipo,"devolucion")==0)
            <div class="col-12 d-none align-items-center justify-content-center position-absolute top-0 h-100 p-2 devVentDiv">
                <div class="col-12 d-flex flex-column align-items-end justify-content-center infDevProd">
                    <p class="p-0 m-0 pe-1 text-end totalDevProd">${{ $fmt4->format($producto->total_producto) }}</p>
                </div>
            </div>
            </div>
        @endif
    </td>
</tr>