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
    $action = $_GET['doc_action'];
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE documents SET status = 'approved' WHERE id = ?");
        $stmt->execute([$doc_id]);
        set_toast_message("Document approved.");
    } elseif ($action == 'reject') {
        $reason = $_GET['reason'] ?? 'Document does not meet requirements.';
        $stmt = $pdo->prepare("UPDATE documents SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $doc_id]);
        
        // Notify Org
        $stmt = $pdo->prepare("INSERT INTO notifications (organization_id, message, type) VALUES (?, ?, 'warning')");
        $stmt->execute([$org_id, "Your document was rejected: " . $reason]);
        
        set_toast_message("Document rejected.", "warning");
    }
    header("Location: view_org_docs.php?id=" . $org_id);
    exit;
}

// Fetch Organization Details
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$org_id]);
$org = $stmt->fetch();

if (!$org) {
    header("Location: organizations.php");
    exit;
}

// Fetch documents
$stmt = $pdo->prepare("SELECT d.*, u.username as uploader FROM documents d JOIN users u ON d.user_id = u.id WHERE d.organization_id = ? ORDER BY d.created_at DESC");
$stmt->execute([$org_id]);
$documents = $stmt->fetchAll();

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

        <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10" data-aos="fade-down">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-primary text-white rounded-[2rem] flex items-center justify-center text-3xl font-black shadow-xl shadow-green-900/10">
                    <?php echo strtoupper(substr($org['name'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black text-gray-900"><?php echo $org['name']; ?></h1>
                    <div class="flex flex-wrap gap-3 mt-1">
                        <span class="text-[10px] font-black uppercase tracking-widest bg-primary/10 text-primary px-3 py-1 rounded-full border border-primary/20"><?php echo $org['status']; ?></span>
                        <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1"><i class="ph ph-calendar"></i> Joined <?php echo date('M Y', strtotime($org['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                 <a href="organizations.php" class="lg:hidden bg-white p-4 rounded-2xl shadow-sm border border-gray-100 text-gray-400"><i class="ph ph-arrow-left text-xl"></i></a>
                 <div class="bg-white px-6 py-4 rounded-[1.5rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total Staff</p>
                        <p class="text-xl font-black text-primary"><?php echo count($users); ?></p>
                    </div>
                    <div class="w-10 h-10 bg-primary/5 rounded-xl flex items-center justify-center text-primary"><i class="ph ph-users-three text-2xl"></i></div>
                 </div>
            </div>
        </header>

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
                            $status_classes = [
                                'pending' => 'bg-orange-50 text-orange-600',
                                'approved' => 'bg-green-50 text-green-600',
                                'rejected' => 'bg-red-50 text-red-600'
                            ];
                            ?>
                            <span class="text-[8px] font-black uppercase px-2 py-1 rounded-full <?php echo $status_classes[$doc['status']] ?? 'bg-gray-50'; ?>">
                                <?php echo $doc['status']; ?>
                            </span>
                         </div>
                         <div class="flex items-center gap-4 mb-6 <?php echo $is_rejected ? 'blur-[1px]' : ''; ?>">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-primary text-2xl group-hover:scale-110 transition-transform">
                                <?php echo in_array(strtolower($doc['file_type']), ['jpg','jpeg','png']) ? '<i class="ph ph-image"></i>' : '<i class="ph ph-file-pdf"></i>'; ?>
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></p>
                                <p class="text-[10px] text-gray-400 font-medium">By: <?php echo $doc['uploader']; ?></p>
                            </div>
                         </div>
                         
                         <div class="flex items-center gap-2 pt-4 border-t border-gray-50">
                            <a href="../<?php echo $doc['file_path']; ?>" target="_blank" class="flex-grow bg-primary text-white text-[10px] font-black py-3 rounded-xl text-center hover:bg-primary-light transition-all uppercase tracking-widest shadow-lg shadow-green-900/10">View File</a>
                            <?php if ($doc['status'] == 'pending'): ?>
                            <a href="?id=<?php echo $org_id; ?>&doc_id=<?php echo $doc['id']; ?>&doc_action=approve" class="p-3 bg-green-50 text-green-600 rounded-xl hover:bg-green-500 hover:text-white transition-all"><i class="ph ph-check-bold font-bold"></i></a>
                            <button onclick="rejectDoc(<?php echo $doc['id']; ?>)" class="p-3 bg-red-50 text-red-600 rounded-xl hover:bg-red-500 hover:text-white transition-all"><i class="ph ph-x-bold font-bold"></i></button>
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
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $u['role'] == 'org_admin' ? 'Super Admin' : 'Staff'; ?></p>
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
        function rejectDoc(docId) {
            const reason = prompt("Reason for rejection:", "Document is blurred or incorrect.");
            if (reason) {
                window.location.href = `?id=<?php echo $org_id; ?>&doc_id=${docId}&doc_action=reject&reason=${encodeURIComponent(reason)}`;
            }
        }
    </script>
</body>
</html>
