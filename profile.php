<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE users SET full_name=:full_name, phone=:phone, role=:role WHERE id=:id');
    $stmt->execute([
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'role' => trim($_POST['role'] ?? 'volunteer'),
        'id' => $user['id']
    ]);
    flash('success', 'Profile updated.');
    header('Location: profile.php');
    exit;
}
$flashes = get_flash_messages();
$user = current_user($pdo);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profile</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:22px;box-shadow:0 10px 25px rgba(0,0,0,.05)}.field{margin-bottom:14px}.field label{display:block;font-weight:700;margin-bottom:8px}input,select{width:100%;padding:12px 14px;border:1px solid #cfdfcf;border-radius:12px}.flash{padding:12px 14px;border-radius:10px;margin-bottom:12px}.success{background:#e7f7ea;color:#1c6b33}@media(max-width:900px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}}</style></head><body><div class="layout"><div class="side"><h2>Tree Keeper</h2><p><?= e($user['full_name']) ?></p><a href="dashboard.php">Dashboard</a><a href="trees.php">Trees</a><a href="add_tree.php">Add Tree</a><a href="tasks.php">Tasks</a><a href="reports.php">Reports</a><a href="map.php">Map</a><a href="tree_health.php">Health Check</a><a class="active" href="profile.php">Profile</a><a href="logout.php">Logout</a></div><div class="main"><h1>Profile</h1><?php foreach ($flashes as $flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?><div class="grid"><div class="card"><form method="post"><div class="field"><label>Full Name</label><input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required></div><div class="field"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div><div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($user['phone']) ?>"></div><div class="field"><label>Role</label><select name="role"><option value="volunteer" <?= $user['role']==='volunteer'?'selected':'' ?>>Volunteer</option><option value="field_officer" <?= $user['role']==='field_officer'?'selected':'' ?>>Field Officer</option><option value="stakeholder" <?= $user['role']==='stakeholder'?'selected':'' ?>>Stakeholder</option><option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option></select></div><button type="submit" style="padding:12px 18px;border:0;border-radius:12px;background:#24653a;color:#fff;font-weight:700;cursor:pointer">Save Profile</button></form></div><div class="card"><h3>Current Session User</h3><pre><?= e(json_encode($user, JSON_PRETTY_PRINT)) ?></pre></div></div></div></div><script>console.log('Profile loaded');</script></body></html>
