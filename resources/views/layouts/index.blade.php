<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    {{ header("Cache-Control: no-cache, no-store, must-revalidate") }}
    {{ header("Expires: 0") }}
    @vite("resources/js/app.js")
    @vite([
      "resources/css/app.css",
      "resources/css/myStyles/index.css"
      ])
    <link rel="stylesheet" href="{{ asset("vendor/bootstrap/css/bootstrap.min.css") }}">
    @yield('head')
</head>
<body>
  <header class="cabecera col-12 d-flex align-items-center justify-content-between">
    <a class="col-auto d-flex ps-2 py-1 align-items-center text-decoration-none text-reset">
        <img class="img-fluid" src="{{ asset("build/assets/icons/ElFaro.svg") }}" alt="ElFaro_logo" draggable="false">
        <h4 class="ps-1">Inventario</h4>
    </a>
    @if (!session()->has("adminSet"))
      <button class="col-auto m-2 p-1 userHead" id="btnModalAdmin" title="Iniciar como administrados" data-bs-toggle="modal" data-bs-target="#adminModal">
        <img class="img-fluid" src="{{ asset("build/assets/icons/user.svg") }}" alt="admin" draggable="false">
      </button>
    @else
      <div class="col-auto m-2 p-1 d-flex align-items-center userHead" title="Cerrar sesión">
        <button class="p-0 m-0" type="submit" form="finAdmin"><img class="img-fluid" src="{{ asset("build/assets/icons/exit.svg") }}" alt="close" draggable="false"></button>
      </div>
      <form action="{{ route("inicio.finadmin") }}" id="finAdmin" method="post" class="d-none">
        @csrf
      </form>
    @endif
  </header>
  <div class="col-12 primerDiv d-flex">
    <aside class="menu col-2">
      <div class="menu-titulo">
        <h4>Navegación</h4>
      </div>
      <div class="menu-opciones mt-3 p-3 pt-2">
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"inicio")==0)echo "selected";
        @endphp">
          <form action="{{ route("inicio.index") }}" method="post" class="col-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/menu.svg") }}" alt="Inicio" draggable="false">
              <p class="p-0 m-0 ps-2">Inicio</p>
            </button>
          </form>
        </div>
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"productos")==0)echo "selected";
        @endphp">
          <form action="{{ route("producto.inicio") }}" method="post" class="col-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/stock.svg") }}" alt="InventarioDisponible" draggable="false">
              <p class="p-0 m-0 ps-2">Inventario Disponible</p>
            </button>
          </form>
        </div>
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"productos-todos")==0)echo "selected";
        @endphp">
          <form action="{{ route("producto.todosinicio") }}" method="post" class="col-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/all_stock.svg") }}" alt="InventarioTotal" draggable="false">
              <p class="p-0 m-0 ps-2">Inventario Total</p>
            </button>
          </form>
        </div>
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"ventas")==0)echo "selected";
        @endphp">
          <form action="{{ route("venta.inicio") }}" method="post" class="col-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/sales.svg") }}" alt="Ventas" draggable="false">
              <p class="p-0 m-0 ps-2">Ventas</p>
            </button>
          </form>
        </div>
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"compras")==0)echo "selected";
        @endphp">
          <form action="{{ route("compra.inicio") }}" method="post" class="sol-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/shopping.svg") }}" alt="Compras" draggable="false">
              <p class="p-0 m-0 ps-2">Compras</p>
            </button>
          </form>
          <a href="" class="">
          </a>
        </div>
        <div class="px-1 mb-2 @php
            if(strcmp(session()->get("pagina"),"proveedores")==0)echo "selected";
        @endphp">
          <form action="{{ route("proveedor.inicio") }}" method="post" class="col-12">
            @csrf
            <button type="submit" class="col-12 d-flex align-items-center justify-content-start text-reset text-decoration-none">
              <img class="img-fluid" src="{{ asset("build/assets/icons/supplier.svg") }}" alt="Proveedores" draggable="false">
              <p class="p-0 m-0 ps-2">Proveedores</p>
            </button>
          </form>
        </div>
      </div>
      @yield('aside')
    </aside>
    <button id="btn-carrito" class="carrito" data-bs-toggle="offcanvas" data-bs-target="#carritoModal" title="Carrito de Ventas">
      <span class="p-0 m-0">@php
              if(isset(session("carrito")["productos"])){
                 echo sizeof(session("carrito")["productos"]);
              }else{
                echo "0";
              }
          @endphp</span>
      <img class="img-fluid" src="{{ asset("build/assets/icons/cart.svg") }}" alt="Carrito" draggable="false">
    </button>

    @if(session()->has("compra"))
      <a href="{{ route("compra.procesarcompra") }}" id="btn-carritoCompra" class="carritoCompra" title="Carrito de Compras">
        <span class="p-0 m-0">@php
                if(isset(session("compra")["productos"])){
                  echo sizeof(session("compra")["productos"]);
                }else{
                  echo "0";
                }
            @endphp</span>
        <img class="img-fluid" src="{{ asset("build/assets/icons/cart.svg") }}" alt="Carrito" draggable="false">
      </a>
    @endif

    <section class="col-10 contenidoPagina">
      @yield("content")
    </section>
    <div id="loading-screen" style="display:none;">
      <div class="loader"></div>
    </div>
  </div>
  <footer class="col-12">

  </footer>
  <div id="carritoModal" class="offcanvas offcanvas-end col-5 p-0 my-0" tabindex="-1">
      <div class="carritoHeader offcanvas-header d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center p-2 ps-3">
          <img class="img-fluid pe-2" src="{{ asset("build/assets/icons/cart_white.svg") }}" alt="Carrito" draggable="false">
          <p class="m-0 p-0 offcanvas-title">Carrito de Venta @php
              if(isset(session("carrito")["productos"])){
                 echo "(".sizeof(session("carrito")["productos"]).")";
              }else{
                echo "(0)";
              }
          @endphp</p>
        </div>
        <div id="btnRefreshCart" class="p-2 btn d-none">
          <p class="p-0 m-0 fs-6">Guardar cambios</p>
        </div>
        <div id="closeCarrito" class="d-flex align-items-center me-3 p-1" data-bs-dismiss="offcanvas">
          <img class="img-fluid" src="{{ asset("build/assets/icons/close_white.svg") }}" alt="Cerrar" draggable="false">
        </div>
      </div>
      <div class="carritoBody pt-2">
        @if (isset(session("carrito")["productos"]))
            @foreach (session("carrito")["productos"] as $producto)
            <x-carrito-producto-component :producto="$producto"/>                
            @endforeach
        @endif
      </div>
      <div class="carritoFooter col-auto mx-2 px-1 py-2 d-flex flex-column align-items-center justify-content-around">
        <div class="col-12 px-2 py-1 d-flex align-items-center justify-content-between">
          <p class="p-0 m-0">SubTotal</p>
          <p class="p-0 m-0" id="subtotalVenta">${{session("carrito") ? (session("carrito")["subtotal_Print"] ? session("carrito")["subtotal_Print"] : "0,0000") : "0,0000" }}</p>
        </div>
        <div class="col-12 mt-2 px-2 py-1 d-flex align-items-center justify-content-between">
          <p class="p-0 m-0">Total</p>
          <p class="p-0 m-0" id="totalVenta">${{session("carrito") ? (session("carrito")["total_Print"]?session("carrito")["total_Print"] : "0,0000" ) : "0,0000"}}</p>
        </div>
        <a href="{{ route("venta.procesarventa") }}" class="col-9 mt-3 text-decoration-none text-reset py-1 text-center">Realizar Venta</a>
      </div>
  </div>

  <div class="modal fade" id="adminModal" aria-hidden="true" aria-labelledby="adminModalLabel" tabindex="-1">
      <div class="modal-dialog modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h1 class="modal-title fs-5" id="adminModalLabel">Iniciar como Administrador</h1>
                  <div class="btn-close btn-closeAdminModal p-1 m-0 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Cerrar">
                      <img class="img-fluid" src="{{ asset("build/assets/icons/close.svg") }}" alt="Cerrar" draggable="false">
                  </div>
              </div>
              <div class="modal-body">
                    <form action="{{ route("inicio.admin") }}" method="post" id="formSessionAdmin" class="col-12 d-flex flex-column align-items-evenly justify-content-center">
                      @csrf
                      <h4 class="mt-2 mb-3 text-center col-12">Ingrese su contraseña para iniciar como administrador.</h4>
                      <input class="form-control @if ($errors->any()) @if (in_array("passAdmin",$errors->keys())) invalid @endif @endif" type="password" id="passAdmin" name="passAdmin" value="" placeholder="Su contraseña">
                      <div class="invalid-feedback fs-4 text-center @if ($errors->any()) @if (in_array("passAdmin",$errors->keys())) is-invalid @endif @endif position-relative" name="invalid-passAdmin" id="invalid-passAdmin">
                          @error('passAdmin')
                            @if (str_contains($message,"obligatorio"))
                              <p class="p-0 m-0">La contraseña es obligatoria.</p>
                            @elseif (str_contains($message,"Formato"))
                              <p class="p-0 m-0">{{ str_replace("pass admin","contraseña",$message) }}</p>
                              <p class="p-0 m-0 fs-5">Ingrese al menos una Mayúscula, un número y un carácter especial.<span class="ms-1">Caracteres</span></p>
                              <div name="validEspecialsPass" id="validEspecialsPass" class="validEspecials m-1 py-1 px-2 position-absolute d-none">
                                <p class="p-0 m-0"># @ | $ % & - _ ¡ ! ¿ ?</p>
                              </div>
                            @elseif(str_contains($message,"incorrecta"))
                              <p class="p-0 m-0">{{ str_replace("pass admin","contraseña",$message) }}</p>
                            @else
                              <p class="p-0 m-0">{{ "La ".str_replace("pass admin","contraseña",$message) }}</p>
                            @endif
                          @enderror
                      </div>
                    </form>  
              </div>
              <div class="modal-footer d-flex align-items-center justify-content-between">
                  <input type="submit" form="formSessionAdmin" class="btn ms-3 col-auto" value="Iniciar">
                  <button type="button" class="btn me-3" data-bs-dismiss="modal">Cancelar</button>
              </div>
          </div>
      </div>
  </div>

