import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import gps_location_off from '@/../assets/leaflet-icons/gps-location-off.svg';
import gps_location_on from '@/../assets/leaflet-icons/gps-location-on.svg';
import marker_icon_red from '@/../assets/leaflet-icons/marker-icon-red.png';
import marker_icon_2x_red from '@/../assets/leaflet-icons/marker-icon-2x-red.png';
//import marker_icon_green from '@/../assets/leaflet-icons/marker-icon-green.png';
//import marker_icon_2x_green from '@/../assets/leaflet-icons/marker-icon-2x-green.png';
import marker_icon_blue from '@/../assets/leaflet-icons/marker-icon-blue.png';
import marker_icon_2x_blue from '@/../assets/leaflet-icons/marker-icon-2x-blue.png';
import marker_shadow from '@/../assets/leaflet-icons/marker-shadow.png';
const MAPBOX_TOKEN = import.meta.env.VITE_MAPBOX_TOKEN;

import mbxGeocoding from '@mapbox/mapbox-sdk/services/geocoding';

const geocodingClient = mbxGeocoding({ accessToken: MAPBOX_TOKEN });


//San Luis
const vertical=-33.3020736;
const horizontal=-66.3369577;
//Distintas capas para el mapa

var porDefecto = L.tileLayer(`https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=${MAPBOX_TOKEN}`, {
        id: 'mapbox/streets-v12',
        tileSize: 512,
        zoomOffset: -1,
    });
var simple = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
var satelite = L.tileLayer(`https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.jpg90?access_token=${MAPBOX_TOKEN}`);

var destino=null;
var miUbicacion = null;

//Icono color Rojo, Destino
var redIcon = L.icon({
    iconUrl: marker_icon_red,
    iconRetinaUrl: marker_icon_2x_red,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: marker_shadow,
    shadowSize: [41, 41]
});

//Icono color Verde
/*
var greenIcon = L.icon({
    iconUrl: marker_icon_green,
    iconRetinaUrl: marker_icon_2x_green,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: marker_shadow,
    shadowSize: [41, 41]
});
*/
//Icono color Azul, My Location
var blueIcon = L.icon({
    iconUrl: marker_icon_blue,
    iconRetinaUrl: marker_icon_2x_blue,
    iconSize: [15, 25],
    iconAnchor: [7, 25],
    popupAnchor: [1, -21],
    shadowUrl: marker_shadow,
    shadowSize: [25, 25]
});

// Añadir distintas capas al mapa
var baseMaps = {
    "Default": porDefecto,
    "Simple": simple,
    "Satelite": satelite
};

var map;

window.addEventListener("load",()=>{
    map=iniciarMapa();
    if(map!=null){
        addMyLocation();
        getMyLocation();
    }
});
function iniciarMapa(){
    // Inicializa el mapa centrado en una ubicación por defecto
    if(document.querySelector("#map")!=null){
        var map = L.map('map',{
        center:[vertical,horizontal],
            zoom: 13,
            layers:[porDefecto]
        }); // San Luis, Argentina
        L.control.layers(baseMaps).addTo(map);
        clickMap(map);
        return map;
    }else{
        return null;
    }
}

function addMyLocation(){
    var divMap=document.querySelector("#map .leaflet-control-container");
    var myLocation=document.createElement("div");
    myLocation.setAttribute("id","btn-container");
    var imgLocation=new Image();
    imgLocation.src=gps_location_off;
    imgLocation.setAttribute("id","btn-centrar-mapa");
    myLocation.appendChild(imgLocation);
    divMap.appendChild(myLocation);
}

async function clickMap(map){
    // Detectar clics en el mapa para obtener coordenadas
    map.on('click', function(e) {
        let lat = e.latlng.lat;
        let lng = e.latlng.lng;
        buscarCoordenadas(lat,lng)
        .then(data => {
            if(!data){
                throw new Error("No se encontró la dirección.");
            }else if (data.features!=null) {
                const inpBuscar=document.querySelector("#direccion-newVenta");
                const coordsInp=document.querySelector('#coordsDestino-newVenta');
                if(coordsInp!=null && inpBuscar!=null){
                    coordsInp.value=lat+'[;]'+lng;
                    if (destino) map.removeLayer(destino);
                    destino = L.marker([lat, lng],{ icon: redIcon }).addTo(map).bindPopup('Destino').openPopup();
                    destino.on('click', function(event) {
                        // Eliminar el destino anterior
                        coordsInp.value="";
                        map.removeLayer(event.target);
                        destino=null;
                    });
                    inpBuscar.value=data.features[0].properties.full_address;
                }
            } else {
                throw new Error("No se encontró la dirección.");
            }
        })
        .catch(error => {
            const divMensaje=document.querySelector("#invalid-map");
            if(!divMensaje.classList.contains("is-invalid")){
                const oldClass=divMensaje.classList.value;
                const oldMess=divMensaje.firstElementChild.textContent;
                divMensaje.firstElementChild.textContent=error;
                divMensaje.classList.add("is-invalid");
                setTimeout(()=>{
                    divMensaje.classList.value=oldClass;
                    divMensaje.firstElementChild.textContent=oldMess;
                },4000);
            }
        });
    });
}

