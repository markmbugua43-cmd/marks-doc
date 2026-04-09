<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $image = upload_image('health_image');
        $text = ($_FILES['health_image']['name'] ?? '') . ' ' . ($_POST['notes'] ?? '');
        $status = detect_health_status($text);
        $stmt = $pdo->prepare('INSERT INTO health_checks (tree_species,image_path,status,notes,location_text,checked_by) VALUES (:tree_species,:image_path,:status,:notes,:location_text,:checked_by)');
        $stmt->execute([
            'tree_species' => trim($_POST['tree_species'] ?? ''),
            'image_path' => $image,
            'status' => $status,
            'notes' => trim($_POST['notes'] ?? ''),
            'location_text' => trim($_POST['location_text'] ?? ''),
            'checked_by' => $user['id']
        ]);
        flash('success', 'Health check saved with status: ' . str_replace('_', ' ', $status));
        header('Location: tree_health.php');
        exit;
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        header('Location: tree_health.php');
        exit;
    }
}
$flashes = get_flash_messages();
$checks = $pdo->query('SELECT h.*, u.full_name FROM health_checks h LEFT JOIN users u ON u.id=h.checked_by ORDER BY h.created_at DESC LIMIT 10')->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Health Check</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:22px;box-shadow:0 10px 25px rgba(0,0,0,.05)}.field{margin-bottom:14px}.field label{display:block;font-weight:700;margin-bottom:8px}input,textarea{width:100%;padding:12px 14px;border:1px solid #cfdfcf;border-radius:12px}.item{display:flex;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid #e6eee5}.badge{padding:6px 10px;border-radius:999px;font-size:12px}.flash{padding:12px 14px;border-radius:10px;margin-bottom:12px}.success{background:#e7f7ea;color:#1c6b33}.error{background:#fde8e7;color:#b33428}@media(max-width:900px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}}</style></head><body><div class="layout"><div class="side"><h2>Tree Keeper</h2><p><?= e($user['full_name']) ?></p><a href="dashboard.php">Dashboard</a><a href="trees.php">Trees</a><a href="add_tree.php">Add Tree</a><a href="tasks.php">Tasks</a><a href="reports.php">Reports</a><a href="map.php">Map</a><a class="active" href="tree_health.php">Health Check</a><a href="profile.php">Profile</a><a href="logout.php">Logout</a></div><div class="main"><h1>Tree Health Check</h1><?php foreach ($flashes as $flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?><div class="grid"><div class="card"><h3>Analyze Tree</h3><form method="post" enctype="multipart/form-data"><div class="field"><label>Tree Species</label><input type="text" name="tree_species" required></div><div class="field"><label>Location</label><input type="text" name="location_text" required></div><div class="field"><label>Image</label><input type="file" name="health_image" accept="image/*" required></div><div class="field"><label>Notes</label><textarea name="notes" rows="5" placeholder="Example: yellow leaves, dry stem, or healthy green canopy"></textarea></div><button type="submit" style="padding:12px 18px;border:0;border-radius:12px;background:#24653a;color:#fff;font-weight:700;cursor:pointer">Save Health Check</button></form></div><div class="card"><h3>Recent Checks</h3><?php foreach ($checks as $check): ?><div class="item"><div><strong><?= e($check['tree_species']) ?></strong><div><?= e($check['location_text']) ?></div><small><?= e($check['full_name']) ?></small></div><div><span class="badge" style="<?= e(status_badge_class($check['status'])) ?>"><?= e(str_replace('_',' ',$check['status'])) ?></span></div></div><?php endforeach; ?></div></div></div></div><script>console.log('Health page ready');</script></body></html>
