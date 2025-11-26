import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import gps_location_off from '@/../assets/leaflet-icons/gps-location-off.svg';
import gps_location_on from '@/../assets/leaflet-icons/gps-location-on.svg';
import marker_icon_red from '@/../assets/leaflet-icons/marker-icon-red.png';
import marker_icon_2x_red from '@/../assets/leaflet-icons/marker-icon-2x-red.png';
import marker_icon_blue from '@/../assets/leaflet-icons/marker-icon-blue.png';
import marker_icon_2x_blue from '@/../assets/leaflet-icons/marker-icon-2x-blue.png';
import marker_shadow from '@/../assets/leaflet-icons/marker-shadow.png';
const MAPBOX_TOKEN = import.meta.env.VITE_MAPBOX_TOKEN;

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
        var latD=document.querySelector("#map").dataset.lat;
        var lngD=document.querySelector("#map").dataset.lng;
        if(latD!=null && lngD!=null && latD.trim()!="" && lngD.trim()!=""){
            var map = L.map('map',{
                center:[latD,lngD],
                zoom: 18,
                layers:[porDefecto]
            }); // Ubicacion destino
            L.marker([latD, lngD],{ icon: redIcon }).addTo(map);
            L.control.layers(baseMaps).addTo(map);
        }else{
            var map = L.map('map',{
                center:[vertical,horizontal],
                zoom: 13,
                layers:[porDefecto]
            }); // San Luis, Argentina
            L.control.layers(baseMaps).addTo(map);
        }
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