async function buscarCoordenadas(lat,lng){
    return fetch(`https://api.mapbox.com/search/geocode/v6/reverse?longitude=${lng}&latitude=${lat}&access_token=${MAPBOX_TOKEN}&feature_type=address&limit=1`)
    .then(respuesta => {
        if (!respuesta.ok) {
            throw new Error("Ocurrió un error al buscar la dirección.");
        }
        return respuesta.json();
    })
}

function getMyLocation(){
    var btnCentrar=document.querySelector("#btn-centrar-mapa");
    btnCentrar.addEventListener("click",(e)=>{
        e.preventDefault();
        e.stopPropagation();
        btnCentrar.src=gps_location_on;
        centrarEnMiUbicacion(btnCentrar);
    });
}

function centrarEnMiUbicacion(btnCentrar) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            // Centra el mapa en la ubicación actual
            map.setView([lat, lon], 25);  // 25 es el nivel de zoom que puedes ajustar
        
            // Añadir un marcador en la ubicación actual
            if(miUbicacion!=null){
                map.removeLayer(miUbicacion);
                miUbicacion=null;
            }
            miUbicacion=L.marker([lat, lon],{icon: blueIcon}).addTo(map)
                .bindPopup("Estás por aquí.")
                .openPopup();
            miUbicacion.addEventListener("click",(e)=>{
                const coordsInp=document.querySelector('#coordsDestino-newVenta');
                if(coordsInp!=null){
                    coordsInp.value="";
                }
                map.removeLayer(e.target);
                miUbicacion=null;
            });
        }, (error) => {
            const divMensaje=document.querySelector("#invalid-map");
            if(!divMensaje.classList.contains("is-invalid")){
                const oldClass=divMensaje.classList.value;
                const oldMess=divMensaje.firstElementChild.textContent;
                divMensaje.firstElementChild.textContent="No se pudo obtener la ubicación.";
                divMensaje.classList.add("is-invalid");
                setTimeout(()=>{
                    divMensaje.classList.value=oldClass;
                    divMensaje.firstElementChild.textContent=oldMess;
                },4000);
            }
            btnCentrar.src=gps_location_off;
        });
    } else {
        const divMensaje=document.querySelector("#invalid-map");
        if(!divMensaje.classList.contains("is-invalid")){
            const oldClass=divMensaje.classList.value;
            const oldMess=divMensaje.firstElementChild.textContent;
            divMensaje.firstElementChild.textContent="Geolocalización no soportada por este navegador.";
            divMensaje.classList.add("is-invalid");
            setTimeout(()=>{
                divMensaje.classList.value=oldClass;
                divMensaje.firstElementChild.textContent=oldMess;
            },4000);
        }
        btnCentrar.src=gps_location_off;
    }
}

let resultados = [];

async function buscarUbicacion(direccion){
    buscar(direccion)
    .then(respuesta => {
        if (respuesta.statusCode!=200) {
            throw new Error("Ocurrió un error al buscar la dirección.");
        }
        return respuesta.body;
    })
    .then(data => {
        if(!data){
            throw new Error("No se encontró la dirección.");
        }else
        if (data.features!=null) {
            resultados=data.features;
            indexActual = 0;
            mostrarUbicacion(indexActual);
            crearBotonesNavegacion();
        } else {
            throw new Error("No se encontró la dirección.");
        }
    })
    .catch(error => {
        const divMensaje=document.querySelector("#invalid-map");
        if(!divMensaje.classList.contains("is-invalid")){
            const oldClass=divMensaje.classList.value;
            const oldMess=divMensaje.firstElementChild.textContent;
            divMensaje.firstElementChild.textContent=error;
            divMensaje.classList.add("is-invalid");
            setTimeout(()=>{
                divMensaje.classList.value=oldClass;
                divMensaje.firstElementChild.textContent=oldMess;
            },4000);
        }
    });
};

async function buscar(direccion){
    try{
        mostrarCarga();
        const res=await geocodingClient
        .forwardGeocode({
            query: direccion,
            limit: 15,
            countries: ['AR'],
            proximity: [horizontal, vertical],
        })
        .send();
        return res;
    }finally{
        ocultarCarga();
    }
}

