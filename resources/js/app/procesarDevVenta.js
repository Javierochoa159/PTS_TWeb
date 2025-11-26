import Decimal from "decimal.js";

(()=>{
    const checkBoxs = document.querySelectorAll(".productoTr input[type='checkbox']")
    for(const checkB of checkBoxs){
        checkB.addEventListener("change",()=>{
            const totalProds=document.querySelector("#totalDevProductos");
            const totalDev=document.querySelector("#totalDevVenta");
            const inpsDiv=checkB.closest(".productoTr").querySelectorAll(".devVentDiv");
            if(checkB.checked && totalDev!=null && totalProds!=null){
                totalDev.closest(".devVentDiv").classList.add("d-flex");
                totalProds.closest(".devVentDiv").classList.add("d-flex");
                totalDev.closest(".devVentDiv").classList.remove("d-none");
                totalProds.closest(".devVentDiv").classList.remove("d-none");
                for(const div of inpsDiv){
                    div.classList.add("d-flex");
                    div.classList.remove("d-none");
                }
                const cantProd=checkB.closest(".productoTr").querySelector(".cantDevProd");
                const checkChecks=document.querySelectorAll("input[type='checkbox']:checked");
                if(checkChecks!=null && cantProd!=null){
                    totalProds.textContent=checkChecks.length;
                    sumarProducto(cantProd,totalDev);
                }
            }else{
                const checkChecks=document.querySelectorAll("input[type='checkbox']:checked");
                if(checkChecks!=null && totalDev!=null && totalProds!=null){
                    const cantProd=checkB.closest(".productoTr").querySelector(".cantDevProd");
                    restarProducto(cantProd,totalDev);
                    if(checkChecks.length==0){
                        totalDev.closest(".devVentDiv").classList.remove("d-flex");
                        totalProds.closest(".devVentDiv").classList.remove("d-flex");
                        totalDev.closest(".devVentDiv").classList.add("d-none");
                        totalProds.closest(".devVentDiv").classList.add("d-none");
                    }
                    totalProds.textContent=checkChecks.length;
                }
                for(const div of inpsDiv){
                    div.classList.add("d-none");
                    div.classList.remove("d-flex");
                }
            }
        });
    }
})();

(()=>{
    const inptCants=document.querySelectorAll(".productoTr input.cantDevProd");
    const totalDev=document.querySelector("#totalDevVenta");
    if(inptCants!=null && totalDev!=null){
        for(const input of inptCants){
            input.addEventListener("input",()=>{
                if(input.value!="" && !input.value.startsWith(".") && !input.value.startsWith(",")){
                    actualizarTotales(input,totalDev);
                }
            });
            input.addEventListener("change",()=>{
                actualizarTotales(input,totalDev);
            });
            input.addEventListener("keydown",(e)=>{
                if (e.key === "Enter") {
                    e.preventDefault();
                    e.stopPropagation();
                    if(input.value.startsWith(".") || input.value.startsWith(",")){
                        input.value="0"+input.value;
                    }
                    if(input.value.trim()==""){
                        input.value="0";
                    }
                    actualizarTotales(input,totalDev);
                }
            });
        }
    }
})();

