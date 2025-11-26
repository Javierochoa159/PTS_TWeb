import Decimal from "decimal.js";

let running = false;
let timeoutId = null;
let currentDelay = 400;
const minDelay = 100;
const accelStep = 75;

function actualizarTotales(input, total, subtotal) {
    const totalV= input.closest(".producto").querySelector(".precioV");
    const totalVT= input.closest(".producto").querySelector(".precioVT");
    const precioV = new Decimal(totalV.textContent.replace("$","").replace(/\./g, '').replace(",",".").split(" ")[0].trim() || "0");
    const oldprecioVT = new Decimal(totalVT.textContent.replace("Total: $","").replace(/\./g, '').replace(",",".").trim() || "0");
    const cantidad = new Decimal(input.value || "0");
    const newPrecioVT = precioV.mul(cantidad).toDecimalPlaces(4);
    const fmt = new Intl.NumberFormat('es-AR', {minimumFractionDigits: 4,maximumFractionDigits: 4});
    totalVT.textContent = "Total: $"+fmt.format(newPrecioVT.toFixed(4));
    
    let oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    let oldSubTotal = new Decimal(subtotal.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");

    let newSubTotal = oldSubTotal.minus(oldprecioVT).toDecimalPlaces(4);
    newSubTotal = newSubTotal.plus(newPrecioVT).toDecimalPlaces(4);

    let newTotal = oldTotal.minus(oldprecioVT).toDecimalPlaces(4);
    newTotal = newTotal.plus(newPrecioVT).toDecimalPlaces(4);
    
    subtotal.textContent = "";
    subtotal.textContent = "$"+fmt.format(newSubTotal.toFixed(4));
    total.textContent = "";
    total.textContent = "$"+fmt.format(newTotal.toFixed(4));
}

function tick(type, input, total, subtotal) {
    if (!running) return;    
    const val  = new Decimal(input.value || '1', 4);
    const step = new Decimal(input.step || '1', 4);
    const min  = input.min !== '' ? new Decimal(input.min, 4) : new Decimal(1, 4);
    const max  = input.max !== '' ? new Decimal(input.max, 4) : new Decimal(1, 4);
    if (type === 'plus') {
        if (val.gte(max)) { stop(); return; }
        if(input.dataset.medida.trim()=="Unidad"){
            if(val.plus(step).toDecimalPlaces(0).gte(max)){
                input.value=max.toDecimalPlaces(0);
            }else{
                input.value = val.plus(step).toDecimalPlaces(0);
            }
        }
        else{
            if(val.plus(step).toDecimalPlaces(4).gte(max)){
                input.value=max.toDecimalPlaces(4);
            }else{
                input.value = val.plus(step).toDecimalPlaces(4);
            }
        }
    } else {
        if (val.lte(min)) { stop(); return; }
        if(input.dataset.medida.trim()=="Unidad"){
            if(val.minus(step).toDecimalPlaces(0).lte(min)){
                input.value=min.toDecimalPlaces(0);
            }else{
                input.value = val.minus(step).toDecimalPlaces(0);
            }
        }
        else{
            if(val.minus(step).toDecimalPlaces(4).lte(min)){
                input.value=min.toDecimalPlaces(4);
            }else{
                input.value = val.minus(step).toDecimalPlaces(4);
            }
        }
    }

    actualizarTotales(input, total, subtotal);

    currentDelay = Math.max(minDelay, currentDelay - accelStep);
    timeoutId = setTimeout(() => tick(type, input, total, subtotal), currentDelay);
}

function start(type, target, ev) {
    ev.preventDefault();
    stop();
    const input = target.parentElement.nextElementSibling.children[0];
    if(type != null){
        running = true;
        currentDelay = 400;
        if (target.setPointerCapture && ev.pointerId !== undefined) {
        try { target.setPointerCapture(ev.pointerId); } catch {}
        }
        const subtotal = document.querySelector("#subtotalVenta");
        const total = document.querySelector("#totalVenta");
        tick(type, input, total, subtotal);
    }
}

function stop() {
    running = false;
    if (timeoutId !== null) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
}


const globalStop = () => stop();
window.addEventListener('pointerup', globalStop, true);
window.addEventListener('pointercancel', globalStop, true);
window.addEventListener('blur', globalStop);
document.addEventListener('mouseleave', globalStop);

(()=>{
    document.querySelectorAll("[name='refreshInput']").forEach(button=>{
        button.addEventListener("pointerdown",(e)=>{
            const input = button.closest(".producto").querySelector("input");
            input.classList.add("modified");
            const btnR=document.querySelector("#btnRefreshCart");
            btnR.classList.remove("d-none");
            btnR.classList.add("d-block");
            start(button.dataset.refbtn,button,e);
        });
    });
})();

const txtError='<div class="mensaje-error col-12 d-flex align-items-center justify-content-center sticky-top"><div class="mensajeDiv mb-5 text-center justify-content-center invalid-feedback invalid"><span></span></div></div>';

(()=>{
    document.querySelectorAll("#carritoModal .carritoBody .producto input").forEach(input=>{
        input.addEventListener("input", () => {
            input.classList.add("modified");
            const btnR=document.querySelector("#btnRefreshCart");
            btnR.classList.remove("d-none");
            btnR.classList.add("d-block");
            const subtotal = document.querySelector("#subtotalVenta");
            const total = document.querySelector("#totalVenta");
            actualizarTotales(input, total, subtotal);
        });
    });
})();

(()=>{
    window.addEventListener("load",()=>{
        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
        if(modInps.length>0){
            const btnR=document.querySelector("#btnRefreshCart");
            btnR.classList.remove("d-none");
            btnR.classList.add("d-block");
        }
    });
    const btnR=document.querySelector("#btnRefreshCart");
    btnR.addEventListener("click",async(e)=>{
        e.preventDefault();
        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
        if(modInps.length>0){
            let selectIds=document.createElement("select");
            selectIds.multiple=true;
            let selectVals=document.createElement("select");
            selectVals.multiple=true;
            modInps.forEach(input=>{
                let idOption=document.createElement("option");
                idOption.setAttribute("value",input.dataset.prod);
                idOption.selected=true;
                selectIds.appendChild(idOption);
                let valOption=document.createElement("option");
                valOption.setAttribute("value",input.value);
                valOption.selected=true;
                selectVals.appendChild(valOption);
            });
            refreshCart(selectIds,selectVals)
            .then(res=>{
                if(res){
                    btnR.classList.remove("d-block");
                    btnR.classList.add("d-none");
                }
            })
            .catch(error => {
                const parser = new DOMParser();
                const newDoc=parser.parseFromString(txtError,"text/html");
                const divError = newDoc.body.firstChild;
                divError.querySelector("span").textContent=error;
                const bodyCart=document.querySelector("#carritoModal .carritoBody");
                if(bodyCart.querySelector(".mensaje-error")!=null){
                    bodyCart.replaceChild(divError,bodyCart.firstChild);
                    setTimeout(()=>{
                        if(bodyCart.hasChildNodes(divError)){
                            bodyCart.removeChild(divError);
                        }
                    },4000);
                }else{
                    bodyCart.insertBefore(divError, bodyCart.firstChild);
                    setTimeout(()=>{
                        if(bodyCart.hasChildNodes(divError)){
                            bodyCart.removeChild(divError);
                        }
                    },4000);
                }
            });
        }
    });
})();

(()=>{
    const formsAddtoCard=document.querySelectorAll("[name='formAddToCard']");
    for(const form of formsAddtoCard){
        form.addEventListener("submit",async(e)=>{
            e.preventDefault();
            const inputModifieds = document.querySelectorAll("#carritoModal .carritoBody input.modified");
            if(inputModifieds.length>0){
                let selectIds=document.createElement("select");
                selectIds.multiple=true;
                let selectVals=document.createElement("select");
                selectVals.multiple=true;
                inputModifieds.forEach(input=>{
                    let idOption=document.createElement("option");
                    idOption.setAttribute("value",input.dataset.prod);
                    idOption.selected=true;
                    selectIds.appendChild(idOption);
                    let valOption=document.createElement("option");
                    valOption.setAttribute("value",input.value);
                    valOption.selected=true;
                    selectVals.appendChild(valOption);
                });
                refreshCart(selectIds,selectVals)
                .then(res=>{
                    if(res){
                        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
                        if(modInps.length>0){
                            for(const inputMod of modInps){
                                inputMod.classList.remove("modified");
                            }
                            const btnR=document.querySelector("#btnRefreshCart");
                            btnR.classList.remove("d-block");
                            btnR.classList.add("d-none");
                        }
                        addToCart(form);
                    }
                })
                .catch(error => {
                    const divMensaje=document.querySelector(".mensajeDiv");
                    if(!divMensaje.classList.contains("invalid")){
                        const oldClass=divMensaje.classList.value;
                        const oldMess=divMensaje.querySelector("span").textContent;
                        divMensaje.classList.add("invalid");
                        divMensaje.classList.add('d-block');
                        var n = 5;
                        divMensaje.querySelector("span").textContent=error+"\nLa página se recargará en "+n+" seg.";
                        window.setInterval(()=>{
                            n--;
                            divMensaje.querySelector("span").textContent=error+"\nLa página se recargará en "+n+" seg.";
                            if(n<1){
                                divMensaje.classList.value=oldClass;
                                divMensaje.firstElementChild.textContent=oldMess;
                                window.location.reload(true);
                            }                        
                        },1000);
                    }
                    return false;
                });
            }else{
                addToCart(form);
            }
        });
    }
    async function addToCart(form){
        const productos=form.parentElement.parentElement;
        const data=new FormData(form);
        fetchConCarga(form.action,{
            method: form.method,
            body: data,
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(respuesta => {
            if (!respuesta.ok) {
                throw new Error("Ocurrio un error al añadir un producto al carrito.");
            }
            return respuesta.json();
        })
        .then(data => {
            if (data.success){ 
                if(data.newProdCart){
                    const parser = new DOMParser();
                    const newDoc=parser.parseFromString(data.newProdCart,"text/html");
                    const newCartElement = newDoc.body.firstChild;
                    const titulo=document.querySelector("#carritoModal .carritoHeader .offcanvas-title");
                    const carrito=document.querySelector("#carritoModal .carritoBody");
                    if(carrito!=null){
                        carrito.appendChild(newCartElement);
                        const input=newCartElement.querySelector("input");
                        input.addEventListener("input", () => {
                            const subtotal = document.querySelector("#subtotalVenta");
                            const total = document.querySelector("#totalVenta");
                            input.classList.add("modified");
                            const btnR=document.querySelector("#btnRefreshCart");
                            btnR.classList.remove("d-none");
                            btnR.classList.add("d-block");
                            actualizarTotales(input, total, subtotal);
                        });
                        input.addEventListener("keydown", async (e) => {
                            if (e.key === "Enter") {
                                e.preventDefault();
                                const inputVal=input.value;
                                const idProd=input.dataset.prod;
                                refreshValue(inputVal,idProd)
                                .then(data => {
                                    if (data.success){ 
                                        const precioVT=document.querySelector("#producto_"+idProd+" .precioVT");
                                        const subtotal=document.querySelector("#subtotalVenta");
                                        const total=document.querySelector("#totalVenta");
                                        
                                        precioVT.textContent="Total: $"+data.newPrecioVT;
                                        subtotal.textContent="$"+data.newSubtotal;
                                        total.textContent="$"+data.newTotal;
    
                                        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
                                        if(modInps.childElementCount==0){
                                        const btnR=document.querySelector("#btnRefreshCart");
                                        btnR.classList.remove("d-block");
                                        btnR.classList.add("d-none");
                                        }
                                    }else{
                                        throw new Error(data.Error);
                                    }
                                })
                                .catch(error => {
                                    const parser = new DOMParser();
                                    const newDoc=parser.parseFromString(txtError,"text/html");
                                    const divError = newDoc.body.firstChild;
                                    divError.querySelector("span").textContent=error;
                                    const bodyCart=document.querySelector("#carritoModal .carritoBody");
                                    if(bodyCart.querySelector(".mensaje-error")!=null){
                                        bodyCart.replaceChild(divError,bodyCart.firstChild);
                                        setTimeout(()=>{
                                            if(bodyCart.hasChildNodes(divError)){
                                                bodyCart.removeChild(divError);
                                            }
                                        },4000);
                                    }else{
                                        bodyCart.insertBefore(divError, bodyCart.firstChild);
                                        setTimeout(()=>{
                                            if(bodyCart.hasChildNodes(divError)){
                                                bodyCart.removeChild(divError);
                                            }
                                        },4000);
                                    }
                                });
                            }
                        });
                        const button=newCartElement.querySelector("[name='deleteOfCart']");
                        button.addEventListener("click", async(e)=>{
                            e.preventDefault();
                            deleteOfCart(button)
                            .then(data => {
                                if(data.success){
                                    const titulo=document.querySelector("#carritoModal .carritoHeader .offcanvas-title");
                                    titulo.textContent="Carrito de Venta ("+(parseInt(titulo.textContent.replace("Carrito de Venta (","").replace(")",""))-1)+")";
                                    const btnCarrito=document.querySelector("#btn-carrito span");
                                    btnCarrito.textContent=parseInt(btnCarrito.textContent)-1;
                                    const subtotalV=button.closest("#carritoModal").querySelector("#subtotalVenta");
                                    const totalV=button.closest("#carritoModal").querySelector("#totalVenta");
                                    button.closest(".carritoBody").removeChild(button.closest(".producto"));
                                    subtotalV.textContent="$"+data.subtotalV;
                                    totalV.textContent="$"+data.totalV;
                                    const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
                                    if(modInps.length==0){
                                        const btnR=document.querySelector("#btnRefreshCart");
                                        btnR.classList.remove("d-block");
                                        btnR.classList.add("d-none");
                                    }
                                }else{
                                    throw new Error(data.Error);
                                }
                                })
                                .catch(error => {
                                    const parser = new DOMParser();
                                    const newDoc=parser.parseFromString(txtError,"text/html");
                                    const divError = newDoc.body.firstChild;
                                    divError.querySelector("span").textContent=error;
                                    const bodyCart=document.querySelector("#carritoModal .carritoBody");
                                    if(bodyCart.querySelector(".mensaje-error")!=null){
                                        bodyCart.replaceChild(divError,bodyCart.firstChild);
                                        setTimeout(()=>{
                                            if(bodyCart.hasChildNodes(divError)){
                                                bodyCart.removeChild(divError);
                                            }
                                        },4000);
                                    }else{
                                        bodyCart.insertBefore(divError, bodyCart.firstChild);
                                        setTimeout(()=>{
                                            if(bodyCart.hasChildNodes(divError)){
                                                bodyCart.removeChild(divError);
                                            }
                                        },4000);
                                    }
                                });
                        });
                        newCartElement.querySelectorAll("[name='refreshInput']").forEach(buttonRef=>{
                            buttonRef.addEventListener("pointerdown",(e)=>{
                                input.classList.add("modified");
                                const btnR=document.querySelector("#btnRefreshCart");
                                btnR.classList.remove("d-none");
                                btnR.classList.add("d-block");
                                start(buttonRef.dataset.refbtn,buttonRef,e);
                            });
                        });
                        const btnCarrito=document.querySelector("#btn-carrito span");
                        btnCarrito.textContent=parseInt(btnCarrito.textContent)+1;
                        titulo.textContent="Carrito de Venta ("+(parseInt(titulo.textContent.replace("Carrito de Venta (","").replace(")",""))+1)+")";
                        const modalCart=document.querySelector("#carritoModal");
                        const subtotalV=modalCart.querySelector("#subtotalVenta");
                        const totalV=modalCart.querySelector("#totalVenta");
                        subtotalV.textContent="$"+data.subtotal;
                        totalV.textContent="$"+data.total;
                    }else{
                        window.location.reload(true);
                    }
                }else{
                    window.location.reload(true);
                }
            }else{
                throw new Error(data.Error);
            }
        })
        .catch(error => {
            const divMensaje=document.querySelector(".mensajeDiv");
            if(!divMensaje.classList.contains("invalid")){
                const oldClass=divMensaje.classList.value;
                const oldMess=divMensaje.firstElementChild.textContent;
                divMensaje.firstElementChild.textContent=error;
                divMensaje.classList.add("invalid");
                divMensaje.classList.add('d-block');
                setTimeout(()=>{
                    divMensaje.classList.value=oldClass;
                    divMensaje.firstElementChild.textContent=oldMess;
                },4000);
            }
        });
    }
})();

(()=>{
    document.querySelectorAll("#carritoModal .carritoBody .producto input").forEach(input => {
        input.addEventListener("keydown", async(e)=>{
            if (e.key === "Enter") {
                e.preventDefault();
                const inputVal=input.value;
                const idProd=input.dataset.prod;
                refreshValue(inputVal,idProd)
                .then(data => {
                    if (data.success){ 
                        const precioVT=document.querySelector("#producto_"+idProd+" .precioVT");
                        const subtotal=document.querySelector("#subtotalVenta");
                        const total=document.querySelector("#totalVenta");
                        
                        precioVT.textContent="Total: $"+data.newPrecioVT;
                        subtotal.textContent="$"+data.newSubtotal;
                        total.textContent="$"+data.newTotal;

                        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
                        if(modInps.childElementCount==0){
                        const btnR=document.querySelector("#btnRefreshCart");
                        btnR.classList.remove("d-block");
                        btnR.classList.add("d-none");
                        }
                    }else{
                        throw new Error(data.Error);
                    }
                })
                .catch(error => {
                    const parser = new DOMParser();
                    const newDoc=parser.parseFromString(txtError,"text/html");
                    const divError = newDoc.body.firstChild;
                    divError.querySelector("span").textContent=error;
                    const bodyCart=document.querySelector("#carritoModal .carritoBody");
                    if(bodyCart.querySelector(".mensaje-error")!=null){
                        bodyCart.replaceChild(divError,bodyCart.firstChild);
                        setTimeout(()=>{
                            if(bodyCart.hasChildNodes(divError)){
                                bodyCart.removeChild(divError);
                            }
                        },4000);
                    }else{
                        bodyCart.insertBefore(divError, bodyCart.firstChild);
                        setTimeout(()=>{
                            if(bodyCart.hasChildNodes(divError)){
                                bodyCart.removeChild(divError);
                            }
                        },4000);
                    }
                });
            }
        });
    });
})();

(()=>{
    document.querySelectorAll("#carritoModal .carritoBody .producto button[name='deleteOfCart']").forEach(button => {
        button.addEventListener("click", async(e)=>{
            e.preventDefault();
            deleteOfCart(button)
            .then(data => {
                if(data.success){
                    const procesarVenta=document.querySelector("#formConfirmarVenta");
                    if(procesarVenta!=null){
                        window.location.reload(true);
                    }else{
                        const titulo=document.querySelector("#carritoModal .carritoHeader .offcanvas-title");
                        titulo.textContent="Carrito de Venta ("+(parseInt(titulo.textContent.replace("Carrito de Venta (","").replace(")",""))-1)+")";
                        const btnCarrito=document.querySelector("#btn-carrito span");
                        btnCarrito.textContent=parseInt(btnCarrito.textContent)-1;
                        const subtotalV=button.closest("#carritoModal").querySelector("#subtotalVenta");
                        const totalV=button.closest("#carritoModal").querySelector("#totalVenta");
                        button.closest(".carritoBody").removeChild(button.closest(".producto"));
                        subtotalV.textContent="$"+data.subtotalV;
                        totalV.textContent="$"+data.totalV;
                        const modInps=document.querySelectorAll("#carritoModal .producto input.modified");
                        if(modInps.length==0){
                            const btnR=document.querySelector("#btnRefreshCart");
                            btnR.classList.remove("d-block");
                            btnR.classList.add("d-none");
                        }
                    }
                }else{
                    throw new Error(data.Error);
                }
                })
            .catch(error => {
                const parser = new DOMParser();
                const newDoc=parser.parseFromString(txtError,"text/html");
                const divError = newDoc.body.firstChild;
                divError.querySelector("span").textContent=error;
                const bodyCart=document.querySelector("#carritoModal .carritoBody");
                if(bodyCart.querySelector(".mensaje-error")!=null){
                    bodyCart.replaceChild(divError,bodyCart.firstChild);
                    setTimeout(()=>{
                        if(bodyCart.hasChildNodes(divError)){
                            bodyCart.removeChild(divError);
                        }
                    },4000);
                }else{
                    bodyCart.insertBefore(divError, bodyCart.firstChild);
                    setTimeout(()=>{
                        if(bodyCart.hasChildNodes(divError)){
                            bodyCart.removeChild(divError);
                        }
                    },4000);
                }
            });
        });
    });
})();

(()=>{
    window.addEventListener("load",()=>{
        const divMensaje=document.querySelector(".mensajeDiv");
        if(divMensaje!=null){
            if(divMensaje.classList.contains("valid") || divMensaje.classList.contains("invalid")){
                setTimeout(()=>{
                    divMensaje.classList.remove("valid");
                    divMensaje.classList.remove("invalid");
                    divMensaje.firstElementChild.textContent="";
                },7000);
            }
        }
    });
})();

(()=>{
    document.querySelectorAll("input[type='submit']").forEach(input=>{
        input.addEventListener("click",()=>{
            mostrarCarga();
        });
    });
})();
(()=>{
    document.querySelectorAll("button[type='submit']").forEach(input=>{
        input.addEventListener("click",()=>{
            mostrarCarga();
        });
    });
})();

(()=>{
    window.addEventListener("load",()=>{
        const btnsCollapsed=document.querySelectorAll("button[data-bs-toggle='collapse']");
        if(btnsCollapsed!=null){
            for (const btnColl of btnsCollapsed) {
                const collapseDiv=document.querySelector(btnColl.dataset.bsTarget);
                if(collapseDiv!=null && btnColl.firstElementChild!=null && btnColl.firstElementChild.src!=null){
                    btnColl.addEventListener("click",()=>{
                        setTimeout(()=>{
                            if(collapseDiv.classList.contains("show")){
                                btnColl.firstElementChild.src=btnColl.firstElementChild.src.replace("down","up");
                            }else{
                                btnColl.firstElementChild.src=btnColl.firstElementChild.src.replace("up","down");
                            }
                        },355);
                    });
                }
            }
        }
    });
})();

(()=>{
    const invAdmins=document.querySelectorAll("[name='invalid-passAdmin']");
    if(invAdmins.length>0){
        for(const invAdmin of invAdmins){
            const btnSpan=invAdmin.querySelector("span");
            const validCaracteres=invAdmin.querySelector("[name='validEspecialsPass']");
            if(validCaracteres!=null && btnSpan!=null){
                btnSpan.addEventListener("click",()=>{
                    if(validCaracteres.classList.contains("d-none")){
                        validCaracteres.classList.remove("d-none");
                    }else{
                        validCaracteres.classList.add("d-none");
                    }
                })
            }
        }
    }
})();

(()=>{
    const showMorePend=document.querySelector("#divShowMasPendientes h5");
    if(showMorePend!=null){
        showMorePend.addEventListener("click",()=>{
            const offset=showMorePend.parentElement.parentElement.childElementCount-1;
            getMorePendientes(offset)
            .then(data => {
                if(data.success){
                    const pendientes=data.pendientes;
                    for(const venta of pendientes){
                        const parser = new DOMParser();
                        const newDoc=parser.parseFromString(venta,"text/html");
                        const vPendiente = newDoc.body.firstChild;
                        showMorePend.parentElement.before(vPendiente);
                    }
                    if((showMorePend.parentElement.parentElement.childElementCount-1) == data.totalPendientes){
                        showMorePend.parentElement.parentElement.removeChild(showMorePend.parentElement);
                    }
                }else{
                    throw new Error(data.Error);
                }
            })
            .catch(error => {
                const divMensaje=document.querySelector(".mensajeDiv");
                if(!divMensaje!=null){
                    const spanMensaje=divMensaje.querySelector("span");
                    if(spanMensaje!=null){
                        spanMensaje.textContent=error;
                        divMensaje.classList.add("invalid");
                    }
                    window.setTimeout(()=>{
                        if(spanMensaje!=null){
                            divMensaje.classList.remove("invalid");
                            spanMensaje.textContent="";
                        }
                    },5000);
                }
            });
        });
    }
})();