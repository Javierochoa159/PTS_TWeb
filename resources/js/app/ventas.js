import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

(()=>{
    const ctx = document.querySelector('#gananciasChart');
    if (ctx) {
        const chartData = JSON.parse(ctx.getAttribute('data-chart'));
        const chart=new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.meses,
                datasets: chartData.gananciasVisibles.map(dataset => ({
                    ...dataset,
                    backgroundColor: dataset.backgroundColor,
                    borderColor: dataset.borderColor,
                    borderWidth: dataset.borderWidth,
                    borderSkipped: false,
                }))
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: $${context.parsed.y.toLocaleString('es-ES', {
                                    minimumFractionDigits: 4,
                                    maximumFractionDigits: 4
                                })}`;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            generateLabels: function(chart) {
                                const original = Chart.defaults.plugins.legend.labels.generateLabels;
                                const labelsOriginal = original.call(this, chart);
                                labelsOriginal.forEach(label => {
                                    label.fillStyle = label.strokeStyle;
                                });
                                return labelsOriginal;
                            }
                        }
                    },
                    customLegend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Ganancias Netas Mensuales'
                    }
                },
                elements: {
                    bar: {
                        backgroundColor: function(context) {
                            return context.dataset.backgroundColor;
                        },
                        borderColor: function(context) {
                            return context.dataset.borderColor;
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'logarithmic',
                        beginAtZero: true,
                        precision: 4,
                        ticks: {
                            maxTicksLimit: 8,
                            precision: 4,
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return "$"+(value / 1000000).toLocaleString('es-ES', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    })+'M';
                                } else if (value >= 1000) {
                                    return "$"+(value / 1000).toLocaleString('es-ES', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    })+'K';
                                }
                                return "$"+value.toLocaleString('es-ES', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
        const btnMostrarMas = document.querySelector('#btnMostrarMasAnios');
        if (btnMostrarMas) {
            btnMostrarMas.addEventListener('click', function() {
                mostrarSelectorAnios(chartData,chart);
            });
        }
    }
    function mostrarSelectorAnios(chartData,chart) {
        // Crear modal/selector
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        `;
        
        const selector = document.createElement('div');
        selector.style.cssText = `
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: fit-content;
            height: fit-content;
            max-height: 20%;
        `;
        
        // Obtener años actualmente visibles
        const añosVisibles = chart.data.datasets.map(ds => ds.label);
        selector.innerHTML = `
            <h5>Seleccionar Años a Mostrar</h5>
            <div id="checkAnios" class="mb-3 ps-2 col-12">
                ${chartData.ganancias.map(dataset => `
                    <div class="form-check">
                        <input id="${dataset.label}" class="form-check-input year-checkbox" 
                            type="checkbox" 
                            value="${dataset.label}"
                            ${añosVisibles.includes(dataset.label) ? 'checked' : ''}>
                        <label for="${dataset.label}" class="form-check-label">
                            ${dataset.label}
                        </label>
                    </div>
                `).join('')}
            </div>
        `;
        
        modal.appendChild(selector);
        document.body.appendChild(modal);
        modal.querySelector("#checkAnios").style.cssText=`
            overflow-y: scroll;
            height: 5rem;
        `;
        // Event listeners
        const checkboxes = selector.querySelectorAll('.year-checkbox').forEach(cb => {
            cb.addEventListener("change",()=>{
                const cbSelecteds = selector.querySelectorAll('.year-checkbox:checked')
                const datasetsSeleccionados = Array.from(cbSelecteds).map(cb => 
                    chartData.ganancias.find(ds => ds.label === cb.value)
                );
                chart.data.datasets = datasetsSeleccionados;
                chart.update();
                actualizarBotonMasAnios(chartData,chart);
            });
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }

    function actualizarBotonMasAnios(chartData,chart) {
        const btnMostrarMas = document.querySelector('#btnMostrarMasAnios');
        if (btnMostrarMas) {
            const añosOcultos = chartData.ganancias.length - chart.data.datasets.length;
            btnMostrarMas.textContent = `+ ${añosOcultos} años más`;
            btnMostrarMas.style.display = 'block';
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
(()=>{
    document.addEventListener("click", function (event) {
        const collapseEl = document.querySelector("#collapse-filtroFechaTotalVenta");
        if(collapseEl!=null){
            const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
            if (bsCollapse!=null && collapseEl.classList.contains("show")) {
                if (!collapseEl.contains(event.target) && !document.querySelector('[data-bs-target="#collapse-filtroFechaTotalVenta"]').contains(event.target)) {
                    bsCollapse.hide();
                }
            }
        }
    });
})();

(()=>{
    window.addEventListener("load",()=>{
        const btnF=document.querySelector("#btn-filtroTotalVenta-fechaPresonal");
        const divFPersonal=document.querySelector("#div_fechaPersonal");
        if(btnF!=null && divFPersonal){
            btnF.addEventListener("click",()=>{
                divFPersonal.classList.remove("d-none");
                const btnFechas=btnF.closest(".dropdown").querySelector("button[data-bs-toggle='dropdown']");
                if(btnFechas!=null){
                    btnFechas.firstElementChild.textContent="Personalizado";
                    const oldSelected=btnF.closest(".dropdown").querySelector(".selected");
                    if(oldSelected!=null){
                        oldSelected.classList.remove("selected");
                    }
                    btnF.classList.add("selected");
                }
            });
        }
    });
})();
