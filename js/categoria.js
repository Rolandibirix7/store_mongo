document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card');

    // Efecto de entrada suave para las tarjetas
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `all 0.4s ease ${index * 0.1}s`;

        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    // Manejo de clicks en el botón de compra
    const buyButtons = document.querySelectorAll('.btn-comprar');
    buyButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Si quieres evitar que el link de la "card" se active al dar click al botón
            e.stopPropagation();
            
            console.log("Producto añadido al carrito o redirigiendo...");
            // Aquí puedes añadir lógica de AJAX si no quieres que refresque la página
        });
    });
});