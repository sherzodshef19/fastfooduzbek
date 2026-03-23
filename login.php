<?php
include 'includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Foydalanuvchi nomi yoki parol xato!";
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fast Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f7f9fb; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 30px; border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); background: white; }
        .btn-primary { background-color: #ff6b6b; border: none; border-radius: 12px; padding: 12px; font-weight: 600; }
        .btn-primary:hover { background-color: #fa5252; }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <h2 class="mb-4 text-primary fw-bold">FAST FOOD</h2>
        <h5 class="mb-4">Tizimga kirish</h5>
        <?php if ($error): ?>
            <div class="alert alert-danger small"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label small">Login</label>
                <input type="text" name="username" class="form-control" placeholder="admin yoki kassir" required>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label small">Parol</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">KIRISH</button>
        </form>
      
    </div>
</body>
</html>
