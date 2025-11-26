(()=>{
    const swithVentas=document.querySelector(".switchVenta");
    if(swithVentas!=null){
        swithVentas.addEventListener("click",()=>{
            const tableO=document.querySelector(".originalVenta");
            const tableA=document.querySelector(".actualVenta");
            const trueTotalV=document.querySelector(".trueTotalV");
            const trueSubTotalV=document.querySelector(".trueSubTotalV");
            const trueCantV=document.querySelector(".trueCantV");
            const oldTotalV=document.querySelector(".oldTotalV");
            const oldSubTotalV=document.querySelector(".oldSubTotalV");
            const oldCantV=document.querySelector(".oldCantV");
            if(tableA!=null && tableO!=null && trueTotalV!=null && trueSubTotalV!=null && trueCantV!=null && oldTotalV!=null && oldSubTotalV!=null && oldCantV!=null){
                if(tableO.classList.contains("d-none")){
                    tableA.classList.add("d-none");
                    trueTotalV.classList.add("d-none");
                    trueSubTotalV.classList.add("d-none");
                    trueCantV.classList.add("d-none");
                    tableO.classList.remove("d-none");
                    oldTotalV.classList.remove("d-none");
                    oldSubTotalV.classList.remove("d-none");
                    oldCantV.classList.remove("d-none");
                }else{
                    tableA.classList.remove("d-none");
                    trueTotalV.classList.remove("d-none");
                    trueSubTotalV.classList.remove("d-none");
                    trueCantV.classList.remove("d-none");
                    tableO.classList.add("d-none");
                    oldTotalV.classList.add("d-none");
                    oldSubTotalV.classList.add("d-none");
                    oldCantV.classList.add("d-none");
                }
            }
        });

    }
})();