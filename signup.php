<?php
require 'db.php';

if (current_user($pdo)) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'volunteer');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
        header('Location: signup.php');
        exit;
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);
    if ($check->fetch()) {
        flash('error', 'Email already exists.');
        header('Location: signup.php');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO users (full_name,email,phone,role,password_hash) VALUES (:full_name,:email,:phone,:role,:password_hash)');
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    $_SESSION['user_id'] = $pdo->lastInsertId();
    flash('success', 'Account created successfully.');
    header('Location: dashboard.php');
    exit;
}

$flashes = get_flash_messages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Signup | Tree Keeper</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#eef5ee;min-height:100vh;display:flex;align-items:center;justify-content:center;color:#17301c}*{box-sizing:border-box}.wrap{width:min(760px,94vw);background:#fff;border:1px solid #d7e3d6;border-radius:18px;padding:32px;box-shadow:0 18px 40px rgba(0,0,0,.08)}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.field{margin-bottom:16px}.field label{display:block;font-weight:700;margin-bottom:8px}.field input,.field select{width:100%;padding:13px 14px;border:1px solid #cfe0cf;border-radius:12px}.btn{width:100%;padding:14px;border:0;border-radius:12px;background:#24653a;color:#fff;font-weight:700;cursor:pointer}.flash{padding:12px 14px;border-radius:10px;margin-bottom:14px}.success{background:#e7f7ea;color:#1c6b33}.error{background:#fde8e7;color:#b33428}.muted{color:#607060}@media(max-width:700px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
<h1>Create Account</h1>
<p class="muted">Signup converted from React to PHP.</p>
<?php foreach ($flashes as $flash): ?>
<div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
<form method="post">
<div class="grid">
<div class="field"><label>Full Name</label><input type="text" name="full_name" required></div>
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Phone</label><input type="text" name="phone"></div>
<div class="field"><label>Role</label><select name="role"><option value="volunteer">Volunteer</option><option value="field_officer">Field Officer</option><option value="stakeholder">Stakeholder</option><option value="admin">Admin</option></select></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<div class="field"><label>Confirm Password</label><input type="password" name="confirm_password" required></div>
</div>
<button class="btn" type="submit">Create Account</button>
</form>
<p class="muted">Already have an account? <a href="index.php">Login</a></p>
</div>
<script>document.querySelector('input[name="full_name"]').focus();</script>
</body>
</html>
