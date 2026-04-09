<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: organizations.php");
    exit;
}

$org_id = $_GET['id'];

// Handle Document Actions
if (isset($_GET['doc_action']) && isset($_GET['doc_id'])) {
    $doc_id = $_GET['doc_id'];
    $doc_action = $_GET['doc_action']; // Renamed from $action for clarity with the new logic
    $reason = $_GET['reason'] ?? 'Document does not meet requirements.'; // Default reason for rejection

    // Fetch Target User ID, label, and existing rejection count
    $target_stmt = $pdo->prepare("SELECT user_id, doc_label, file_name, rejection_count FROM documents WHERE id = ?");
    $target_stmt->execute([$doc_id]);
    $target_info = $target_stmt->fetch();
    $target_user_id = $target_info['user_id'];
    
    // Fallback logic for label
    $label = trim($target_info['doc_label'] ?? '');
    if (empty($label)) {
        $label = $target_info['file_name'] ?? 'Requirement Document';
    }
    
    $rejection_count = (int)$target_info['rejection_count'];
    $stars = str_repeat('*', $rejection_count);
    $display_label = $label . $stars;
    
    if ($doc_action == 'approve') {
        $stmt = $pdo->prepare("UPDATE documents SET status = 'approved' WHERE id = ?");
        $stmt->execute([$doc_id]);
        set_toast_message("Document approved.");

        // Notify Team Member
        $notif = "Congratulations! Your document '$display_label' has been approved by the global administrator.";
        $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'info')")->execute([$target_user_id, $notif]);

    } elseif ($doc_action == 'reject') {
        // Increment rejection count for this specific document record
        $stmt = $pdo->prepare("UPDATE documents SET status = 'rejected', rejection_reason = ?, rejection_count = rejection_count + 1 WHERE id = ?");
        $stmt->execute([$reason, $doc_id]);
        
        // Notify Org
        $stmt = $pdo->prepare("INSERT INTO notifications (organization_id, message, type) VALUES (?, ?, 'warning')");
        $stmt->execute([$org_id, "Your document '$display_label' was rejected: " . $reason]);

        // Notify Team Member
        $notif = "CRITICAL: Your document '$display_label' was rejected by the global admin: $reason";
        $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'warning')")->execute([$target_user_id, $notif]);
        
        set_toast_message("Document rejected.", "warning");
    }
    header("Location: view_org_docs.php?id=" . $org_id);
    exit;
}

// Fetch Organization Details & Primary Role
$stmt = $pdo->prepare("SELECT o.*, u.role FROM organizations o LEFT JOIN users u ON o.id = u.organization_id WHERE o.id = ? ORDER BY u.id ASC LIMIT 1");
$stmt->execute([$org_id]);
$org = $stmt->fetch();

if (!$org) {
    header("Location: organizations.php");
    exit;
}

$user_id_filter = $_GET['user_id'] ?? null;

// Fetch documents (with optional filter)
$sql = "SELECT d.*, u.username as uploader FROM documents d JOIN users u ON d.user_id = u.id WHERE d.organization_id = ?";
if ($user_id_filter) {
    $sql .= " AND d.user_id = ? ORDER BY d.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$org_id, $user_id_filter]);
} else {
    $stmt = $pdo->prepare($sql . " ORDER BY d.created_at DESC");
    $stmt->execute([$org_id]);
}
$documents = $stmt->fetchAll();

// Fetch unique people in this organization for the picker
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE organization_id = ? AND role = 'org_user'");
$stmt->execute([$org_id]);
$team_members = $stmt->fetchAll();

