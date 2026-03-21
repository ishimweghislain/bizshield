<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['org_admin', 'org_user'])) {
    header("Location: ../login.php");
    exit;
}

$org_id = $_SESSION['org_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch User Requirement (for org_user)
$required_doc = null;
if ($role === 'org_user') {
    $stmt = $pdo->prepare("SELECT required_doc FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $required_doc = $stmt->fetchColumn();
}

// Handle Organization Admin Approval/Rejection
if ($role === 'org_admin' && isset($_GET['action']) && isset($_GET['doc_id'])) {
    $doc_id = $_GET['doc_id'];
    $action = $_GET['action'];
    $reason = $_GET['reason'] ?? 'Needs improvement.';

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE documents SET status = 'verified' WHERE id = ? AND organization_id = ?");
        $stmt->execute([$doc_id, $org_id]);
        set_toast_message("Document verified and sent for global approval.");
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE documents SET status = 'rejected', rejection_reason = ? WHERE id = ? AND organization_id = ?");
        $stmt->execute([$reason, $doc_id, $org_id]);
        set_toast_message("Document rejected.", "warning");
    }
    header("Location: documents.php");
    exit;
}

// Handle Document Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_doc'])) {
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = '../uploads/docs/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = $_FILES['document']['name'];
        $doc_label = $_POST['doc_label'] ?? 'General Doc';
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "org_" . $org_id . "_user_" . $user_id . "_label_" . str_replace(' ', '_', $doc_label) . "_" . time() . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $db_file_path = 'uploads/docs/' . $new_file_name;
            $stmt = $pdo->prepare("INSERT INTO documents (organization_id, user_id, file_path, file_name, file_type, status, doc_label) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$org_id, $user_id, $db_file_path, $file_name, $file_ext, $doc_label]);
            set_toast_message("Document for $doc_label uploaded successfully.");
        }
    } else {
        set_toast_message("Error uploading file.", "warning");
    }
    header("Location: documents.php");
    exit;
}

// Fetch documents
$id_doc = null;
$contract_doc = null;
if ($role === 'org_user') {
    // Specifically fetch ID and Work Contract for the user
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ? AND doc_label = 'ID' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $id_doc = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ? AND doc_label = 'Work Contract' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $contract_doc = $stmt->fetch();
}