let indexActual = 0;


function mostrarUbicacion(index) {
    const ubic = resultados[index];
    if (!ubic) return;

    const [lng, lat] = ubic.center;
    if (destino) map.removeLayer(destino);
    const coordsInp=document.querySelector('#coordsDestino-newVenta');

    destino = L.marker([lat, lng],{icon: redIcon}).addTo(map)
        .bindPopup(ubic.place_name)
        .openPopup();
    destino.addEventListener("click",(e)=>{
        if(coordsInp!=null){
            coordsInp.value="";
        }
        map.removeLayer(e.target);
        destino=null;
    });

    map.setView([lat, lng], 15);
    if(coordsInp!=null){
        coordsInp.value = lat+"[;]"+lng;
    }
}

function crearBotonesNavegacion() {
    let contenedor = document.querySelector('#navegacion-ubicaciones');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'navegacion-ubicaciones';
        contenedor.className = 'col-12 gap-2 mt-2 d-flex justify-content-between align-items-center position-relative';
        document.querySelector('#divMapa_Boton').appendChild(contenedor);
    }

    contenedor.innerHTML = `
        <button type="button" id="prevUbicacion" class="ms-3 py-3 btn btn-secondary position-absolute switchResUbicacion left-0" title="Anterior"></button>
        <button type="button" id="nextUbicacion" class="me-3 py-3 btn btn-secondary position-absolute switchResUbicacion right-0" title="Siguiente"></button>
    `;

    document.querySelector('#prevUbicacion').addEventListener('click', () => {
        indexActual = (indexActual - 1 + resultados.length) % resultados.length;
        mostrarUbicacion(indexActual);
    });

    document.querySelector('#nextUbicacion').addEventListener('click', () => {
        indexActual = (indexActual + 1) % resultados.length;
        mostrarUbicacion(indexActual);
    });
}

let indiceSeleccionado=-1;

(()=>{
    const inpBuscar=document.querySelector("#direccion-newVenta");
    const resultadosDiv = document.querySelector("#resultados");
    if(inpBuscar!=null && resultadosDiv!=null){
        inpBuscar.addEventListener("input", (e) => {
            const texto = e.target.value.trim();
            resultadosDiv.innerHTML = "";
            if (texto.length < 3) return; 
            buscadorUbicacion(texto);
        });
        inpBuscar.addEventListener('keydown', (e) => {
            if (!resultadosDiv.classList.contains('fill')) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                moverSeleccion('down');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                moverSeleccion('up');
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (indiceSeleccionado >= 0) {
                    seleccionarOpcion(indiceSeleccionado);
                }else{
                    resultadosDiv.classList.remove("fill");
                    const ubicacion=inpBuscar.value;
                    if(ubicacion.trim()!=""){
                        buscarUbicacion(ubicacion);
                    }
                }
            }
        });
        inpBuscar.addEventListener('blur', () => {
            setTimeout(() => {
                resultadosDiv.classList.remove('fill');
            }, 150);
        });

        function seleccionarOpcion(index) {
            if (resultados[index]) {
                resultados[index].click();
                const navUbicaciones=document.querySelector("#navegacion-ubicaciones");
                if(navUbicaciones!=null){
                    navUbicaciones.parentElement.removeChild(navUbicaciones);
                }
                resultados=[];
                indiceSeleccionado=-1;
                indexActual=0;
            }
        }

        function moverSeleccion(direccion) {
            const items = resultadosDiv.querySelectorAll('.list-group-item');
            if (items.length === 0) return;

            // Quitar clase de selección actual
            if (indiceSeleccionado >= 0) {
                items[indiceSeleccionado].classList.remove('active');
            }

            if (direccion === 'down') {
                indiceSeleccionado = (indiceSeleccionado + 1) % items.length;
            } else if (direccion === 'up') {
                indiceSeleccionado = (indiceSeleccionado - 1 + items.length) % items.length;
            }

            // Añadir clase activa
            items[indiceSeleccionado].classList.add('active');
            items[indiceSeleccionado].scrollIntoView({ block: 'nearest' });

            // Actualizar input visualmente (opcional)
            inpBuscar.value = resultados[indiceSeleccionado].textContent;
        }
    }


    async function buscadorUbicacion(ubicacion){
        geocodingClient
        .forwardGeocode({
            query: ubicacion+", San Luis",
            limit: 15,
            countries: ['AR'],
            proximity: [horizontal, vertical],
        })
        .send()
        .then(respuesta => {
            if (respuesta.statusCode!=200) {
                throw new Error("Ocurrió un error al buscar la dirección.");
            }
            return respuesta.body;
        })
        .then(data => {
            if(!data){
                throw new Error("No se encontró la dirección.");
            }else
            if (data.features!=null) {
                resultados=data.features;
                const resultadosDiv = document.querySelector("#resultados");
                indiceSeleccionado = -1;
                resultadosDiv.innerHTML = "";
                if(resultados.length<1){
                    resultadosDiv.classList.remove("fill");
                }else{
                    resultadosDiv.classList.add("fill");
                    let newResultados=[];
                    let i=0;
                    resultados.forEach((lugar) => {
                        const item = document.createElement("button");
                        item.setAttribute("type","button")
                        item.className = "resultado list-group-item";
                        item.textContent = lugar.place_name;
                        item.addEventListener("click", (e) => {
                            e.preventDefault();
                            resultadosDiv.classList.remove("fill");
                            inpBuscar.value=item.textContent;
                            const [lng, lat] = lugar.center;
                            if (destino) destino.remove();
                            destino = L.marker([lat, lng],{icon: redIcon}).addTo(map);
                            const coordsInp=document.querySelector("#coordsDestino-newVenta");
                            if(coordsInp!=null){
                                coordsInp.value=lat+"[;]"+lng;
                            }
                            destino.addEventListener("click",(e)=>{
                                if(coordsInp!=null){
                                    coordsInp.value="";
                                }
                                map.removeLayer(e.target);
                                destino=null;
                            });
                            map.setView([lat, lng], 15);
                            resultadosDiv.innerHTML = "";
                        });
                        resultadosDiv.appendChild(item);
                        newResultados[i]=item;
                        i++;
                    });
                    resultados=newResultados;
                }
            } else {
                throw new Error("No se encontró la dirección.");
            }
        })
        .catch(error => {
            const divMensaje=document.querySelector("#invalid-map");
            if(!divMensaje.classList.contains("is-invalid")){
                const oldClass=divMensaje.classList.value;
                const oldMess=divMensaje.firstElementChild.textContent;
                divMensaje.firstElementChild.textContent=error;
                divMensaje.classList.add("is-invalid");
                setTimeout(()=>{
                    divMensaje.classList.value=oldClass;
                    divMensaje.firstElementChild.textContent=oldMess;
                },4000);
            }
        });
    }
})();

