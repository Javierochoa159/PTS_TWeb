(()=>{
    const btnCV=document.querySelector("#btnConfirmarVenta");
    const btnCMV=document.querySelector("#btnModificarVenta");
    const btnCDV=document.querySelector("#btnConfirmacionDevolucion");
    
    if(btnCV!=null){
        const tipoV=document.querySelector("#tipoVenta-newVenta");
        const metodoP=document.querySelector("#metodoPago-newVenta");
        const textarea=document.querySelector("table textarea");
        const checkbox=document.querySelector("table input[type='checkbox']");
        const inputs=document.querySelectorAll("table input.form-control");
        if(tipoV!=null && textarea!=null && inputs.length>0 && checkbox!=null && metodoP!=null){
            if(metodoP!=null){
                const disFotos=metodoP.closest("form").querySelector(".disableFotos");
                if(disFotos!=null){
                    metodoP.addEventListener("change",()=>{
                        if(metodoP.value.trim()=="Pendiente"){
                            disFotos.classList.remove("d-none");
                        }else{
                            disFotos.classList.add("d-none");
                        }
                    });
                    if(metodoP.value.trim()=="Pendiente"){
                        disFotos.classList.remove("d-none");
                    }else{
                        disFotos.classList.add("d-none");
                    }
                }
            }
            checkbox.addEventListener("change",()=>{
                if(checkbox.checked){
                    const optP=metodoP.querySelector("option[value='Pendiente']");
                    if(optP!=null){
                        optP.disabled=true;
                    }
                    if(metodoP.value.trim()=="Pendiente"){
                        optP.selected=false;
                        const firstOpt=metodoP.querySelector("option[value='Tarjeta']");
                        const disFotos=metodoP.closest("form").querySelector(".disableFotos");
                        if(firstOpt!=null && disFotos!=null){
                            firstOpt.selected=true;
                            disFotos.classList.add("d-none");
                        }
                    }
                }else{
                    const optP=metodoP.querySelector("option[value='Pendiente']");
                    if(optP!=null){
                        optP.disabled=false;
                    }
                }
            });
            tipoV.addEventListener("change",()=>{
                if(tipoV.value.trim()=="Local"){
                    textarea.disabled=true;
                    checkbox.disabled=false;
                    for(const input of inputs){
                        if(!input.id.trim().includes("receptor") && !input.id.trim().includes("contacto")){
                            input.disabled=true;
                        }
                    }
                }else{
                    textarea.disabled=false;
                    checkbox.checked=false;
                    checkbox.disabled=true;
                    for(const input of inputs){
                        input.disabled=false;
                    }
                    const optP=metodoP.querySelector("option[value='Pendiente']");
                    if(optP!=null){
                        optP.disabled=false;
                    }
                }
            })
            checkbox.addEventListener("change",()=>{
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
            });
        }
        for(const input of inputs){
            input.addEventListener("keydown",(e)=>{
                if(e.key === 'Enter'){
                    e.preventDefault();
                }
            });
        }
    }
    if(btnCMV!=null){
        const tipoV=document.querySelector("#tipoVenta-newVenta");
        const textarea=document.querySelector("table textarea");
        const checkbox=document.querySelector("table input[type='checkbox']");
        const inputs=document.querySelectorAll("table input.form-control");
        if(tipoV!=null && textarea!=null && inputs.length>0 && checkbox!=null){
            const confirmarP=document.querySelector("#confirmarPago-newVenta");
            const metodoP=document.querySelector("#metodoPago-newVenta");
            if(confirmarP!=null && metodoP!=null){
                const disFotos=confirmarP.closest("form").querySelector(".disableFotos");
                if(disFotos!=null){
                    confirmarP.addEventListener("change",()=>{
                        if(confirmarP.checked){
                            disFotos.classList.add("d-none");
                            metodoP.disabled=false;
                        }else{
                            disFotos.classList.remove("d-none");
                            metodoP.disabled=true;
                        }
                    });
                    checkbox.addEventListener("change",()=>{
                        if(checkbox.checked){
                            confirmarP.checked=true;
                            confirmarP.hidden=true;
                            metodoP.disabled=false;
                            disFotos.classList.add("d-none");
                        }else{
                            confirmarP.hidden=false;
                        }
                    });
                }
            }
            tipoV.addEventListener("change",()=>{
                if(tipoV.value.trim()=="Local"){
                    textarea.disabled=true;
                    checkbox.disabled=false;
                    for(const input of inputs){
                        if(!input.id.trim().includes("receptor") && !input.id.trim().includes("contacto")){
                            input.disabled=true;
                        }
                    }
                }else{
                    textarea.disabled=false;
                    checkbox.checked=false;
                    checkbox.disabled=true;
                    for(const input of inputs){
                        input.disabled=false;
                    }
    
                }
            })
            checkbox.addEventListener("change",()=>{
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
            });
        }
        for(const input of inputs){
            input.addEventListener("keydown",(e)=>{
                if(e.key === 'Enter'){
                    e.preventDefault();
                }
            });
        }
    }
})();

