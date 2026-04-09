<?php
require 'db.php';

if (current_user($pdo)) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        flash('success', 'Welcome back.');
        header('Location: dashboard.php');
        exit;
    }

    flash('error', 'Invalid login details.');
    header('Location: index.php');
    exit;
}

$flashes = get_flash_messages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Tree Keeper</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#f2f7f1,#dfeee0);min-height:100vh;display:flex;align-items:center;justify-content:center;color:#17301c}*{box-sizing:border-box}.shell{width:min(980px,94vw);display:grid;grid-template-columns:1.1fr .9fr;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 45px rgba(19,63,28,.12)}.hero{padding:42px;background:linear-gradient(135deg,#1e5a31,#4f8a5a);color:#fff}.hero h1{font-size:42px;line-height:1.1;margin:14px 0}.hero p{color:#e6f3e7}.card{padding:42px}.field{margin-bottom:16px}.field label{display:block;font-weight:700;margin-bottom:8px}.field input{width:100%;padding:13px 14px;border:1px solid #cfe0cf;border-radius:12px}.btn{width:100%;padding:14px;border:0;border-radius:12px;background:#24653a;color:#fff;font-weight:700;cursor:pointer}.flash{padding:12px 14px;border-radius:10px;margin-bottom:14px}.success{background:#e7f7ea;color:#1c6b33}.error{background:#fde8e7;color:#b33428}.muted{color:#607060}@media(max-width:760px){.shell{grid-template-columns:1fr}.hero{display:none}}
</style>
</head>
<body>
<div class="shell">
<div class="hero">
<div>Tree Keeper</div>
<h1>Pure PHP conversion of your React project.</h1>
<p>This version uses PHP sessions, PDO, MySQL, HTML, CSS, and JavaScript only.</p>
<p>Demo login: <strong>admin@treekeeper.local</strong> / <strong>admin123</strong></p>
</div>
<div class="card">
<h2>Login</h2>
<p class="muted">Sign in to access the dashboard.</p>
<?php foreach ($flashes as $flash): ?>
<div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
<form method="post">
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<button class="btn" type="submit">Sign In</button>
</form>
<p class="muted">No account? <a href="signup.php">Create one</a></p>
</div>
</div>
<script>
document.querySelector('input[name="email"]').focus();
</script>
</body>
</html>
