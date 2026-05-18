document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll('.card');

    // Animación de entrada escalonada
    cards.forEach((card, index) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(15px)";
        card.style.transition = `all 0.4s ease ${index * 0.05}s`;
        
        setTimeout(() => {
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
        }, 50);
    });

    // Confirmación extra elegante para eliminar
    const deleteButtons = document.querySelectorAll('.btn-eliminar');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const confirmed = confirm("¿Estás seguro de que deseas eliminar este producto de la base de datos?");
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});