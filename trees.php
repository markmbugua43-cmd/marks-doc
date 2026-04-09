<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT t.*, u.full_name AS reporter_name FROM trees t LEFT JOIN users u ON u.id=t.reporter_id';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE t.species LIKE :search OR t.location_label LIKE :search OR u.full_name LIKE :search';
    $params['search'] = '%' . $search . '%';
}
$sql .= ' ORDER BY t.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trees = $stmt->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Trees</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}*{box-sizing:border-box}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:20px;box-shadow:0 10px 25px rgba(0,0,0,.05);margin-bottom:18px}input{padding:12px 14px;border:1px solid #cfdfcf;border-radius:12px;min-width:260px}button,a.button{padding:12px 16px;border:0;border-radius:12px;background:#24653a;color:#fff;text-decoration:none;cursor:pointer}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:13px 10px;border-bottom:1px solid #e6eee5}.badge{padding:6px 10px;border-radius:999px;font-size:12px}.top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}@media(max-width:900px){.layout{grid-template-columns:1fr}}</style></head><body><div class="layout"><div class="side"><h2>Tree Keeper</h2><p><?= e($user['full_name']) ?></p><a href="dashboard.php">Dashboard</a><a class="active" href="trees.php">Trees</a><a href="add_tree.php">Add Tree</a><a href="tasks.php">Tasks</a><a href="reports.php">Reports</a><a href="map.php">Map</a><a href="tree_health.php">Health Check</a><a href="profile.php">Profile</a><a href="logout.php">Logout</a></div><div class="main"><div class="top"><div><h1>Tree Registry</h1><p>All trees stored in MySQL.</p></div><a class="button" href="add_tree.php">Add Tree</a></div><div class="card"><form method="get"><input type="text" name="search" placeholder="Search trees" value="<?= e($search) ?>"> <button type="submit">Search</button></form></div><div class="card"><table><thead><tr><th>Species</th><th>Planted</th><th>Location</th><th>Status</th><th>Reporter</th></tr></thead><tbody><?php if (!$trees): ?><tr><td colspan="5">No trees found.</td></tr><?php endif; ?><?php foreach ($trees as $tree): ?><tr><td><?= e($tree['species']) ?></td><td><?= e($tree['date_planted']) ?></td><td><?= e($tree['location_label']) ?><br><small><?= e($tree['latitude']) ?>, <?= e($tree['longitude']) ?></small></td><td><span class="badge" style="<?= e(status_badge_class($tree['status'])) ?>"><?= e(str_replace('_',' ',$tree['status'])) ?></span></td><td><?= e($tree['reporter_name']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div><script>console.log('Trees page ready');</script></body></html>
