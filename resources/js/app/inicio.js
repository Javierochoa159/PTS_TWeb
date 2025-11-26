(()=>{
    const raizCatego=document.querySelector(".categorias-newCatego .raiz-catego");
    raizCatego.addEventListener("click",()=>{
        const inputNewCatego=raizCatego.closest(".categorias").parentElement.previousElementSibling.querySelector("input");
        if(inputNewCatego.value.trim()==""){
            const invErr=inputNewCatego.nextElementSibling;
            let oldClass=invErr.classList.value;
            let oldMess=invErr.textContent;
            invErr.classList.add('is-invalid');
            invErr.classList.add('d-block');
            invErr.textContent = "Ingrese el nombre de la Categoría";
            setTimeout(()=>{
                invErr.classList.value=oldClass;
                invErr.textContent = oldMess;
            },4000);
        }else{
            const inputId = raizCatego.closest(".categorias").nextElementSibling;
            inputId.value=0;
            const inputText = inputId.nextElementSibling;
            inputText.value="";
            inputText.value=inputNewCatego.value;
        }
    });

    const btnCategos=document.querySelectorAll(".categorias-newCatego .btn-catego");
    for(const btnCatego of btnCategos){
        btnCatego.addEventListener("click",()=>{
            const inputNewCatego=btnCatego.closest(".categorias").parentElement.previousElementSibling.querySelector("input");
            if(inputNewCatego.value.trim()==""){
                const invErr=inputNewCatego.nextElementSibling;
                let oldClass=invErr.classList.value;
                let oldMess=invErr.textContent;
                invErr.classList.add('is-invalid');
                invErr.classList.add('d-block');
                invErr.textContent = "Ingrese el nombre de la Categoría";
                setTimeout(()=>{
                    invErr.classList.value=oldClass;
                    invErr.textContent = oldMess;
                },4000);
            }else{
                let fin=false;
                let valueInput = inputNewCatego.value;
                if(btnCatego.closest(".catego").parentElement.classList.contains("collapse")){
                    let imgCatego = btnCatego.previousElementSibling;
                    while(!fin){
                        valueInput=imgCatego.nextElementSibling.textContent+"/"+valueInput;
                        if(imgCatego.closest(".catego").parentElement.classList.contains("collapse")){
                            imgCatego=imgCatego.closest(".catego").parentElement.previousElementSibling.children[0];
                        }else{
                            fin=true;
                        }
                    }
                }else{
                    valueInput = btnCatego.textContent+"/"+valueInput;
                }
                const inputId = btnCatego.closest(".categorias").nextElementSibling;
                inputId.value=btnCatego.dataset.id;
                const inputText = inputId.nextElementSibling;
                inputText.value="";
                inputText.value=valueInput;
            }
        });
    }

    const txtCategos=document.querySelectorAll(".categorias-newCatego .txt-catego");
    for(const txtCatego of txtCategos){
        txtCatego.addEventListener("click",()=>{
            const inputNewCatego=txtCatego.closest(".categorias").parentElement.previousElementSibling.querySelector("input");
            if(inputNewCatego.value.trim()==""){
                const invErr=inputNewCatego.nextElementSibling;
                let oldClass=invErr.classList.value;
                let oldMess=invErr.textContent;
                invErr.classList.add('is-invalid');
                invErr.classList.add('d-block');
                invErr.textContent = "Ingrese el nombre de la Categoría";
                setTimeout(()=>{
                    invErr.classList.value=oldClass;
                    invErr.textContent = oldMess;
                },4000);
            }else{
                let fin=false;
                let valueInput = txtCatego.textContent+"/"+inputNewCatego.value;
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
                inputId.value=txtCatego.dataset.id;
                const inputText = inputId.nextElementSibling;
                inputText.value="";
                inputText.value=valueInput;
            }
        });
    }
})();

