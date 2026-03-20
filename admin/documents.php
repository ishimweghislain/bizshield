<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Document Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE documents SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        set_toast_message("Document approved.");
        header("Location: documents.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_doc'])) {
    $id = $_POST['doc_id'];
    $reason = $_POST['reason'];
    
    try {
        $pdo->beginTransaction();
        
        // Update document status
        $stmt = $pdo->prepare("UPDATE documents SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $id]);
        
        // Get org_id for notification
        $stmt = $pdo->prepare("SELECT organization_id, file_name FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $doc_info = $stmt->fetch();
        
        // Send notification
        $msg = "Your document '{$doc_info['file_name']}' was rejected. Reason: $reason. Please upload a correct version.";
        $stmt = $pdo->prepare("INSERT INTO notifications (organization_id, message, type) VALUES (?, ?, 'warning')");
        $stmt->execute([$doc_info['organization_id'], $msg]);
        
        $pdo->commit();
        set_toast_message("Document rejected and notification sent.", "warning");
    } catch (Exception $e) {
        $pdo->rollBack();
        set_toast_message("Error: " . $e->getMessage(), "warning");
    }
    header("Location: documents.php");
    exit;
}

// Fetch all documents
$stmt = $pdo->query("SELECT d.*, o.name as org_name, u.username FROM documents d JOIN organizations o ON d.organization_id = o.id JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
$documents = $stmt->fetchAll();

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents | BizShield</title>
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
<body class="bg-gray-50/50 flex min-h-screen">

    <!-- Sidebar (Same as dashboard) -->
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
                <a href="organizations.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-buildings text-xl"></i>
                    <span>Organizations</span>
                </a>
                <a href="documents.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
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
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary">
                    <i class="ph ph-user-circle text-2xl"></i>
                </div>
                <div><p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p><p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Administrator</p></div>
            </div>
            <a href="../logout.php" class="w-full bg-red-50 text-red-600 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-red-100 transition-all">
                <i class="ph ph-sign-out"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-grow p-10">
        <?php if ($toast): ?>
        <div class="fixed top-10 right-10 z-[2000] animate-bounce-in bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-green-500' : 'border-l-orange-500'; ?> flex items-center gap-4">
            <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-green-50 text-green-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center">
                <i class="ph <?php echo $toast['type'] == 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-2xl font-bold"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p>
                <p class="text-sm font-bold text-gray-700"><?php echo $toast['message']; ?></p>
            </div>
        </div>
        <script>setTimeout(() => document.querySelector('.animate-bounce-in').remove(), 3000);</script>
        <?php endif; ?>

        <header class="mb-10" data-aos="fade-down">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Company Documents</h1>
            <p class="text-sm text-gray-400 font-medium">Review and download uploaded certificates from all organizations.</p>
        </header>

        <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($documents as $doc): ?>
                <div class="group p-6 bg-gray-50/50 border border-gray-50 rounded-[2rem] hover:bg-white hover:border-primary/20 transition-all duration-500 relative">
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <?php 
                        $status_classes = [
                            'pending' => 'bg-orange-50 text-orange-600',
                            'approved' => 'bg-green-50 text-green-600',
                            'rejected' => 'bg-red-50 text-red-600'
                        ];
                        $status_class = $status_classes[$doc['status']] ?? 'bg-gray-100 text-gray-500';
                        ?>
                        <span class="<?php echo $status_class; ?> text-[8px] font-black uppercase px-2 py-0.5 rounded-full tracking-tighter shadow-sm border border-black/5">
                            <?php echo $doc['status']; ?>
                        </span>
                    </div>

                    <div class="flex items-center justify-between mb-4 mt-2">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-gray-100 text-primary shadow-sm group-hover:scale-110 transition-transform">
                            <?php 
                            $ext = strtolower($doc['file_type']);
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg'])) {
                                echo '<i class="ph ph-image text-2xl"></i>';
                            } else if ($ext == 'pdf') {
                                echo '<i class="ph ph-file-pdf text-2xl text-red-500"></i>';
                            } else {
                                echo '<i class="ph ph-file text-2xl"></i>';
                            }
                            ?>
                        </div>
                        <div class="flex gap-2">
                            <a href="../<?php echo $doc['file_path']; ?>" target="_blank" class="w-10 h-10 bg-white text-gray-400 border border-gray-100 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm">
                                <i class="ph ph-eye font-bold"></i>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-1 mb-6">
                        <h3 class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $doc['org_name']; ?></p>
                    </div>

                    <!-- Approval Actions -->
                    <?php if ($doc['status'] == 'pending'): ?>
                    <div class="flex gap-2 mb-6">
                        <a href="?action=approve&id=<?php echo $doc['id']; ?>" class="flex-1 bg-green-500 text-white text-[10px] font-bold py-2 rounded-xl text-center hover:bg-green-600 transition-all">APPROVE</a>
                        <button onclick="openRejectModal(<?php echo $doc['id']; ?>, '<?php echo addslashes($doc['file_name']); ?>')" class="flex-1 bg-red-100 text-red-600 text-[10px] font-bold py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all">REJECT</button>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-auto">
                        <div class="flex items-center gap-2">
                             <div class="w-4 h-4 bg-primary/10 text-primary rounded-full flex items-center justify-center text-[8px] font-black">
                                <i class="ph ph-user"></i>
                             </div>
                             <span class="text-[10px] text-gray-400 font-bold"><?php echo $doc['username']; ?></span>
                        </div>
                        <span class="text-[10px] text-gray-300 font-medium italic"><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($documents)): ?>
            <div class="py-24 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mx-auto mb-4">
                    <i class="ph ph-folder-open text-3xl"></i>
                </div>
                <p class="text-gray-400 font-medium">No documents uploaded yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 z-[5000] bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl overflow-hidden" data-aos="zoom-in">
            <div class="bg-red-500 p-10 text-white text-center">
                <i class="ph ph-x-circle text-5xl mb-4"></i>
                <h2 class="text-2xl font-bold mb-1 font-serif">Reject Document</h2>
                <p id="modalDocName" class="text-red-100 text-[10px] font-bold uppercase tracking-widest italic truncate"></p>
            </div>
            <form action="" method="POST" class="p-10 space-y-6">
                <input type="hidden" name="doc_id" id="modalDocId">
                <div class="space-y-2">
                    <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Reason for Rejection</label>
                    <textarea name="reason" required placeholder="e.g. Scanned copy is not clear..." class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-primary/10 outline-none transition-all text-sm h-32"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 bg-gray-50 text-gray-400 py-4 rounded-2xl font-bold text-sm hover:bg-gray-100">Cancel</button>
                    <button type="submit" name="reject_doc" class="flex-2 bg-red-500 text-white px-8 py-4 rounded-2xl font-bold text-sm hover:bg-red-600 transition-all shadow-xl shadow-red-900/10">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        function openRejectModal(id, name) {
            document.getElementById('modalDocId').value = id;
            document.getElementById('modalDocName').textContent = name;
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
        }
    </script>
</body>
</html>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
