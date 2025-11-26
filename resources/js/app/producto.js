(()=>{
    const txtCategos=document.querySelectorAll(".categorias-editProd .txt-catego");
    for(const txtCatego of txtCategos){
        txtCatego.addEventListener("click",()=>{
            let fin=false;
            let valueInput = txtCatego.textContent;
            if(txtCatego.parentElement.classList.contains("collapse")){
                let imgCatego = txtCatego.parentElement.previousElementSibling.children[0];
                while(!fin){
                    valueInput=imgCatego.nextElementSibling.textContent+"/"+valueInput;
                    if(imgCatego.closest(".catego").parentElement.classList.contains("collapse")){
                        imgCatego=imgCatego.closest(".catego").parentElement.previousElementSibling.children[0];
                    }else{
                        fin=true;
                    }
                }
            }
            const inputId = txtCatego.closest(".categorias").nextElementSibling;
            inputId.value=parseInt(txtCatego.dataset.id);
            const inputText = inputId.nextElementSibling;
            inputText.value="";
            inputText.value=valueInput;
        });
    }
})();

(()=>{
    document.addEventListener("click", function (event) {
        const collapseEl = document.querySelector("#collapse-proveedores-editProd");
        const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
        if (bsCollapse && collapseEl.classList.contains("show")) {
            if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#collapse-proveedores-editProd"]').contains(event.target)) {
                bsCollapse.hide();
            }
        }
    });
})();

(()=>{
    document.querySelectorAll(".optProveedor-newCompra").forEach(opt=>{
        const oldPrecioC=document.querySelector("#oldPrecioCompra-newCompra");
        const newPrecioC=document.querySelector("#precioCompra-newCompra");
        if(oldPrecioC!=null && newPrecioC!=null){
            opt.addEventListener("click",()=>{
                oldPrecioC.textContent=opt.dataset.printp;
                if(opt.dataset.valuep==0){
                    newPrecioC.value="";
                }else{
                    newPrecioC.value=opt.dataset.valuep;
                }
            });
        }
    });
    window.addEventListener("load",()=>{
        const selectedProv=document.querySelector("#proveedor-newCompra option[selected]");
        if(selectedProv!=null){
            const oldPrecCompra=selectedProv.closest("form").querySelector("#oldPrecioCompra-newCompra");
            const newPrecCompra=selectedProv.closest("form").querySelector("#precioCompra-newCompra");
            if(oldPrecCompra!=null && newPrecCompra!=null){
                oldPrecCompra.textContent=selectedProv.dataset.printp;
                newPrecCompra.value=selectedProv.dataset.valuep;
            }
        }
    });
})();