@yield('footer')
<script src="{{ asset("vendor/bootstrap/js/bootstrap.bundle.min.js") }}"></script>
@vite('resources/js/app/index.js')
<script>
  function mostrarCarga() {
    document.getElementById("loading-screen").style.display = "flex";
  }

  function ocultarCarga() {
    document.getElementById("loading-screen").style.display = "none";
  }

  async function fetchConCarga(url, options) {
    try {
      mostrarCarga();
      const res = await fetch(url, options);
      return res;
    } finally {
      ocultarCarga();
    }
  }

  async function deleteOfCart(button){
    let data=new FormData();
    data.append("idProd", button.dataset.prod);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route("producto.deleteofcart") }}",{
      method: "post",
      body: data,
      headers: {
          "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrio un error al eliminar un producto del carrito.");
        }
        return respuesta.json();
    })
  }
  async function getOldsProdsEditCompra(button){
    let data=new FormData();
    data.append("idCompra", button.dataset.compra);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route("compra.getprodscompra") }}",{
      method: "post",
      body: data,
      headers: {
          "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrio un error al reiniciar los productos de la compra.");
        }
        return respuesta.json();
    })
  }

  async function refreshValue(inputVal,idProd){
    let data=new FormData();
    data.append("inputVal", inputVal);
    data.append("idProd", idProd);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route("producto.refreshcart") }}",{
      method: "post",
      body: data,
      headers: {
          "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrio un error al modificar un producto del carrito.");
        }
        return respuesta.json();
    })
    
  }

  async function refreshCart(selectIds,selectVals){
    let data=new FormData();
    [...selectIds.selectedOptions].forEach(opt=>{
      data.append("modifiedIds[]", opt.value);
    });
    [...selectVals.selectedOptions].forEach(opt=>{
      data.append("modifiedVals[]", opt.value);
    });
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route("producto.refreshallcart") }}",{
      method: "post",
      body: data,
      headers: {
          "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrio un error modificar un producto del carrito.");
        }
        return respuesta.json();
    })
    .then(data => {
        if (data.Error){
            throw new Error(data.Error);
        }else{
          return true;
        }
    });
  }

  async function editarCompra(idProd){
    let data=new FormData();
    data.append("idProd", idProd);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route('compra.editprodcompra') }}",{
      method: "post",
      body: data,
      headers: {
          "X-Requested-With": "XMLHttpRequest"
      }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrio un error al redireccionar la pagina.");
        }
        return respuesta.json();
    });
  }
  
  async function deleteProdCompra(idProd){
    let data=new FormData();
    data.append("idProd", idProd);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route('compra.deleteprodcompra') }}",{
        method: "post",
        body: data,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrió un error al eliminar el producto.");
        }
        return respuesta.json();
    })
  }
</script>
<script>
    const errAdmin={{ isset($errors) ? ( in_array("passAdmin",$errors->keys()) ? json_encode(1) : json_encode(0)) : json_encode(0) }};
    if(errAdmin){
      const btnA=document.querySelector("#btnModalAdmin");
      if(btnA!=null){
        btnA.click();
      }
    }
</script>
<script>
    async function getMorePendientes(offset){
    let data=new FormData();
    data.append("offset", offset);
    data.append("_token", "{{ csrf_token() }}");
    return fetchConCarga("{{ route('venta.getmorependientes') }}",{
        method: "post",
        body: data,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrió un error inesperado.");
        }
        return respuesta.json();
    })
  }
</script>
</body>
</html>
@php
@endphp