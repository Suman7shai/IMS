<?php

if (session_status() === PHP_SESSION_NONE){
  session_start();
}

if (isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/dashboard.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/login.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-copy">
                <p class="eyebrow">Welcome back</p>
                <h1 class="form-title">Login</h1>
                <p class="auth-text">Enter your username and password to access your dashboard</p>
            </div>

            <form class="auth-form" action="http://localhost:8080/Project_IMS/auth/login.php" method="post">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Password</label>  
                <div class="password-field">
                    <input type="password" id="password" name="password" required>

                </div>

                <button type="submit" class="primary-btn" id="login-btn">Login</button>

            </form>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/Project_IMS/assests/js/logic.js"></script>
</body>
</html>