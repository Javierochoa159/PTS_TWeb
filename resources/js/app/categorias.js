import minus from '@/../assets/icons/minus.svg';
import plus from '@/../assets/icons/plus.svg';
(()=>{
    const imgsCatego=document.querySelectorAll(".img-catego");
    for(const imgCatego of imgsCatego){
        imgCatego.addEventListener("click",()=>{
            setTimeout(()=>{
                const collapseCatego=imgCatego.closest(".boton-catego").nextElementSibling;
                if(collapseCatego.classList.contains("show")){
                    imgCatego.children[0].setAttribute("src",minus);
                    imgCatego.children[0].setAttribute("title","Ocultar");
                    imgCatego.children[0].setAttribute("alt","Ocultar");
                }else{
                    imgCatego.children[0].setAttribute("src",plus);
                    imgCatego.children[0].setAttribute("title","Mostrar");
                    imgCatego.children[0].setAttribute("alt","Mostrar");
                }
            },375)
        });
    }
})();