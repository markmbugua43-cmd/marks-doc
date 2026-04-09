<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO tasks (title,task_type,tree_species,location_text,assignee_id,due_date,status,details) VALUES (:title,:task_type,:tree_species,:location_text,:assignee_id,:due_date,:status,:details)');
    $stmt->execute([
        'title' => trim($_POST['title'] ?? ''),
        'task_type' => $_POST['task_type'] ?? 'watering',
        'tree_species' => trim($_POST['tree_species'] ?? ''),
        'location_text' => trim($_POST['location_text'] ?? ''),
        'assignee_id' => $_POST['assignee_id'] ?: null,
        'due_date' => $_POST['due_date'] ?? '',
        'status' => $_POST['status'] ?? 'pending',
        'details' => trim($_POST['details'] ?? '')
    ]);
    flash('success', 'Task created.');
    header('Location: tasks.php');
    exit;
}
$flashes = get_flash_messages();
$filter = $_GET['status'] ?? 'all';
$sql = 'SELECT t.*, u.full_name AS assignee_name FROM tasks t LEFT JOIN users u ON u.id=t.assignee_id';
$params = [];
if ($filter !== 'all') {$sql .= ' WHERE t.status = :status'; $params['status'] = $filter;}
$sql .= ' ORDER BY t.due_date ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
$users = $pdo->query('SELECT id, full_name FROM users ORDER BY full_name ASC')->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Tasks</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}*{box-sizing:border-box}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:22px;box-shadow:0 10px 25px rgba(0,0,0,.05)}.field{margin-bottom:14px}.field label{display:block;font-weight:700;margin-bottom:8px}input,select,textarea{width:100%;padding:12px 14px;border:1px solid #cfdfcf;border-radius:12px}.badge{padding:6px 10px;border-radius:999px;font-size:12px}.item{display:flex;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid #e6eee5}.flash{padding:12px 14px;border-radius:10px;margin-bottom:12px}.success{background:#e7f7ea;color:#1c6b33}@media(max-width:900px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}}</style></head><body><div class="layout"><div class="side"><h2>Tree Keeper</h2><p><?= e($user['full_name']) ?></p><a href="dashboard.php">Dashboard</a><a href="trees.php">Trees</a><a href="add_tree.php">Add Tree</a><a class="active" href="tasks.php">Tasks</a><a href="reports.php">Reports</a><a href="map.php">Map</a><a href="tree_health.php">Health Check</a><a href="profile.php">Profile</a><a href="logout.php">Logout</a></div><div class="main"><h1>Tasks</h1><?php foreach ($flashes as $flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?><div class="grid"><div class="card"><h3>Create Task</h3><form method="post"><div class="field"><label>Title</label><input type="text" name="title" required></div><div class="field"><label>Task Type</label><select name="task_type"><option value="watering">Watering</option><option value="inspection">Inspection</option><option value="maintenance">Maintenance</option></select></div><div class="field"><label>Tree Species</label><input type="text" name="tree_species" required></div><div class="field"><label>Location</label><input type="text" name="location_text"></div><div class="field"><label>Assignee</label><select name="assignee_id"><option value="">Unassigned</option><?php foreach ($users as $person): ?><option value="<?= e($person['id']) ?>"><?= e($person['full_name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Due Date</label><input type="date" name="due_date" required></div><div class="field"><label>Status</label><select name="status"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div><div class="field"><label>Details</label><textarea name="details" rows="4"></textarea></div><button type="submit">Save Task</button></form></div><div class="card"><div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap"><h3>Task List</h3><form method="get"><select name="status" onchange="this.form.submit()"><option value="all" <?= $filter==='all'?'selected':'' ?>>All</option><option value="pending" <?= $filter==='pending'?'selected':'' ?>>Pending</option><option value="in_progress" <?= $filter==='in_progress'?'selected':'' ?>>In Progress</option><option value="completed" <?= $filter==='completed'?'selected':'' ?>>Completed</option></select></form></div><?php foreach ($tasks as $task): ?><div class="item"><div><strong><?= e($task['title']) ?></strong><div><?= e($task['task_type']) ?> for <?= e($task['tree_species']) ?></div><small><?= e($task['location_text']) ?> <?= $task['assignee_name'] ? '| ' . e($task['assignee_name']) : '' ?></small></div><div><span class="badge" style="<?= e(status_badge_class($task['status'])) ?>"><?= e(str_replace('_',' ',$task['status'])) ?></span><div><small><?= e($task['due_date']) ?></small></div></div></div><?php endforeach; ?></div></div></div></div><script>console.log('Tasks loaded');</script></body></html>
