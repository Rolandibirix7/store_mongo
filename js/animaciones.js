document.querySelectorAll(".card").forEach(card => {
    card.addEventListener("mouseover", () => {
        card.style.boxShadow = "0 0 15px orange";
    });

    card.addEventListener("mouseout", () => {
        card.style.boxShadow = "none";
    });
});