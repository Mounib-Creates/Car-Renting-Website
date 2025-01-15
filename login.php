<?php
include 'userStorage.php';

session_start();
$storage = new UserStorage();
$loginError = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['login_email'] ?? '';
    $password = $_POST['login_password'] ?? '';
    
    
    $user = $storage->verifyUser($email, $password);
    
    if ($user) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['isAdmin'] = isset($user['isAdmin']) ? $user['isAdmin'] : false;
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Login failed: Invalid email or password.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<a href="index.php" class="button button-home">Home</a>
<div class="login-container">
    <form class="login-form" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" novalidate>
        <h2>Login</h2>
        <div class="form-group">
            <label for="login-email">Email:</label>
            <input type="text" id="login-email" name="login_email" required>
        </div>
        <div class="form-group">
            <label for="login-password">Password:</label>
            <input type="password" id="login-password" name="login_password" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-login">Login</button>
        </div>
        <?php if ($loginError): ?>
            <p class="error"><?= $loginError ?></p>
        <?php endif; ?>
        <p>Don't have an account? <a href="registration.php">Register here</a></p>
    </form>
</div>
</body>
</html>
