<?php
require 'db.php';
require_login($pdo);
$user = current_user($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $image = upload_image('tree_image');
        $stmt = $pdo->prepare('INSERT INTO trees (species,date_planted,latitude,longitude,location_label,status,notes,image_path,reporter_id) VALUES (:species,:date_planted,:latitude,:longitude,:location_label,:status,:notes,:image_path,:reporter_id)');
        $stmt->execute([
            'species' => trim($_POST['species'] ?? ''),
            'date_planted' => $_POST['date_planted'] ?? '',
            'latitude' => $_POST['latitude'] !== '' ? $_POST['latitude'] : null,
            'longitude' => $_POST['longitude'] !== '' ? $_POST['longitude'] : null,
            'location_label' => trim($_POST['location_label'] ?? ''),
            'status' => $_POST['status'] ?? 'healthy',
            'notes' => trim($_POST['notes'] ?? ''),
            'image_path' => $image,
            'reporter_id' => $user['id']
        ]);
        flash('success', 'Tree added successfully.');
        header('Location: trees.php');
        exit;
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        header('Location: add_tree.php');
        exit;
    }
}
$flashes = get_flash_messages();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Add Tree</title><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f8f3;color:#18311c}.layout{display:grid;grid-template-columns:260px 1fr;min-height:100vh}.side{background:#19261c;color:#eef7ef;padding:22px}.side a{display:block;color:#d9e9d9;padding:11px 12px;border-radius:10px;text-decoration:none;margin-bottom:6px}.side a.active,.side a:hover{background:rgba(255,255,255,.1);color:#fff}.main{padding:24px}*{box-sizing:border-box}.card{background:#fff;border:1px solid #dbe6da;border-radius:16px;padding:22px;box-shadow:0 10px 25px rgba(0,0,0,.05)}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.field{margin-bottom:16px}.field label{display:block;font-weight:700;margin-bottom:8px}input,select,textarea{width:100%;padding:12px 14px;border:1px solid #cfdfcf;border-radius:12px}button{padding:12px 18px;border:0;border-radius:12px;background:#24653a;color:#fff;font-weight:700;cursor:pointer}.flash{padding:12px 14px;border-radius:10px;margin-bottom:12px}.success{background:#e7f7ea;color:#1c6b33}.error{background:#fde8e7;color:#b33428}@media(max-width:900px){.layout{grid-template-columns:1fr}.grid{grid-template-columns:1fr}}</style></head><body><div class="layout"><div class="side"><h2>Tree Keeper</h2><p><?= e($user['full_name']) ?></p><a href="dashboard.php">Dashboard</a><a href="trees.php">Trees</a><a class="active" href="add_tree.php">Add Tree</a><a href="tasks.php">Tasks</a><a href="reports.php">Reports</a><a href="map.php">Map</a><a href="tree_health.php">Health Check</a><a href="profile.php">Profile</a><a href="logout.php">Logout</a></div><div class="main"><h1>Add Tree</h1><?php foreach ($flashes as $flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?><div class="card"><form method="post" enctype="multipart/form-data"><div class="grid"><div class="field"><label>Species</label><input type="text" name="species" required></div><div class="field"><label>Date Planted</label><input type="date" name="date_planted" required></div><div class="field"><label>Latitude</label><input type="number" step="0.000001" name="latitude" id="latitude"></div><div class="field"><label>Longitude</label><input type="number" step="0.000001" name="longitude" id="longitude"></div></div><div class="field"><label>Location Label</label><input type="text" name="location_label" required></div><div class="field"><label>Status</label><select name="status"><option value="healthy">Healthy</option><option value="needs_attention">Needs Attention</option><option value="diseased">Diseased</option><option value="dead">Dead</option></select></div><div class="field"><label>Photo</label><input type="file" name="tree_image" accept="image/*"></div><div class="field"><label>Notes</label><textarea name="notes" rows="5"></textarea></div><button type="button" id="gpsBtn">Auto Detect GPS</button> <button type="submit">Save Tree</button></form></div></div></div><script>document.getElementById('gpsBtn').addEventListener('click',function(){if(!navigator.geolocation){alert('Geolocation not supported');return;}navigator.geolocation.getCurrentPosition(function(pos){document.getElementById('latitude').value=pos.coords.latitude.toFixed(6);document.getElementById('longitude').value=pos.coords.longitude.toFixed(6);},function(){alert('Could not fetch GPS');});});</script></body></html>