(()=>{
    const limpiarNewVenta = document.querySelector("#btnReiniciarVenta");
    if(limpiarNewVenta!=null){
        limpiarNewVenta.addEventListener("click",()=>{
            const prevFotos = limpiarNewVenta.closest("form").querySelector(".fotos-newVenta .previewFotos");
            while(prevFotos.childElementCount>0){
                prevFotos.lastElementChild.click();
            }
            const selects=limpiarNewVenta.closest("form").querySelectorAll("select.form-control");
            for(const select of selects){
                const optSelected=select.querySelector("button selectedcontent");
                if(optSelected!=null){
                    optSelected.textContent=select.children[1].textContent;
                    select.querySelector("option[selected]").selected=false;
                    select.firstElementChild.selected=true;
                }
            }
            const metodoPago=limpiarNewVenta.closest("form").querySelector("#metodoPago-newVenta");
            if(metodoPago!=null){
                const disFotos=metodoPago.closest("form").querySelector(".disableFotos");
                if(disFotos!=null){
                    disFotos.classList.remove("d-none");
                }
            }
            const inputs=limpiarNewVenta.closest("form").querySelectorAll("table input.form-control");
            for(const input of inputs){
                input.disabled=false;
            }
            const textarea = limpiarNewVenta.closest("form").querySelector("table textarea.form-control");
            if(textarea!=null){
                textarea.disabled=false;
            }
            const checkbox=limpiarNewVenta.closest("form").querySelector("input[type='checkbox']");
            if(checkbox!=null){
                checkbox.disabled=true;
            }
            if(destino){
                map.removeLayer(destino);
                destino=null;
            }
            if(miUbicacion!=null){
                map.removeLayer(miUbicacion);
                miUbicacion=null;
            }
            const navUbicaciones=limpiarNewVenta.closest("form").querySelector("#navegacion-ubicaciones");
            if(navUbicaciones!=null){
                navUbicaciones.parentElement.removeChild(navUbicaciones);
            }
            indexActual=0;
            indiceSeleccionado=-1;
            resultados = [];
        });
    }
})();

