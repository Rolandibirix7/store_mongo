inputBusqueda.addEventListener("keyup", function() {
    const texto = this.value.trim();
    if (texto === "") {
        resultados.style.display = "none";
        return;
    }

    fetch("/31b/store/buscar.php?buscar=" + encodeURIComponent(texto))
        .then(response => response.text())
        .then(data => {
            // Seteamos el HTML primero
            resultados.innerHTML = data;
            
            // Si hay datos, mostramos el contenedor
            if (data.trim() !== "") {
                resultados.style.display = "block";
            } else {
                resultados.style.display = "none";
            }
        });
});

document.addEventListener("DOMContentLoaded", () => {
    const inputBusqueda = document.getElementById("busqueda");
    const resultados = document.getElementById("resultado-busqueda");

    if (inputBusqueda && resultados) {
        inputBusqueda.addEventListener("keyup", function() {
            const texto = this.value.trim();

            if (texto === "") {
                resultados.innerHTML = "";
                resultados.style.display = "none";
                return;
            }

            // Llamada al PHP
            fetch("/31b/store/buscar.php?buscar=" + encodeURIComponent(texto))
                .then(response => response.text())
                .then(data => {
                    if (data.trim() !== "") {
                        resultados.innerHTML = data;
                        resultados.style.display = "block";
                    } else {
                        resultados.style.display = "none";
                    }
                })
                .catch(err => console.error("Error en la búsqueda:", err));
        });

        // Cerrar al hacer click afuera
        document.addEventListener("click", function(e) {
            if (!resultados.contains(e.target) && e.target !== inputBusqueda) {
                resultados.style.display = "none";
            }
        });
    }
});