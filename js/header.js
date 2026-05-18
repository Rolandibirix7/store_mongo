// Efecto de scroll para la barra transparente
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    if (window.scrollY > 20) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Tu lógica de búsqueda (mantenida y optimizada)
const inputBusqueda = document.getElementById("busqueda");
const resultados = document.getElementById("resultado-busqueda");

inputBusqueda.addEventListener("keyup", function(){
    const texto = this.value.trim();

    if(texto === ""){
        resultados.innerHTML = "";
        resultados.style.display = "none";
        return;
    }

    fetch("/31b/store/buscar.php?buscar=" + encodeURIComponent(texto))
        .then(response => response.text())
        .then(data => {
            if(data.trim() !== "") {
                resultados.innerHTML = data;
                resultados.style.display = "block";
            }
        });
});

// Cerrar resultados al hacer click fuera
document.addEventListener("click", function(e){
    if(!resultados.contains(e.target) && e.target !== inputBusqueda){
        resultados.style.display = "none";
    }
});