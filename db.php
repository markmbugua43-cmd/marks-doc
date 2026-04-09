<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function env_or_default($key, $default)
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function database_connection_candidates()
{
    $host = env_or_default('DB_HOST', '127.0.0.1');
    $port = env_or_default('DB_PORT', '3306');
    $dbname = env_or_default('DB_NAME', 'tree_keeper_pure_php');
    $socket = env_or_default('DB_SOCKET', '');

    $candidates = [];

    if ($socket !== '') {
        $candidates[] = [
            'dsn' => "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8mb4",
            'label' => "socket $socket",
        ];
    }

    $candidates[] = [
        'dsn' => "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        'label' => "$host:$port",
    ];

    if ($host === 'localhost') {
        $candidates[] = [
            'dsn' => "mysql:host=127.0.0.1;port=$port;dbname=$dbname;charset=utf8mb4",
            'label' => "127.0.0.1:$port",
        ];
    } elseif ($host === '127.0.0.1') {
        $candidates[] = [
            'dsn' => "mysql:host=localhost;port=$port;dbname=$dbname;charset=utf8mb4",
            'label' => "localhost:$port",
        ];
    }

    return $candidates;
}

function connect_database()
{
    $username = env_or_default('DB_USER', 'root');
    $password = env_or_default('DB_PASS', '');
    $attemptErrors = [];
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ];

    foreach (database_connection_candidates() as $candidate) {
        try {
            return new PDO($candidate['dsn'], $username, $password, $options);
        } catch (PDOException $e) {
            $attemptErrors[] = $candidate['label'] . ' - ' . $e->getMessage();
        }
    }

    $message = 'Database connection failed. Start MySQL in XAMPP and confirm database "'
        . env_or_default('DB_NAME', 'tree_keeper_pure_php')
        . '" exists. Tried: ' . implode('; ', $attemptErrors);

    throw new RuntimeException($message);
}

try {
    $pdo = connect_database();
} catch (Throwable $e) {
    http_response_code(500);
    exit('<h2>Database connection failed</h2><p>' . e($e->getMessage()) . '</p>');
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash_messages()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function current_user($pdo)
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, full_name, email, phone, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login($pdo)
{
    if (!current_user($pdo)) {
        flash('error', 'Please log in first.');
        header('Location: index.php');
        exit;
    }
}

function status_badge_class($status)
{
    $map = [
        'healthy' => 'background:#e7f7ea;color:#1f7a35;',
        'needs_attention' => 'background:#fff4dd;color:#9a6800;',
        'diseased' => 'background:#fde8e7;color:#b33428;',
        'dead' => 'background:#ececec;color:#666;',
        'pending' => 'background:#fff4dd;color:#9a6800;',
        'in_progress' => 'background:#e4efff;color:#2457a6;',
        'completed' => 'background:#e7f7ea;color:#1f7a35;'
    ];
    return $map[$status] ?? 'background:#ececec;color:#666;';
}

function upload_image($field)
{
    if (empty($_FILES[$field]) || empty($_FILES[$field]['name'])) {
        return null;
    }

    $file = $_FILES[$field];
    $uploadError = $file['error'] ?? UPLOAD_ERR_OK;
    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded image is larger than the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded image is larger than the allowed form limit.',
            UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'No image was selected.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is missing a temporary upload folder.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded image to disk.',
            UPLOAD_ERR_EXTENSION => 'A server extension stopped the image upload.'
        ];

        throw new RuntimeException($messages[$uploadError] ?? 'Image upload failed on the server.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The uploaded file could not be verified.');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException('Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('The upload folder could not be created on the server.');
    }

    if (!is_writable($uploadDir)) {
        throw new RuntimeException('The upload folder is not writable on the server.');
    }

    $fileName = uniqid('img_', true) . '.' . $extension;
    $target = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('The image could not be moved into the upload folder.');
    }

    return 'uploads/' . $fileName;
}

function detect_health_status($text)
{
    $text = strtolower($text);

    foreach (['dead', 'dry', 'yellow', 'wilt', 'disease', 'spot'] as $bad) {
        if (strpos($text, $bad) !== false) {
            return 'diseased';
        }
    }

    foreach (['healthy', 'green', 'fresh', 'leaf'] as $good) {
        if (strpos($text, $good) !== false) {
            return 'healthy';
        }
    }

    return 'needs_attention';
}
