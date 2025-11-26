(()=>{
    const dropAreas = document.querySelectorAll(".drop-area");
    for(const dropArea of dropAreas){
        const dragText = dropArea.querySelector("h2");
        const button = dropArea.querySelector("button");
        const span = dropArea.querySelector("span");
        const input = dropArea.querySelector(".inputFotos")

        let files;
    
        button.addEventListener("click",(e)=>{
            e.preventDefault();
            input.click();
        });
    
        input.addEventListener("change",async()=>{
            files = input.files;
            dropArea.classList.add("active");
            let result = await showFiles(files);
            procesarResult(result);
        });

        function procesarResult(result){
            if(result["error"]!=null){
                let invFot=dropArea.querySelector(".invalid-feedback");
                let oldClass=invFot.classList.value;
                let oldMess=invFot.textContent;
                invFot.classList.add('is-invalid');
                invFot.classList.add('d-block');
                invFot.textContent = result["error"];
                setTimeout(()=>{
                    invFot.classList.value=oldClass;
                    invFot.textContent = oldMess;
                },4000);
            }else{
                const preview=dropArea.nextElementSibling;
                const prevCount=preview.childElementCount;
                const select = dropArea.querySelector("input").nextElementSibling;
                const imgsCount = result["imgs"].length;
                let i=0;
                while((i<imgsCount) && ((i+prevCount)<5)){
                    const img = result["imgs"][i];
                    preview.appendChild(img);
                    let newOption=document.createElement("option");
                    newOption.setAttribute("data-foto",img.id);
                    newOption.setAttribute("value",img.src);
                    newOption.selected=true;
                    select.appendChild(newOption);
                    if(preview.childElementCount>3){
                        preview.classList.add("masTres");
                    }
                    i++;
                }
                if(i<imgsCount){
                    let invFot=dropArea.querySelector(".invalid-feedback");
                    let oldClass=invFot.classList.value;
                    let oldMess=invFot.textContent;
                    invFot.classList.add('is-invalid');
                    invFot.classList.add('d-block');
                    invFot.textContent = "Maximo 5 imágenes";
                    setTimeout(()=>{
                        invFot.classList.value=oldClass;
                        invFot.textContent = oldMess;
                    },4000);
                }
            }
            dropArea.classList.remove("active");
        }
    
        dropArea.addEventListener("dragover",(e)=>{
            e.preventDefault();
            dropArea.classList.remove("justify-content-center");
            dropArea.classList.add(["active","justify-content-start","mt-2"]);
            button.classList.add("d-none");
            span.classList.add("d-none");
            let invFot=dropArea.querySelector(".invalid-feedback");
            invFot.classList.add('d-none');
            dragText.classList.add("d-none");
        });
        
        dropArea.addEventListener("dragleave",(e)=>{
            e.preventDefault();
            dropArea.classList.add("justify-content-center");
            dropArea.classList.remove(["active","justify-content-start","mt-2"]);
            button.classList.remove("d-none");
            span.classList.remove("d-none");
            let invFot=dropArea.querySelector(".invalid-feedback");
            invFot.classList.remove('d-none');
            dragText.classList.remove("d-none");
        });
        
        dropArea.addEventListener("drop",async(e)=>{
            e.preventDefault();
            files = e.dataTransfer.files;
            let result = await showFiles(files);
            procesarResult(result);
            dropArea.classList.add("justify-content-center");
            dropArea.classList.remove(["active","justify-content-start","mt-2"]);
            button.classList.remove("d-none");
            span.classList.remove("d-none");
            let invFot=dropArea.querySelector(".invalid-feedback");
            invFot.classList.remove('d-none');
            dragText.classList.remove("d-none");
        });
    }
    
    async function showFiles(files){
        let response={"imgs":[],"error":null};
        if(files.length === undefined){
            try{
                let result = await processFile(files);
                response["imgs"].push(result);
            }catch(err){
                response["error"]=err;
            }
        }else{
            for(const file of files){
                try{
                    let result = await processFile(file);
                    response["imgs"].push(result);
                }catch(err){
                    response["error"]=err;
                }
            }
        }
        return response;
    }
    
    function processFile(file){
        return new Promise((resolve,reject) => {
            const docType = file.type;
            const validExtensions = ["image/png","image/jpeg","image/jpg"];
            if(validExtensions.includes(docType)){
                const fileReader = new FileReader();
                fileReader.addEventListener("load",()=>{
                    const fileUrl = fileReader.result;
                    let img=new Image();
                    img.id=Math.random().toString(32).substring(7);
                    img.src=fileUrl;
                    img.alt=img.id;
                    img.setAttribute("title","Eliminar");
                    img.setAttribute("class","img-fluid my-2 me-2");
                    img.addEventListener("click",()=>deleteFoto(img));
                    resolve(img);
                });
                fileReader.readAsDataURL(file);    
            }else{
                reject("Ingrese imágenes válidas");
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