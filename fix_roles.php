<?php
require 'config.php';

try {
    // 1. Alter table to include 'member' in the role enum
    echo "Altering table to include 'member' role...\n";
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'org_admin', 'org_user', 'member') NOT NULL");
    echo "Table altered successfully.\n";

    // 2. Fix roles for users with empty or invalid roles
    $stmt = $pdo->query("SELECT id, username, organization_id, role FROM users");
    while($user = $stmt->fetch()) {
        $uid = $user['id'];
        $role = trim($user['role'] ?? '');
        
        // If role is empty (could be empty string from failed enum insert)
        if ($role == '' || $role == 'UNDEFINED/NULL') {
            if ($user['username'] == 'admin') {
                $new_role = 'admin';
            } else if ($user['organization_id'] > 0) {
                // If they belong to an organization, they are likely a member
                // unless they are the owner who created it (org_admin).
                // But for now, let's follow the logic: if org_id > 0 and no role, 'member' is a safe bet for user2.
                $new_role = 'member';
            } else {
                $new_role = 'org_admin';
            }
            
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $uid]);
            echo "Fixed [{$user['username']}]: Set role to '$new_role'\n";
        }
    }
    echo "Role fix completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
