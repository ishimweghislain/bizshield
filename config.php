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

function get_active_notifications($org_id = null, $user_id = null, $limit = 5) {
    global $pdo;
    
    // Base conditions: always show global notifications (both IDs null)
    $conditions = ["(organization_id IS NULL AND user_id IS NULL)"];
    $params = [];

    if ($org_id) {
        $conditions[] = "organization_id = ?";
        $params[] = $org_id;
    }

    if ($user_id) {
        $conditions[] = "user_id = ?";
        $params[] = $user_id;
    }

    $sql = "SELECT * FROM notifications WHERE (" . implode(" OR ", $conditions) . ") ORDER BY created_at DESC LIMIT " . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
