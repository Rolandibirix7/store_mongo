document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById('file-input');
    const imgPreview = document.getElementById('img-preview');

    if (fileInput && imgPreview) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block'; // Mostramos la imagen
                }
                
                reader.readAsDataURL(file);
            }
        });
    }

    // Desvanecer mensajes de éxito automáticamente
    const successMsg = document.querySelector('.success');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = "opacity 0.5s ease";
            successMsg.style.opacity = "0";
            setTimeout(() => successMsg.remove(), 500);
        }, 3000);
    }
});