(()=>{
    const limpiarDevVenta = document.querySelector("#btnReiniciarDevolucion");
    if(limpiarDevVenta!=null){
        limpiarDevVenta.addEventListener("click",()=>{
            setTimeout(()=>{
                const selects=limpiarDevVenta.closest("form").querySelectorAll("select.form-control");
                for(const select of selects){
                    const optSelected=select.querySelector("button selectedcontent");
                    if(optSelected!=null){
                        optSelected.textContent=select.querySelector("option[selected]").textContent;
                    }
                }
                const checkboxs=limpiarDevVenta.closest("form").querySelectorAll("input[type='checkbox']");
                if(checkboxs!=null){
                    for(const checkB of checkboxs){
                        const inpCheck=checkB.closest(".productoTr").querySelector("input.cantDevProd");
                        if(inpCheck!=null){
                            actualizarTotales(inpCheck);
                        }
                        const inpsDiv=checkB.closest(".productoTr").querySelectorAll(".devVentDiv");
                        if(checkB.checked){
                            for(const div of inpsDiv){
                                div.classList.add("d-flex");
                                div.classList.remove("d-none");
                            }
                        }else{
                            for(const div of inpsDiv){
                                div.classList.add("d-none");
                                div.classList.remove("d-flex");
                            }
                        }
                    }
                }
                const checkChecks=limpiarDevVenta.closest("form").querySelectorAll("input[type='checkbox']:checked");
                const totalProds=limpiarDevVenta.closest("form").querySelector("#totalDevProductos");
                const totalDev=limpiarDevVenta.closest("form").querySelector("#totalDevVenta");
                if(checkChecks!=null && totalDev!=null && totalProds!=null){
                    totalDev.textContent="$0,0000";
                    if(checkChecks.length>0){
                        for(const check of checkChecks){
                            const inpCheck=check.closest(".productoTr").querySelector("input.cantDevProd");
                            sumarProducto(inpCheck,totalDev);  
                        }
                        totalProds.closest(".devVentDiv").classList.add("d-flex");
                        totalProds.closest(".devVentDiv").classList.remove("d-none");
                        totalDev.closest(".devVentDiv").classList.add("d-flex");
                        totalDev.closest(".devVentDiv").classList.remove("d-none");
                    }else{
                        totalProds.closest(".devVentDiv").classList.add("d-none");
                        totalProds.closest(".devVentDiv").classList.remove("d-flex");
                        totalDev.closest(".devVentDiv").classList.add("d-none");
                        totalDev.closest(".devVentDiv").classList.remove("d-flex");
                    }
                }
                const prevFotos = limpiarDevVenta.closest("form").querySelector(".fotos-newVenta .previewFotos");
                while(prevFotos.childElementCount>0){
                    prevFotos.lastElementChild.click();
                }
            },50);
        });
    }
})();

