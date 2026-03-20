<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bizshield';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function get_toast_message() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

function set_toast_message($message, $type = 'success') {
    $_SESSION['toast'] = ['message' => $message, 'type' => $type];
}

function get_active_notifications($org_id = null, $limit = 5) {
    global $pdo;
    if ($org_id) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE (organization_id = ? OR organization_id IS NULL) ORDER BY created_at DESC LIMIT $limit");
        $stmt->execute([$org_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE organization_id IS NULL ORDER BY created_at DESC LIMIT $limit");
        $stmt->execute();
    }
    return $stmt->fetchAll();
}
?>