(()=>{
    const txtCategos=document.querySelectorAll(".categorias-newProd .txt-catego");
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
    const btnCategos=document.querySelectorAll(".categorias-editCatego .btn-catego");
    for(const btnCatego of btnCategos){
        btnCatego.addEventListener("click",()=>{
            const inputEditCatego=btnCatego.closest(".categorias").parentElement.previousElementSibling.querySelector("input");
            if(inputEditCatego.value.trim()==""){
                const invErr=inputEditCatego.nextElementSibling;
                let oldClass=invErr.classList.value;
                let oldMess=invErr.textContent;
                invErr.classList.add('is-invalid');
                invErr.classList.add('d-block');
                invErr.textContent = "Ingrese el nuevo nombre de la Categoría";
                setTimeout(()=>{
                    invErr.classList.value=oldClass;
                    invErr.textContent = oldMess;
                },4000);
            }else{
                let fin=false;
                let valueInput = "";
                if(btnCatego.closest(".catego").parentElement.classList.contains("collapse")){
                    let imgCatego = btnCatego.previousElementSibling;
                    while(!fin){
                        valueInput=imgCatego.nextElementSibling.textContent+"/"+valueInput;
                        if(imgCatego.closest(".catego").parentElement.classList.contains("collapse")){
                            imgCatego=imgCatego.closest(".catego").parentElement.previousElementSibling.children[0];
                        }else{
                            fin=true;
                        }
                    }
                }else{
                    valueInput = btnCatego.textContent;
                }
                const inputId = btnCatego.closest(".categorias").nextElementSibling;
                inputId.value=btnCatego.dataset.id;
                const inputText = inputId.nextElementSibling;
                inputText.value="";
                inputText.value=valueInput;
            }
        });
    }

    const txtCategos=document.querySelectorAll(".categorias-editCatego .txt-catego");
    for(const txtCatego of txtCategos){
        txtCatego.addEventListener("click",()=>{
            const inputEditCatego=txtCatego.closest(".categorias").parentElement.previousElementSibling.querySelector("input");
            if(inputEditCatego.value.trim()==""){
                const invErr=inputEditCatego.nextElementSibling;
                let oldClass=invErr.classList.value;
                let oldMess=invErr.textContent;
                invErr.classList.add('is-invalid');
                invErr.classList.add('d-block');
                invErr.textContent = "Ingrese el nuevo nombre de la Categoría";
                setTimeout(()=>{
                    invErr.classList.value=oldClass;
                    invErr.textContent = oldMess;
                },4000);
            }else{
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
                inputId.value=txtCatego.dataset.id;
                const inputText = inputId.nextElementSibling;
                inputText.value="";
                inputText.value=valueInput;
            }
        });
    }
})();

(()=>{
    const btnCategos=document.querySelectorAll(".categorias-deleteCatego .btn-catego");
    for(const btnCatego of btnCategos){
        btnCatego.addEventListener("click",()=>{
            let fin=false;
            let valueInput = "";
            if(btnCatego.closest(".catego").parentElement.classList.contains("collapse")){
                let imgCatego = btnCatego.previousElementSibling;
                while(!fin){
                    valueInput=imgCatego.nextElementSibling.textContent+"/"+valueInput;
                    if(imgCatego.closest(".catego").parentElement.classList.contains("collapse")){
                        imgCatego=imgCatego.closest(".catego").parentElement.previousElementSibling.children[0];
                    }else{
                        fin=true;
                    }
                }
            }else{
                valueInput = btnCatego.textContent;
            }
            const inputId = btnCatego.closest(".categorias").nextElementSibling;
            inputId.value=btnCatego.dataset.id;
            const inputText = inputId.nextElementSibling;
            inputText.value="";
            inputText.value=valueInput;
        });
    }

    const txtCategos=document.querySelectorAll(".categorias-deleteCatego .txt-catego");
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
            inputId.value=txtCatego.dataset.id;
            const inputText = inputId.nextElementSibling;
            inputText.value="";
            inputText.value=valueInput;
        });
    }
})();

(()=>{
    const limpiarNewProd = document.querySelector("#btn-limpiar-newProd");
    limpiarNewProd.addEventListener("click",()=>{
        const prevFotos = limpiarNewProd.closest(".modal-content").querySelector("form .fotos-newProd .previewFotos");
        while(prevFotos.childElementCount>0){
            prevFotos.lastElementChild.click();
        }
    });
})();



(()=>{
    document.addEventListener("click", function (event) {
        const collapseEl = document.querySelector("#collapse-proveedores-newProd");
        const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
        if (bsCollapse && collapseEl.classList.contains("show")) {
            if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#collapse-proveedores-newProd"]').contains(event.target)) {
                bsCollapse.hide();
            }
        }
    });
})();