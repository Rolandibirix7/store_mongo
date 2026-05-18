document.addEventListener("DOMContentLoaded", () => {
    const footer = document.querySelector('footer');
    const footerText = footer.querySelector('p');

    // 1. Actualizar el contenido para que se vea más pro
    // Esto cambia "Mi Tienda" por tu marca y asegura el año actual
    const year = new Date().getFullYear();
    footerText.innerHTML = `&copy; ${year} <b>StreetShoes_sv</b> - Todos los derechos reservados.`;

    // 2. Animación de entrada (Scroll Reveal)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    // Configuración inicial de la animación
    footer.style.opacity = '0';
    footer.style.transform = 'translateY(20px)';
    footer.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';

    observer.observe(footer);
});