function actualizarTotales(input, total=null) {
    const pPrecioV = input.closest(".productoTr").querySelector(".precioVDevProd");
    const pTotalDevProd = input.closest(".productoTr").querySelector(".totalDevProd");
    const pTotalProd = input.closest(".productoTr").querySelector(".totalProd");
    const pCantProd = input.closest(".productoTr").querySelector(".cantProd");
    const medida = input.closest(".productoTr").querySelector(".medidaProd");
    const precioV = new Decimal(pPrecioV.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const oldprecioVT = new Decimal(pTotalDevProd.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const oldCantidad = new Decimal(pCantProd.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const oldTotalProd = new Decimal(pTotalProd.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const min = new Decimal(input.min || "0");
    const max = new Decimal(input.max || "0");
    let cantidad = new Decimal(input.value || "0");
    if(medida.textContent.trim()=="Unidad"){
        cantidad=new Decimal(cantidad.toFixed(0));
        input.value=cantidad.toFixed(0);
    }
    if(cantidad.lessThan(min)){
        cantidad = min;
        let aux=new Decimal(cantidad.toFixed(0));
        if(aux.lessThan(cantidad)){
            input.value=cantidad.toFixed(4);
        }else{
            input.value=cantidad.toFixed(0);
        }
    }else{
        if(cantidad.greaterThan(max)){
            cantidad = max;
            let aux=new Decimal(cantidad.toFixed(0));
            if(aux.lessThan(cantidad)){
                input.value=cantidad.toFixed(4);
            }else{
                input.value=cantidad.toFixed(0);
            }
        }
    }
    const fmt = new Intl.NumberFormat('es-AR', {minimumFractionDigits: 4,maximumFractionDigits: 4});
    if(cantidad.equals(oldCantidad)){
        pTotalDevProd.textContent = "$"+fmt.format(oldTotalProd.toFixed(4));
        if(total!=null){
            let oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
            
            let newTotal = oldTotal.minus(oldprecioVT).toDecimalPlaces(4);
            newTotal = newTotal.plus(oldTotalProd).toDecimalPlaces(4);
        
            total.textContent = "";
            total.textContent = "$"+fmt.format(newTotal.toFixed(4));
        }
    }else{
        const newPrecioVT = precioV.mul(cantidad).toDecimalPlaces(4);
        pTotalDevProd.textContent = "$"+fmt.format(newPrecioVT.toFixed(4));
        
        if(total!=null){
            let oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
        
            let newTotal = oldTotal.minus(oldprecioVT).toDecimalPlaces(4);
            newTotal = newTotal.plus(newPrecioVT).toDecimalPlaces(4);
        
            total.textContent = "";
            total.textContent = "$"+fmt.format(newTotal.toFixed(4));
        }
    }
}

function sumarProducto(input,total){
    const pTotalV = input.closest(".productoTr").querySelector(".totalDevProd");
    const precV = input.closest(".productoTr").querySelector(".precioVDevProd");
    const pCantProd = input.closest(".productoTr").querySelector(".cantProd");
    const precioVT = new Decimal(pTotalV.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const precioV = new Decimal(precV.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const oldCantidad = new Decimal(pCantProd.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const fmt = new Intl.NumberFormat('es-AR', {minimumFractionDigits: 4,maximumFractionDigits: 4});
    let cantidad = new Decimal(input.value || "0");
    if(cantidad.equals(oldCantidad)){
        let oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    
        const newTotal = oldTotal.plus(precioVT).toDecimalPlaces(4);
    
        total.textContent = "";
        total.textContent = "$"+fmt.format(newTotal.toFixed(4));
    }else{
        const newPrecioVT = precioV.mul(cantidad).toDecimalPlaces(4);
        pTotalV.textContent = "$"+fmt.format(newPrecioVT.toFixed(4));

        const oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");

        const newTotal = oldTotal.plus(newPrecioVT).toDecimalPlaces(4);
    
        total.textContent = "";
        total.textContent = "$"+fmt.format(newTotal.toFixed(4));
    }

}
function restarProducto(input,total){
    const pTotalV = input.closest(".productoTr").querySelector(".totalDevProd");
    const precioVT = new Decimal(pTotalV.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");
    const fmt = new Intl.NumberFormat('es-AR', {minimumFractionDigits: 4,maximumFractionDigits: 4});
    
    let oldTotal = new Decimal(total.textContent.replace("$","").replace(/\./g, '').replace(",",".").trim() || "0");

    const newTotal = oldTotal.minus(precioVT).toDecimalPlaces(4);

    total.textContent = "";
    total.textContent = "$"+fmt.format(newTotal.toFixed(4));
}

(()=>{
    window.addEventListener("load",()=>{
        const checkChecks=document.querySelectorAll("input[type='checkbox']:checked");
        const totalProds=document.querySelector("#totalDevProductos");
        const totalDev=document.querySelector("#totalDevVenta");
        if(checkChecks!=null && totalProds!=null && totalDev!=null){
            if(checkChecks.length>0){
                totalProds.closest(".devVentDiv").classList.remove("d-none");
                totalProds.closest(".devVentDiv").classList.add("d-flex");
                totalDev.closest(".devVentDiv").classList.remove("d-none");
                totalDev.closest(".devVentDiv").classList.add("d-flex");
                totalProds.textContent=checkChecks.length;
                for(const check of checkChecks){
                    const cantProd=check.closest(".productoTr").querySelector("input.cantDevProd");
                    const inpsDiv=check.closest(".productoTr").querySelectorAll(".devVentDiv");
                    if(cantProd!=null && inpsDiv!=null){
                        for(const div of inpsDiv){
                            div.classList.add("d-flex");
                            div.classList.remove("d-none");
                        }
                        sumarProducto(cantProd,totalDev);
                    }
                }
            }
        }
    });
})();