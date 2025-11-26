(()=>{
    const btnResEditComp=document.querySelector("#btnResetEditCompra");
    if(btnResEditComp!=null){
        btnResEditComp.addEventListener("click",()=>{
            const selects=btnResEditComp.closest("form").querySelectorAll("select.form-control");
            for(const select of selects){
                const optSelected=select.querySelector("button selectedcontent");
                if(optSelected!=null){
                    optSelected.textContent=select.querySelector("option[selected]").textContent;
                }
            }
            getOldsProdsEditCompra(btnResEditComp)
            .then(data=>{
                if(data.success){
                    const totalCompra=btnResEditComp.closest("form").querySelector("#totalCompra");
                    const tableProdsEditCompra=btnResEditComp.closest("form").querySelector("#tableProdsEditCompra");
                    if(tableProdsEditCompra==null && totalCompra!=null){
                        window.location.reload(true);
                    }else{
                        if(data.divProds!=null){
                            const parser = new DOMParser();
                            const newDoc=parser.parseFromString(data.divProds,"text/html");
                            const divProds = newDoc.body.querySelector("#tableProdsEditCompra");
                            tableProdsEditCompra.parentElement.replaceChild(divProds,tableProdsEditCompra);
                            const btnsEditComp=divProds.querySelectorAll(".editProducto");
                            if(btnsEditComp!=null){
                                totalCompra.textContent=data.oldTotalCompra;
                                btnsEditComp.forEach(btn => {
                                    btn.addEventListener("click",async(e)=>{
                                        e.preventDefault();
                                        editarCompra(btn.dataset.prod)
                                        .then(data=>{
                                            if (data.success && data.redirect) {
                                                const divMensaje=document.querySelector(".mensajeDiv");
                                                if(!divMensaje!=null){
                                                    divMensaje.firstElementChild.textContent="Redireccionando para editar la compra";
                                                    divMensaje.classList.remove("invalid");
                                                    divMensaje.classList.add("valid");
                                                    divMensaje.classList.add('d-block');
                                                    setTimeout(()=>{
                                                        window.location.href = data.redirect;
                                                    },1000);
                                                }
                                            }else{
                                                throw new Error(data.Error);
                                            }
                                        })
                                        .catch(error=>{
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
                                    });
                                });
                            }else{
                                window.location.reload(true);
                            }
                        }else{
                            window.location.reload(true);
                        }
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
        })
    }
})();