if ($role === 'org_admin') {
    $stmt = $pdo->prepare("SELECT d.*, u.username FROM documents d JOIN users u ON d.user_id = u.id WHERE d.organization_id = ? ORDER BY d.created_at DESC");
    $stmt->execute([$org_id]);
    $documents = $stmt->fetchAll();
} else {
    // For org_user, we already fetched specific ones, but let's also fetch any other general docs if they exist
    $stmt = $pdo->prepare("SELECT d.*, u.username FROM documents d JOIN users u ON d.user_id = u.id WHERE d.user_id = ? ORDER BY d.created_at DESC");
    $stmt->execute([$user_id]);
    $documents = $stmt->fetchAll();
}

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents | <?php echo $_SESSION['org_name']; ?></title>
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
                <?php if ($role === 'org_admin'): ?>
                <a href="dashboard.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-squares-four text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-users text-xl"></i>
                    <span>Team Members</span>
                </a>
                <?php endif; ?>
                <a href="documents.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-files text-xl"></i>
                    <span>My Documents</span>
                </a>
            </nav>
        </div>
        <div class="mt-auto p-8 border-t border-gray-50 bg-gray-50/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div><p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p><p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest truncate w-32"><?php echo $_SESSION['org_name']; ?></p></div>
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

        <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-12" data-aos="fade-down">
            <div>
                <?php if ($role === 'org_user'): ?>
                <h1 class="text-2xl lg:text-4xl font-black text-gray-900 mb-2 leading-tight">Welcome, <span class="text-primary"><?php echo $_SESSION['username']; ?></span></h1>
                <p class="text-xs lg:text-sm text-gray-400 font-bold uppercase tracking-[.2em] flex items-center gap-2">
                    <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                    Complete your profile documentation
                </p>
                <?php else: ?>
                <h1 class="text-xl lg:text-2xl font-bold text-gray-900 mb-1">Team Document Review</h1>
                <p class="text-xs lg:text-sm text-gray-400 font-medium tracking-tighter">Review and verify papers uploaded by your staff.</p>
                <?php endif; ?>
            </div>
            <?php if ($role === 'org_admin'): ?>
            <button onclick="openUploadModal('General Doc')" class="bg-primary text-white p-3 lg:px-6 lg:py-3 rounded-2xl font-bold text-sm flex items-center gap-2 hover:bg-primary-light transition-all shadow-lg active:scale-[0.98]">
                <i class="ph ph-upload-simple text-xl font-bold"></i>
                <span class="hidden lg:inline">Upload New Doc</span>
            </button>
            <?php endif; ?>
        </header>

        <?php if ($role === 'org_user'): ?>
        <!-- Requirement Cards for Staff -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16" data-aos="fade-up">
            <!-- ID Card -->
            <div class="group bg-white border border-gray-100 rounded-[3rem] p-10 shadow-soft hover:border-primary/20 transition-all duration-500 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/5 rounded-full group-hover:scale-125 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-primary/5 rounded-3xl flex items-center justify-center text-primary mb-8 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                        <i class="ph ph-identification-card text-4xl"></i>
                    </div>
                    <div class="mb-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-2">Personal Identification</h2>
                        <p class="text-sm text-gray-400 font-medium leading-relaxed">A clear scanned copy of your valid National ID or Passport front & back.</p>
                    </div>
                    
                    <div class="flex items-center justify-between gap-6">
                        <div class="shrink-0">
                            <?php if ($id_doc): ?>
                                <?php 
                                $s = $id_doc['status'];
                                $c = ($s == 'approved') ? 'text-green-500' : (($s == 'rejected') ? 'text-red-500' : 'text-orange-500');
                                ?>
                                <span class="<?php echo $c; ?> text-[10px] font-black uppercase tracking-widest flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
                                    <i class="ph ph-<?php echo ($s == 'approved' ? 'check-circle' : ($s == 'rejected' ? 'warning-circle' : 'hourglass')); ?> text-lg"></i>
                                    <?php echo $s; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-300 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
                                    <i class="ph ph-circle text-lg"></i>
                                    Missing
                                </span>
                            <?php endif; ?>
                        </div>
                        <button onclick="openUploadModal('ID')" class="flex-1 bg-primary text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary-light transition-all shadow-xl shadow-green-900/10">
                            <?php echo $id_doc ? 'Update ID' : 'Upload ID'; ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Work Contract Card -->
            <div class="group bg-white border border-gray-100 rounded-[3rem] p-10 shadow-soft hover:border-primary/20 transition-all duration-500 relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/5 rounded-full group-hover:scale-125 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-primary/5 rounded-3xl flex items-center justify-center text-primary mb-8 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                        <i class="ph ph-certificate text-4xl"></i>
                    </div>
                    <div class="mb-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-2">Work Contract</h2>
                        <p class="text-sm text-gray-400 font-medium leading-relaxed">The signed employment agreement between you and the organization.</p>
                    </div>
                    
                    <div class="flex items-center justify-between gap-6">
                        <div class="shrink-0">
                            <?php if ($contract_doc): ?>
                                <?php 
                                $s = $contract_doc['status'];
                                $c = ($s == 'approved') ? 'text-green-500' : (($s == 'rejected') ? 'text-red-500' : 'text-orange-500');
                                ?>
                                <span class="<?php echo $c; ?> text-[10px] font-black uppercase tracking-widest flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
                                    <i class="ph ph-<?php echo ($s == 'approved' ? 'check-circle' : ($s == 'rejected' ? 'warning-circle' : 'hourglass')); ?> text-lg"></i>
                                    <?php echo $s; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-300 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100">
                                    <i class="ph ph-circle text-lg"></i>
                                    Missing
                                </span>
                            <?php endif; ?>
                        </div>
                        <button onclick="openUploadModal('Work Contract')" class="flex-1 bg-primary text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary-light transition-all shadow-xl shadow-green-900/10">
                            <?php echo $contract_doc ? 'Update Contract' : 'Upload Contract'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-10">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-[.3em] mb-6 flex items-center gap-3">
                <span class="w-10 h-[2px] bg-primary"></span>
                <?php echo $role === 'org_user' ? 'Submission History' : 'Recent Uploads'; ?>
            </h2>
        </div>

        <!-- Document Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-aos="fade-up">
            <?php foreach ($documents as $doc): 
                $is_rejected = $doc['status'] == 'rejected';
            ?>
            <div class="group p-6 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 hover:border-primary/20 transition-all duration-500 relative overflow-hidden <?php echo $is_rejected ? 'grayscale-[0.5]' : ''; ?>">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4 animate-pulse-slow">
                    <?php 
                    $status_classes = [
                        'pending' => 'bg-orange-50 text-orange-600 border-orange-100',
                        'verified' => 'bg-blue-50 text-blue-600 border-blue-100',
                        'approved' => 'bg-green-50 text-green-600 border-green-100',
                        'rejected' => 'bg-red-50 text-red-600 border-red-100'
                    ];
                    $status_class = $status_classes[$doc['status']] ?? 'bg-gray-100 text-gray-400';
                    ?>
                    <span class="<?php echo $status_class; ?> text-[8px] font-black uppercase px-2 py-1 rounded-full border tracking-tighter shadow-sm">
                        <?php echo $doc['status']; ?>
                    </span>
                </div>

                <div class="flex items-center justify-between mb-4 mt-2 <?php echo $is_rejected ? 'blur-[2px]' : ''; ?>">
                    <div class="w-14 h-14 bg-gray-50 rounded-3xl flex items-center justify-center text-primary group-hover:scale-110 transition-transform shadow-sm">
                        <?php 
                        $ext = strtolower($doc['file_type']);
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg'])) {
                            echo '<i class="ph ph-image text-3xl"></i>';
                        } else if ($ext == 'pdf') {
                            echo '<i class="ph ph-file-pdf text-3xl text-red-500"></i>';
                        } else {
                            echo '<i class="ph ph-file text-3xl"></i>';
                        }
                        ?>
                    </div>
                    <div class="flex gap-2">
                        <a href="../<?php echo $doc['file_path']; ?>" target="_blank" class="w-10 h-10 bg-white border border-gray-50 text-gray-400 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all shadow-sm">
                            <i class="ph ph-eye font-bold"></i>
                        </a>
                        <?php if ($role === 'org_admin' && $doc['status'] === 'pending'): ?>
                        <a href="?action=approve&doc_id=<?php echo $doc['id']; ?>" class="w-10 h-10 bg-green-50 text-green-600 rounded-full flex items-center justify-center hover:bg-green-500 hover:text-white transition-all shadow-sm">
                            <i class="ph ph-check-bold font-bold"></i>
                        </a>
                        <button onclick="rejectDoc(<?php echo $doc['id']; ?>)" class="w-10 h-10 bg-red-50 text-red-600 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                            <i class="ph ph-x-bold font-bold"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="space-y-1 mb-8">
                    <h3 class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></h3>
                    <?php if ($doc['status'] == 'rejected'): ?>
                    <p class="text-[10px] text-red-500 font-bold bg-red-50 p-2 rounded-xl mt-2 leading-tight">
                        <i class="ph ph-warning-circle inline-block mr-1 text-xs"></i>
                        <?php echo $doc['rejection_reason'] ?: 'Rejected without reason.'; ?>
                    </p>
                    <?php else: ?>
                    <p class="text-[10px] text-gray-300 font-bold uppercase tracking-widest italic"><?php echo $doc['file_type']; ?> format</p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                    <div class="flex items-center gap-2">
                         <div class="w-4 h-4 bg-primary/10 text-primary rounded-full flex items-center justify-center text-[8px] font-black">
                            <i class="ph ph-user"></i>
                         </div>
                         <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight"><?php echo $doc['username']; ?></span>
                    </div>
                    <span class="text-[10px] text-gray-300 font-bold italic"><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($documents)): ?>
            <div class="col-span-full py-24 text-center border-2 border-dashed border-gray-100 rounded-[3rem]">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 mx-auto mb-6">
                    <i class="ph ph-upload-simple text-4xl"></i>
                </div>
                <h3 class="text-gray-900 font-bold text-lg mb-2">No documents yet</h3>
                <p class="text-gray-400 text-sm max-w-xs mx-auto">Upload your RDB, VAT, or TIN certificates to get started with BizShield.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Upload Modal -->
        <div id="uploadModal" class="fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl overflow-hidden animate-zoom-in">
                <div class="bg-primary p-12 text-white text-center">
                    <i class="ph ph-file-arrow-up text-5xl mb-4 font-bold"></i>
                    <h2 class="text-2xl font-bold mb-1 font-serif">Upload Document</h2>
                    <p class="text-green-100 text-[10px] font-bold uppercase tracking-widest italic">PDF, DOC, or Images</p>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" class="p-12 space-y-8">
                    <div class="group relative space-y-3">
                        <label class="text-[10px] text-primary font-black uppercase tracking-widest ml-1">Select File</label>
                        <div onclick="document.getElementById('file-input').click()" class="border-4 border-dashed border-gray-50 rounded-[2rem] p-12 text-center hover:bg-gray-50 hover:border-primary/20 transition-all cursor-pointer">
                            <i class="ph ph-cloud-arrow-up text-4xl text-gray-300 group-hover:text-primary mb-4 block"></i>
                            <p id="file-name" class="text-sm text-gray-400 font-bold">Click to browse files</p>
                            <input type="file" name="document" id="file-input" required class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0].name; document.getElementById('file-name').className = 'text-sm text-primary font-black animate-pulse'">
                        </div>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="flex-1 bg-gray-50 text-gray-400 py-5 rounded-[1.5rem] font-bold text-sm hover:bg-gray-100 transition-all">Cancel</button>
                        <button type="submit" name="upload_doc" class="flex-2 bg-primary text-white px-10 py-5 rounded-[1.5rem] font-bold text-sm hover:bg-primary-light transition-all shadow-2xl shadow-green-900/10">Upload Now</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        
        function rejectDoc(docId) {
            const reason = prompt("Enter rejection reason:");
            if (reason) {
                window.location.href = `?action=reject&doc_id=${docId}&reason=${encodeURIComponent(reason)}`;
            }
        }
    </script>
</body>
</html>