// Fetch users
$stmt = $pdo->prepare("SELECT * FROM users WHERE organization_id = ? ORDER BY role DESC");
$stmt->execute([$org_id]);
$users = $stmt->fetchAll();

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title><?php echo $org['name']; ?> Details | BizShield</title>
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
    <style>body { font-family: 'Inter', sans-serif; } .sidebar-link.active { background-color: #064E3B; color: white; }</style>
</head>
<body class="bg-gray-50/50 flex min-h-screen">
    <?php include 'components/bottom_nav.php'; ?>

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
            </nav>
        </div>
        <div class="mt-auto p-8 border-t border-gray-50">
             <a href="organizations.php" class="w-full bg-gray-50 text-gray-500 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-gray-100 transition-all">
                <i class="ph ph-arrow-left"></i> Back to List
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

        <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-12" data-aos="fade-down">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-primary/5 text-primary rounded-[2.5rem] flex items-center justify-center text-4xl font-black shadow-xl border-4 border-white">
                    <?php echo strtoupper(substr($org['name'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-900 leading-tight">
                        <?php if ($user_id_filter && !empty($documents)): ?>
                            Audit: <span class="text-primary italic underline uppercase"><?php echo $documents[0]['uploader']; ?></span>
                        <?php else: ?>
                            Org Audit: <span class="text-primary italic"><?php echo $org['name']; ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[.3em] flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                        Reviewing <?php echo ($org['role'] === 'member') ? 'Individual Member' : 'Corporate Organization'; ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php if ($user_id_filter): ?>
                <a href="view_org_docs.php?id=<?php echo $org_id; ?>" class="bg-red-50 text-red-500 px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:bg-red-100 transition-all border border-red-100 shadow-xl shadow-red-900/10">
                    <i class="ph ph-x-circle text-xl"></i> Clear Selection
                </a>
                <?php endif; ?>
                <a href="organizations.php" class="bg-white border border-gray-100 shadow-soft px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-gray-400 hover:text-primary transition-all flex items-center gap-2">
                    <i class="ph ph-buildings text-xl"></i> All Orgs
                </a>
            </div>
        </header>

        <!-- Personnel Filter -->
        <div class="mb-14" data-aos="fade-up">
            <h2 class="text-[10px] font-black text-gray-300 uppercase tracking-[.4em] mb-6 flex items-center gap-4">
                <span class="w-12 h-[2px] bg-primary/20"></span>
                Select Team Member
            </h2>
            <div class="flex flex-wrap gap-4">
                <?php foreach ($team_members as $m): ?>
                <a href="?id=<?php echo $org_id; ?>&user_id=<?php echo $m['id']; ?>" class="group flex items-center gap-4 px-6 py-4 bg-white border <?php echo ($user_id_filter == $m['id']) ? 'border-primary ring-4 ring-primary/5 shadow-2xl scale-105' : 'border-gray-50'; ?> rounded-2xl hover:border-primary/50 hover:shadow-xl transition-all duration-500">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-300 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                        <i class="ph ph-user text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-gray-900 uppercase tracking-tighter truncate w-32"><?php echo $m['username']; ?></h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Compliance Task</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Documents Session -->
            <div class="xl:col-span-2 space-y-8" data-aos="fade-up">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-black text-gray-900 flex items-center gap-3">
                        <i class="ph ph-files text-primary"></i>
                        Uploaded Papers
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($documents as $doc): 
                        $is_rejected = $doc['status'] == 'rejected';
                    ?>
                    <div class="bg-white border border-gray-100 rounded-[2rem] p-6 shadow-soft shadow-green-900/5 hover:border-primary/20 transition-all group overflow-hidden relative <?php echo $is_rejected ? 'opacity-75' : ''; ?>">
                         <?php if ($is_rejected): ?>
                         <div class="absolute inset-0 bg-white/40 backdrop-blur-[2px] z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                             <div class="bg-red-500 text-white text-[10px] font-black px-4 py-2 rounded-full uppercase tracking-widest shadow-lg">Rejected Document</div>
                         </div>
                         <?php endif; ?>

                         <div class="absolute top-4 right-4 z-20">
                            <?php 
                    $status_colors = [
                        'pending' => 'bg-orange-50 text-orange-600 border-orange-100',
                        'verified' => 'bg-blue-50 text-blue-600 border-blue-100',
                        'approved' => 'bg-green-50 text-green-600 border-green-100',
                        'rejected' => 'bg-red-50 text-red-600 border-red-100'
                    ];
                    $status_class = $status_colors[$doc['status']] ?? 'bg-gray-100 text-gray-400';
                    ?>
        <span class="text-[8px] font-black uppercase px-2 py-1 rounded-full border <?php echo $status_class; ?>">
                                <?php echo $doc['status']; ?>
                            </span>
                         </div>
                         <div class="flex items-center gap-4 mb-6 <?php echo $is_rejected ? 'blur-[1px]' : ''; ?>">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-primary text-2xl group-hover:scale-110 transition-transform">
                                <?php echo in_array(strtolower($doc['file_type']), ['jpg','jpeg','png']) ? '<i class="ph ph-image"></i>' : '<i class="ph ph-file-pdf"></i>'; ?>
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-[10px] text-primary font-black uppercase tracking-widest mb-1"><?php echo htmlspecialchars($doc['doc_label'] ?? 'General Document'); ?></p>
                                <p class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">By: <?php echo $doc['uploader']; ?></p>
                                </div>
                            </div>
                         </div>
                         
                         <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
                            <div class="flex gap-2">
                                <a href="../organization/view_doc.php?id=<?php echo $doc['id']; ?>" target="_blank" class="w-10 h-10 bg-white border border-gray-50 text-gray-400 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm">
                                    <i class="ph ph-eye font-bold"></i>
                                </a>
                            </div>
                            <?php if ($doc['status'] == 'pending'): ?>
                            <a href="?id=<?php echo $org_id; ?>&doc_id=<?php echo $doc['id']; ?>&doc_action=approve<?php echo $user_id_filter ? '&user_id='.$user_id_filter : ''; ?>" class="p-3 bg-green-50 text-green-600 rounded-xl hover:bg-green-500 hover:text-white transition-all"><i class="ph ph-check-bold font-bold"></i></a>
                            <button onclick="rejectDoc(<?php echo $doc['id']; ?>, '<?php echo $user_id_filter; ?>')" class="p-3 bg-red-50 text-red-600 rounded-xl hover:bg-red-500 hover:text-white transition-all"><i class="ph ph-x-bold font-bold"></i></button>
                            <?php endif; ?>
                         </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($documents)): ?>
                <div class="py-20 text-center bg-white border border-dashed border-gray-200 rounded-[2.5rem]">
                    <i class="ph ph-folder-open text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-400 font-bold">No documents uploaded yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Team Members Session -->
            <div class="xl:col-span-1 space-y-8" data-aos="fade-left">
                <h2 class="text-xl font-black text-gray-900 px-2 flex items-center gap-3">
                    <i class="ph ph-users text-primary"></i>
                    Internal Team
                </h2>
                <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8 space-y-4">
                    <?php foreach ($users as $u): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl hover:bg-gray-50 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-primary font-bold shadow-sm">
                                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-900"><?php echo $u['username']; ?></p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">
                                    <?php 
                                    if ($u['role'] == 'org_admin') echo 'Super Admin';
                                    elseif ($u['role'] == 'member') echo 'Member';
                                    else echo 'Staff';
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="w-2 h-2 rounded-full <?php echo $u['status'] == 'active' ? 'bg-green-400' : 'bg-gray-300'; ?>"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        function rejectDoc(docId, userId) {
            const reason = prompt("Reason for rejection:", "Document is blurred or incorrect.");
            if (reason) {
                let url = `?id=<?php echo $org_id; ?>&doc_id=${docId}&doc_action=reject&reason=${encodeURIComponent(reason)}`;
                if (userId) url += `&user_id=${userId}`;
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
