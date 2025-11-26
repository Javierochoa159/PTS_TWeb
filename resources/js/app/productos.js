(()=>{
    const txtCategos=document.querySelectorAll(".categorias-buscar .txt-catego");
    for(const txtCatego of txtCategos){
        txtCatego.addEventListener("click",()=>{
            const inputId = txtCatego.closest(".categorias").nextElementSibling;
            if(!inputId.hasAttribute("disabled")){
                const oldSelected=txtCatego.closest(".categorias").querySelector("[data-id='"+inputId.value+"']");
                if(oldSelected)oldSelected.classList.remove("selected");
            }else{
                inputId.removeAttribute("disabled");
                txtCatego.closest(".categorias").querySelector(".raiz-catego").classList.remove("selected");
            }
            inputId.value=parseInt(txtCatego.dataset.id);
            txtCatego.classList.add("selected");
        });
    }
    const btnCategos=document.querySelectorAll(".categorias-buscar .btn-catego");
    for(const btnCatego of btnCategos){
        btnCatego.addEventListener("click",()=>{
            const inputId = btnCatego.closest(".categorias").nextElementSibling;
            if(!inputId.hasAttribute("disabled")){
                const oldSelected=btnCatego.closest(".categorias").querySelector("[data-id='"+inputId.value+"']");
                if(oldSelected)oldSelected.classList.remove("selected");
            }else{
                inputId.removeAttribute("disabled");
                btnCatego.closest(".categorias").querySelector(".raiz-catego").classList.remove("selected");
            }
            inputId.value=parseInt(btnCatego.dataset.id);
            btnCatego.classList.add("selected");
        });
    }
    const raizCatego=document.querySelector(".categorias-buscar .raiz-catego");
    if(raizCatego!=null){
        raizCatego.addEventListener("click",()=>{
            const inputId = raizCatego.closest(".categorias-buscar").querySelector("[name='catego']");
            if(!inputId.hasAttribute("disabled")){
                const oldSelected=raizCatego.closest(".categorias").querySelector("[data-id='"+inputId.value+"']");
                if(oldSelected)oldSelected.classList.remove("selected");
            }
            inputId.setAttribute("disabled","");
            raizCatego.classList.add("selected");
        });
    }
})();

(()=>{
    document.addEventListener("click", function (event) {
        const collapseEl = document.querySelector("#categoriasBuscar");
        const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
        if (bsCollapse && collapseEl.classList.contains("show")) {
            if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#categoriasBuscar"]').contains(event.target)) {
                bsCollapse.hide();
            }
        }
    });
})();

(()=>{
    setTimeout(()=>{
        const selectedCategos=document.querySelectorAll(".categorias .selected");
        if(selectedCategos.length==0){
            const raizCategos=document.querySelectorAll(".categorias .raiz-catego");
            for(const raiz of raizCategos){
                raiz.classList.add("selected");
            }
        }
    },50);
})();

(()=>{
    window.addEventListener("load",()=>{
        document.querySelectorAll("[name='orden']").forEach(btn=>{
            btn.addEventListener("click",()=>{
                mostrarCarga();
            });
        });
        document.querySelectorAll(".categorias [name='idCatego']").forEach(btn=>{
            btn.addEventListener("click",()=>{
                mostrarCarga();
            });
        });
        document.querySelectorAll(".categorias [name='catego']").forEach(btn=>{
            btn.addEventListener("click",()=>{
                mostrarCarga();
            });
        });
        const raiz=document.querySelector(".categorias a");
        if(raiz!=null){
            raiz.addEventListener("click",()=>{
                mostrarCarga();
            });
        }
        const btnBusPag=document.querySelector("button[name='pagina']");
        if(btnBusPag!=null){
            btnBusPag.addEventListener("click",()=>{
                mostrarCarga();
            });
        }
    });
})();