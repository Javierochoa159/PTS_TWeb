<div class="col-12 d-flex align-items-center justify-content-center sticky-top">
    <div class="mensajeDiv mb-5 text-center justify-content-center invalid-feedback @php 
        if(session()->has("mensaje")){
            if(isset(session("mensaje")["Error"]))echo "invalid";
            elseif(isset(session("mensaje")["Success"]))echo "valid";
        }            
    @endphp">
    <pre class="m-0"><span>@php 
        if(session()->has("mensaje"))echo session("mensaje")["Mensaje"];           
    @endphp</span></pre>
    </div>
</div>