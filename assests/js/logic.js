const loginForm = document.querySelector(".auth-form");

loginForm.addEventListener("submit", (e) => {
    // e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    if (username === "" || password === "") {
        showError("Please fill in all fields!");
        return;
    }
});

window.addEventListener('load', function() {
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
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