(()=>{
    const fechaMin=document.querySelector("#fechaEntregaMin-newVenta");
    const fechaMax=document.querySelector("#fechaEntregaMax-newVenta");
    if(fechaMax!=null && fechaMin){
        fechaMax.addEventListener("change",()=>{
            controlFecha(fechaMax);
            fechaMin.setAttribute("max",fechaMax.value);
        });
        fechaMin.addEventListener("change",()=>{
            controlFecha(fechaMin);
            fechaMax.setAttribute("min",fechaMin.value);
        });
        window.addEventListener("load",()=>{
            controlFecha(fechaMin);
            controlFecha(fechaMax);
        });
    }
    function getFormato(dato){
        if(dato<10){
            return "0"+dato;
        }else{
            return dato;
        }
    }
    function controlFecha(fechaInp){
        const nowF=new Date();
        const fecha=new Date(fechaInp.value);
        const fecha_min=new Date(fechaInp.min);
        const fecha_max=new Date(fechaInp.max);
        if(fechaInp!=null){
            if(fecha.getHours()<7 ||(fecha.getHours()==7 && fecha.getMinutes()<30)){
                fecha.setHours(7,30);
            }else if(fecha.getHours()>20 ||(fecha.getHours()==20 && fecha.getMinutes()>30)){
                fecha.setHours(20,30);
            }

            if(fecha.getDate()==nowF.getDate() && fecha.getHours()<fecha_min.getHours()){
                fecha.setHours(fecha_min.getHours(),fecha_min.getMinutes());
            }else if(fecha.getDate()==nowF.getDate() && fecha.getHours()==fecha_min.getHours() && fecha.getMinutes()<fecha_min.getMinutes()){
                fecha.setMinutes(fecha_min.getMinutes());
            }
            
            if(fecha.getDate()==nowF.getDate() && fecha.getHours()>fecha_max.getHours()){
                fecha.setHours(fecha_max.getHours(),fecha_max.getMinutes());
            }else if(fecha.getDate()==nowF.getDate() && fecha.getHours()==fecha_max.getHours() && fecha.getMinutes()>fecha_max.getMinutes()){
                fecha.setMinutes(fecha_max.getMinutes());
            }

            if(fecha.getDate()==nowF.getDate() && (nowF.getHours()>18 || (nowF.getHours()>=18 && nowF.getMinutes()>30))){
                fecha.setDate(fecha.getDate()+1);
                fecha.setHours(7,30);
                fechaInp.setAttribute("min",fecha.getFullYear()+"-"+getFormato(fecha.getMonth()+1)+"-"+getFormato(fecha.getDate())+"T07:30");
            }
            
            fechaInp.value=fecha.getFullYear()+"-"+getFormato(fecha.getMonth()+1)+"-"+getFormato(fecha.getDate())+"T"+getFormato(fecha.getHours())+":"+getFormato(fecha.getMinutes());
        }
    }
})();

(()=>{
    window.addEventListener("load",()=>{
        const tipoVenta=document.querySelector("#tipoVenta-newVenta");
        if(tipoVenta!=null){
            if(tipoVenta.value.trim()=="Local"){
                const estadoE=document.querySelector("#estadoEntrega-newVenta");
                if(estadoE!=null){
                    if(estadoE.checked){
                        const confirmarP=document.querySelector("#confirmarPago-newVenta");
                        if(confirmarP!=null){
                            confirmarP.hidden=true;
                        }
                    }
                }
                const inps=tipoVenta.closest("form").querySelectorAll("input.form-control");
                for(const inp of inps){
                    if(!inp.id.trim().includes("receptor") && !inp.id.trim().includes("contacto")){
                        inp.disabled=true;
                    }
                }
                const textArea=tipoVenta.closest("form").querySelector("textarea");
                if(textArea!=null){
                    textArea.disabled=true;
                }
                const checkBox=tipoVenta.closest("form").querySelector("input[type='checkbox']");
                if(checkBox!=null){
                    checkBox.disabled=false;
                }
            }
        }
        const confirmarP=document.querySelector("#confirmarPago-newVenta");
        if(confirmarP!=null){
            if(confirmarP.checked){
                const metodoP=confirmarP.closest("form").querySelector("#metodoPago-newVenta");
                const disFotos=confirmarP.closest("form").querySelector(".disableFotos");
                if(metodoP!=null && disFotos!=null){
                    metodoP.disabled=false;
                    disFotos.classList.add("d-none");
                }
            }
        }
    });
})();

