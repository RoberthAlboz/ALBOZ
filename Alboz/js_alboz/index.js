// Abrir pop-up de login
document.getElementById("loginBtn").addEventListener("click", () => {
    document.getElementById("loginPopup").style.display = "flex";
});

// Fechar pop-up
document.getElementById("closePopup").addEventListener("click", () => {
    document.getElementById("loginPopup").style.display = "none";
});

// Simulação de login com Google
document.getElementById("googleLoginBtn").addEventListener("click", () => {
    alert("Login com Google realizado!");

    // Esconde o botão de login
    document.getElementById("loginBtn").style.display = "none";

    // Fecha o pop-up
    document.getElementById("loginPopup").style.display = "none";
});

// Exibir/ocultar menu da conta
document.getElementById("accountIcon").addEventListener("click", () => {
    const menu = document.getElementById("accountMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
});

// Fechar o menu ao clicar fora
document.addEventListener("click", (e) => {
    if (!document.getElementById("accountIcon").contains(e.target) &&
        !document.getElementById("accountMenu").contains(e.target)) {
        document.getElementById("accountMenu").style.display = "none";
    }
});
