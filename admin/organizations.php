<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE organizations SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        set_toast_message("Organization approved successfully.");
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE organizations SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        set_toast_message("Organization rejected.", "warning");
    } elseif ($action == 'disable') {
        $stmt = $pdo->prepare("UPDATE organizations SET status = 'disabled' WHERE id = ?");
        $stmt->execute([$id]);
        set_toast_message("Organization disabled.", "warning");
    } elseif ($action == 'enable') {
        $stmt = $pdo->prepare("UPDATE organizations SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        set_toast_message("Organization enabled.");
    } elseif ($action == 'delete') {
        try {
            $pdo->beginTransaction();
            // Delete documents
            $pdo->prepare("DELETE FROM documents WHERE organization_id = ?")->execute([$id]);
            // Delete notifications
            $pdo->prepare("DELETE FROM notifications WHERE organization_id = ?")->execute([$id]);
            // Delete users
            $pdo->prepare("DELETE FROM users WHERE organization_id = ?")->execute([$id]);
            // Delete organization
            $pdo->prepare("DELETE FROM organizations WHERE id = ?")->execute([$id]);
            $pdo->commit();
            set_toast_message("Organization and data permanently deleted.", "warning");
        } catch (Exception $e) {
            $pdo->rollBack();
            set_toast_message("Error: " . $e->getMessage(), "warning");
        }
    }
    header("Location: organizations.php");
    exit;
}

// Fetch all organizations with user counts
$stmt = $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM users u WHERE u.organization_id = o.id) as user_count FROM organizations o ORDER BY o.created_at DESC");
$organizations = $stmt->fetchAll();

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Manage Organizations | BizShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#064E3B', light: '#14532D', dark: '#022c22' },
                    },
                }
            }
        }
    </script>
    <style>body { font-family: 'Inter', sans-serif; } .sidebar-link.active { background-color: #064E3B; color: white; box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.1); }</style>
</head>
<body class="bg-gray-50/50">
    <?php include '../components/marquee.php'; ?>

    <div class="flex min-h-screen">
        <?php include 'components/bottom_nav.php'; ?>

        <!-- Sidebar -->
        <aside class="w-72 bg-white border-r border-gray-100 flex flex-col h-screen sticky top-0 hidden lg:flex">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                        <i class="ph ph-shield-check text-2xl"></i>
                    </div>
                <span class="text-xl font-bold text-primary tracking-tight">BizShield</span>
            </div>
            <nav class="space-y-1">
                <a href="dashboard.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-squares-four text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="organizations.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-buildings text-xl"></i>
                    <span>Organizations</span>
                </a>
                <a href="documents.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-files text-xl"></i>
                    <span>Documents</span>
                </a>
                <a href="notifications.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-bell text-xl"></i>
                    <span>Notifications</span>
                </a>
                <a href="settings.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-gear text-xl"></i>
                    <span>Portal Settings</span>
                </a>
            </nav>
        </div>
        <div class="mt-auto p-8 border-t border-gray-50 bg-gray-50/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div><p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p><p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Administrator</p></div>
            </div>
            <a href="../logout.php" class="w-full bg-red-50 text-red-600 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-red-100 transition-all">
                <i class="ph ph-sign-out"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-grow p-6 lg:p-10 pb-32">
        <?php if ($toast): ?>
        <div id="toast" class="fixed top-10 right-4 lg:right-10 z-[2000] bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-green-500' : 'border-l-orange-500'; ?> flex items-center gap-4 animate-bounce-in">
            <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-green-50 text-green-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center"><i class="ph <?php echo $toast['type'] == 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-2xl font-bold"></i></div>
            <div><p class="text-xs text-gray-400 font-bold uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p><p class="text-sm font-bold text-gray-700"><?php echo $toast['message']; ?></p></div>
        </div>
        <script>setTimeout(() => { document.getElementById('toast')?.remove(); }, 3000);</script>
        <?php endif; ?>

        <header class="flex items-center justify-between mb-10" data-aos="fade-down">
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-gray-900 mb-1">Manage Organizations</h1>
                <p class="text-sm text-gray-400 font-medium">Global entity oversight and account status controls.</p>
            </div>
        </header>

        <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left py-4 border-b border-gray-50">
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Organization</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">Staff</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Papers</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($organizations as $org): ?>
                        <tr class="group hover:bg-gray-50/50 transition-all">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-primary font-bold text-lg">
                                        <?php echo strtoupper(substr($org['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900"><?php echo $org['name']; ?></p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-[10px] text-gray-400 font-medium truncate w-32"><?php echo $org['email']; ?></p>
                                            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                            <p class="text-[8px] font-black uppercase text-primary tracking-widest">
                                                <?php 
                                                $role_stmt = $pdo->prepare("SELECT role FROM users WHERE organization_id = ? ORDER BY id ASC LIMIT 1");
                                                $role_stmt->execute([$org['id']]);
                                                $role = $role_stmt->fetchColumn();
                                                echo ($role === 'member') ? 'Individual' : 'Corporate';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="text-sm font-black text-primary bg-primary/5 px-3 py-1.5 rounded-lg border border-primary/10">
                                    <?php echo $org['user_count']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <?php 
                                $status_classes = [
                                    'pending' => 'bg-orange-50 text-orange-600 border-orange-100',
                                    'approved' => 'bg-green-50 text-green-600 border-green-100',
                                    'rejected' => 'bg-red-50 text-red-600 border-red-100',
                                    'disabled' => 'bg-gray-100 text-gray-500 border-gray-200'
                                ];
                                $status_class = $status_classes[$org['status']] ?? 'bg-gray-100 text-gray-500';
                                ?>
                                <span class="<?php echo $status_class; ?> text-[9px] font-black uppercase px-3 py-1.5 rounded-full border tracking-widest">
                                    <?php echo $org['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <a href="view_org_docs.php?id=<?php echo $org['id']; ?>" class="text-[10px] font-bold text-primary hover:bg-primary hover:text-white px-3 py-1.5 rounded-xl border border-primary/20 transition-all text-center inline-block">VIEW</a>
                            </td>
                            <td class="px-6 py-5 text-right flex items-center justify-end gap-2">
                                <a href="view_org_docs.php?id=<?php echo $org['id']; ?>" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all shadow-sm" title="View Info">
                                    <i class="ph ph-eye"></i>
                                </a>
                                <?php if ($org['status'] == 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $org['id']; ?>" class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm" title="Approve">
                                    <i class="ph ph-check"></i>
                                </a>
                                <a href="?action=reject&id=<?php echo $org['id']; ?>" class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Reject">
                                    <i class="ph ph-x"></i>
                                </a>
                                <?php elseif ($org['status'] == 'approved'): ?>
                                <a href="?action=disable&id=<?php echo $org['id']; ?>" class="w-8 h-8 rounded-full bg-gray-50 text-gray-500 flex items-center justify-center hover:bg-gray-900 hover:text-white transition-all shadow-sm" title="Disable">
                                    <i class="ph ph-prohibit"></i>
                                </a>
                                <?php elseif ($org['status'] == 'disabled' || $org['status'] == 'rejected'): ?>
                                <a href="?action=enable&id=<?php echo $org['id']; ?>" class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm" title="Enable">
                                     <i class="ph ph-arrows-clockwise"></i>
                                 </a>
                                 <a href="?action=delete&id=<?php echo $org['id']; ?>" onclick="return confirm('PERMANENTLY DELETE ORGANIZATION?')" class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                                     <i class="ph ph-trash"></i>
                                 </a>
                                 <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
