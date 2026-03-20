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

function get_latest_notification($org_id = null) {
    global $pdo;
    if ($org_id) {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE organization_id = ? OR organization_id IS NULL ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$org_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 1");
    }
    return $stmt->fetch();
}
?>
