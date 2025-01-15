<?php
include 'userStorage.php';

session_start();
$storage = new UserStorage();
$errors = [];
$registerData = [
    'name' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registerData['name'] = $_POST['register_name'] ?? '';
    $registerData['email'] = $_POST['register_email'] ?? '';
    $registerData['password'] = $_POST['register_password'] ?? '';
    $registerData['confirm_password'] = $_POST['register_confirm_password'] ?? '';

    if (empty($registerData['name'])) {
        $errors['name'] = 'Full name is required.';
    }
    if (empty($registerData['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($registerData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    }
    if (empty($registerData['password'])) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($registerData['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $registerData['password']) || !preg_match('/[0-9]/', $registerData['password'])) {
        $errors['password'] = 'Password must include at least one uppercase letter and one number.';
    }
    if ($registerData['password'] !== $registerData['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $storage->saveUser([
                'name' => $registerData['name'],
                'email' => $registerData['email'],
                'password' =>$registerData['password']
            ]);
            $_SESSION['name'] = $registerData['name'];
            header('Location: login.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <a href="index.php" class="button button-home">Home</a>
    <div class="register-container">
        <form class="register-form" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" novalidate>
            <h2>Register</h2>

    
            <div class="form-group">
                <label for="register-name">Full Name:</label>
                <input 
                    type="text" 
                    id="register-name" 
                    name="register_name" 
                    value="<?= htmlspecialchars($registerData['name']) ?>" 
                    required>
                <?php if (isset($errors['name'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="register-email">Email:</label>
                <input 
                    type="email" 
                    id="register-email" 
                    name="register_email" 
                    value="<?= htmlspecialchars($registerData['email']) ?>" 
                    required>
                <?php if (isset($errors['email'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="register-password">Password:</label>
                <input 
                    type="password" 
                    id="register-password" 
                    name="register_password" 
                    required>
                <?php if (isset($errors['password'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="register-confirm-password">Confirm Password:</label>
                <input 
                    type="password" 
                    id="register-confirm-password" 
                    name="register_confirm_password" 
                    required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-register">Register</button>
            </div>

            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
