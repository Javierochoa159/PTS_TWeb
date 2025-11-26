<div class="proveedor col-12 mb-4">
    <a class="p-3 d-flex flex-column justify-content-center text-reset text-decoration-none" href="{{ route("proveedor.proveedor",$proveedor->id) }}">
        <p class="nombreProveedor p-0 m-0 pb-2 ps-1">{{ $proveedor->nombre }}</p>
        <p class="ultimaCompraProveedor p-0 m-0 pt-2 pe-1">{{ $proveedor->fecha_compra ? "Ultima compra: ".date_create($proveedor->fecha_compra)->format("d/m/Y H:i") : "Ultima compra: --/--/---- --:--" }}</p>
    </a>
</div>