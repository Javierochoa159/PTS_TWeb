(()=>{
    document.addEventListener("click", function (event) {
        const collapsesRecibos = document.querySelectorAll(".collapse-horizontal");
        if(collapsesRecibos!=null){
            collapsesRecibos.forEach(collapseEl=>{
                const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                if (bsCollapse && collapseEl.classList.contains("show")) {
                    if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#'+collapseEl.id+'"]').contains(event.target)) {
                        bsCollapse.hide();
                    }
                }
            });
        }
    });
})();
(()=>{
    const btnsEditComp=document.querySelectorAll(".editProducto");
    if(btnsEditComp!=null){
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
    }
})();
(()=>{
    const btnsDeleteProd=document.querySelectorAll(".deleteProducto");
    if(btnsDeleteProd!=null){
        btnsDeleteProd.forEach(btn => {
            btn.addEventListener("click",async(e)=>{
                e.preventDefault();
                deleteProdCompra(btn.dataset.prod)
                .then(data=>{
                    if (data.success) {
                        const totalComp=btn.closest("form").querySelector("#totalCompra");
                        const totalProds=btn.closest("form").querySelector("#totalProductos");
                        const trProd=btn.closest(".productoTr");
                        const carritoCompra=document.querySelector("#btn-carritoCompra");
                        if(totalComp!=null && totalProds!=null && trProd!=null && carritoCompra!=null){
                            if(data.totalProds==0){
                                const provComp=btn.closest("form").querySelector("#proveedorCompra");
                                const fotosDiv=btn.closest("form").querySelector("#div-fotos-newCompra");
                                const btnAddMoreProds=btn.closest("form").querySelector("#addMoreProds");
                                const confirmCompra=btn.closest("form").querySelector("#btnConfirmarCompra");
                                const fechaCompra=btn.closest("form").querySelector("#fechaCompra-newCompra");
                                if(provComp!=null && fotosDiv!=null && btnAddMoreProds!=null && confirmCompra!=null && fechaCompra!=null){
                                    provComp.textContent="";
                                    provComp.removeAttribute("id");
                                    fotosDiv.parentElement.removeChild(fotosDiv);
                                    btnAddMoreProds.parentElement.parentElement.removeChild(btnAddMoreProds.parentElement);
                                    confirmCompra.parentElement.parentElement.removeChild(confirmCompra.parentElement);
                                    carritoCompra.parentElement.removeChild(carritoCompra);
                                    if(fechaCompra.nextElementSibling!=null){
                                        fechaCompra.parentElement.removeChild(fechaCompra.nextElementSibling);
                                    }
                                    var p=document.createElement("p");
                                    p.textContent="--/--/---- --:--";
                                    p.classList.value="col-6 p-0 m-0";
                                    fechaCompra.parentNode.replaceChild(p, fechaCompra);
                                    trProd.parentElement.parentElement.removeChild(trProd.parentElement);
                                }else{
                                    window.location.reload(true);
                                }
                            }else{
                                carritoCompra.firstElementChild.textContent=data.totalProds;
                                trProd.parentElement.removeChild(trProd);
                            }
                            totalComp.textContent="$"+data.totalCompra;
                            totalProds.textContent=data.totalProds;
                        }else{
                            window.location.reload(true);
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
    }
})();

(()=>{
    const firstInvalid=document.querySelector(".invalid-feedback.is-invalid");
    if(firstInvalid!=null){
        window.addEventListener("load",()=>{
            const btnConfCompra=document.querySelector("#btnConfirmarCompra");
            if(btnConfCompra!=null){
                btnConfCompra.focus();
            }
            const previewFotos=document.querySelector("#formConfirmarCompra .previewFotos");
            if(previewFotos!=null && previewFotos.childElementCount>0){
                for (let foto of previewFotos.children) {
                    foto.addEventListener("click", () => deleteFoto(foto));
                }
            }
        });
    }
    function deleteFoto(foto){
        const optFoto = foto.parentElement.previousElementSibling.querySelector("option[data-foto='"+foto.id+"']");
        const select = foto.parentElement;
        optFoto.remove();
        foto.remove();
        if(select.childElementCount<4){
            select.classList.remove("masTres");
        }
    }
})();

(()=>{
    document.addEventListener("click", function (event) {
        const collapseEl = document.querySelector("#collapse-filtroFecha");
        if(collapseEl!=null){
            const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
            if (bsCollapse!=null && collapseEl.classList.contains("show")) {
                if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#collapse-filtroFecha"]').contains(event.target)) {
                    bsCollapse.hide();
                }
            }
        }
    });
})();