(()=>{
    const limpiarModVenta = document.querySelector("#btnReiniciarModificarVenta");
    if(limpiarModVenta!=null){
        limpiarModVenta.addEventListener("click",()=>{
            setTimeout(()=>{
                const selects=limpiarModVenta.closest("form").querySelectorAll("select.form-control");
                for(const select of selects){
                    const optSelected=select.querySelector("button selectedcontent");
                    if(optSelected!=null){
                        optSelected.textContent=select.querySelector("option[selected]").textContent;
                    }
                }
                const metodoPago=limpiarModVenta.closest("form").querySelector("#metodoPago-newVenta");
                if(metodoPago!=null){
                    const disFotos=metodoPago.closest("form").querySelector(".disableFotos");
                    if(disFotos!=null){
                        if(metodoPago.value.trim()!="Pendiente"){
                            disFotos.classList.remove("d-none");
                        }else{
                            disFotos.classList.add("d-none");
                        }
                    }
                }
                const tipoVenta=limpiarModVenta.closest("form").querySelector("#tipoVenta-newVenta");
                if(tipoVenta!=null){
                    const inputs=limpiarModVenta.closest("form").querySelectorAll("table input.form-control");
                    const textarea = limpiarModVenta.closest("form").querySelector("table textarea.form-control");
                    const checkbox=limpiarModVenta.closest("form").querySelector("input[type='checkbox']");
                    switch(tipoVenta){
                        case "Envio":
                            for(const input of inputs){
                                input.disabled=false;
                            }
                            if(textarea!=null){
                                textarea.disabled=false;
                            }
                            if(checkbox!=null){
                                checkbox.disabled=true;
                            }
                            break;
                        case "Local":
                            for(const input of inputs){
                                if(!input.id.trim().includes("receptor") || !input.id.trim().includes("contacto")){
                                    input.disabled=true;
                                }
                            }
                            if(textarea!=null){
                                textarea.disabled=true;
                            }
                            if(checkbox!=null){
                                checkbox.disabled=false;
                            }
                        break;
                    }
                    if(checkbox.checked){
                        for(const input of inputs){
                            if(input.id.trim().includes("receptor") || input.id.trim().includes("contacto")){
                                input.disabled=true;
                            }
                        }
                    }else{
                        for(const input of inputs){
                            if(input.id.trim().includes("receptor") || input.id.trim().includes("contacto")){
                                input.disabled=false;
                            }
                        }
                    }
                }
            },200);
            if(destino){
                map.removeLayer(destino);
                destino=null;
            }
            if(miUbicacion!=null){
                map.removeLayer(miUbicacion);
                miUbicacion=null;
            }
            const navUbicaciones=limpiarModVenta.closest("form").querySelector("#navegacion-ubicaciones");
            if(navUbicaciones!=null){
                navUbicaciones.parentElement.removeChild(navUbicaciones);
            }
            indexActual=0;
            indiceSeleccionado=-1;
            resultados = [];
            setTimeout(()=>{
                const coordsInp=document.querySelector("#coordsDestino-newVenta");
                if(coordsInp!=null && coordsInp.value.trim()!=""){
                    const valueDireccion=document.querySelector("#direccion-newVenta");
                    const coordenadas=coordsInp.value.split("[;]");
                    if(coordenadas.length==2){
                        const [lat,lng]=coordenadas;
                        if(valueDireccion!=null){
                            destino = L.marker([lat, lng],{icon: redIcon}).addTo(map)
                                .bindPopup(valueDireccion.value)
                                .openPopup();
                        }else{
                            destino = L.marker([lat, lng],{icon: redIcon}).addTo(map);
                        }
                        destino.addEventListener("click",(e)=>{
                            coordsInp.value="";
                            map.removeLayer(e.target);
                            destino=null;
                        });
                        map.setView([lat, lng], 15);
                    }
                }
            },200);
        });
    }
})();

(()=>{
    window.addEventListener("load",()=>{
        const coordsInp=document.querySelector("#coordsDestino-newVenta");
        if(coordsInp!=null && coordsInp.value.trim()!=""){
            const valueDireccion=document.querySelector("#direccion-newVenta");
            const coordenadas=coordsInp.value.split("[;]");
            if(coordenadas.length==2){
                const [lat,lng]=coordenadas;
                if(valueDireccion!=null){
                    destino = L.marker([lat, lng],{icon: redIcon}).addTo(map)
                        .bindPopup(valueDireccion.value)
                        .openPopup();
                }else{
                    destino = L.marker([lat, lng],{icon: redIcon}).addTo(map);
                }
                destino.addEventListener("click",(e)=>{
                    coordsInp.value="";
                    map.removeLayer(e.target);
                    destino=null;
                });
                map.setView([lat, lng], 15);
            }
        }
    })
})();

