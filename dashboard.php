<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
$flashes = get_flash_messages();

$summary = [
    'trees' => (int)$pdo->query('SELECT COUNT(*) FROM trees')->fetchColumn(),
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'healthy' => (int)$pdo->query("SELECT COUNT(*) FROM trees WHERE status = 'healthy'")->fetchColumn(),
    'attention' => (int)$pdo->query("SELECT COUNT(*) FROM trees WHERE status IN ('needs_attention','diseased','dead')")->fetchColumn(),
];
$recent = $pdo->query('SELECT species, location_label, status, created_at FROM trees ORDER BY created_at DESC LIMIT 6')->fetchAll();
$survival = $summary['trees'] ? round(($summary['healthy'] / $summary['trees']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}*{box-sizing:border-box}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:20px 0}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:20px;box-shadow:0 10px 25px rgba(0,0,0,.05)}.list-item{display:flex;justify-content:space-between;gap:14px;padding:14px 0;border-bottom:1px solid #e7eee5}.badge{padding:6px 10px;border-radius:999px;font-size:12px}.top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}.progress{height:14px;background:#edf3ea;border-radius:999px;overflow:hidden}.progress span{display:block;height:100%;background:linear-gradient(90deg,#2b6c40,#8bbe66)}.flash{padding:12px 14px;border-radius:10px;margin-bottom:12px}.success{background:#e7f7ea;color:#1c6b33}.error{background:#fde8e7;color:#b33428}@media(max-width:900px){.layout{grid-template-columns:1fr}.cards{grid-template-columns:1fr 1fr}}@media(max-width:620px){.cards{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="layout">
<div class="side">
<h2>Tree Keeper</h2>
<p><?= e($user['full_name']) ?></p>
<a class="active" href="dashboard.php">Dashboard</a>
<a href="trees.php">Trees</a>
<a href="add_tree.php">Add Tree</a>
<a href="tasks.php">Tasks</a>
<a href="reports.php">Reports</a>
<a href="map.php">Map</a>
<a href="tree_health.php">Health Check</a>
<a href="profile.php">Profile</a>
<a href="logout.php">Logout</a>
</div>
<div class="main">
<div class="top"><div><h1>Dashboard</h1><p>Overview of the converted PHP app.</p></div><a href="add_tree.php">Add Tree</a></div>
<?php foreach ($flashes as $flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?>
<div class="cards">
<div class="card"><div>Total Trees</div><h2><?= e($summary['trees']) ?></h2></div>
<div class="card"><div>Users</div><h2><?= e($summary['users']) ?></h2></div>
<div class="card"><div>Healthy</div><h2><?= e($summary['healthy']) ?></h2></div>
<div class="card"><div>Need Attention</div><h2><?= e($summary['attention']) ?></h2></div>
</div>
<div class="cards" style="grid-template-columns:1fr 1fr;">
<div class="card"><h3>Survival Rate</h3><div class="progress"><span style="width:<?= e($survival) ?>%"></span></div><p><strong><?= e($survival) ?>%</strong> currently healthy.</p></div>
<div class="card"><h3>Recent Tree Activity</h3><?php foreach ($recent as $item): ?><div class="list-item"><div><strong><?= e($item['species']) ?></strong><div><?= e($item['location_label']) ?></div></div><div><span class="badge" style="<?= e(status_badge_class($item['status'])) ?>"><?= e(str_replace('_',' ',$item['status'])) ?></span></div></div><?php endforeach; ?></div>
</div>
</div>
</div>
<script>console.log('Dashboard loaded');</script>
</body>
</html>
