const loginForm = document.querySelector(".auth-form");

loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    if (username === "" || password === "") {
        showError("Please fill in all fields!");
        return;
    }

    if (username === "admin" && password === "admin") {
        window.location.href = "../dashboard.html";
        return;
    }

    showError("Username or password is incorrect.");
});

function showError(message) {
    if (window.Swal) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: message,
        });
        return;
    }

    alert(message);
}
