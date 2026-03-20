<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['org_admin', 'org_user'])) {
    header("Location: ../login.php");
    exit;
}

$org_id = $_SESSION['org_id'];
$user_id = $_SESSION['user_id'];

// Handle Document Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_doc'])) {
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = '../uploads/docs/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = $_FILES['document']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "org_" . $org_id . "_user_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $db_file_path = 'uploads/docs/' . $new_file_name;
            $stmt = $pdo->prepare("INSERT INTO documents (organization_id, user_id, file_path, file_name, file_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$org_id, $user_id, $db_file_path, $file_name, $file_ext]);
            set_toast_message("Document uploaded successfully.");
        }
    } else {
        set_toast_message("Error uploading file.", "warning");
    }
    header("Location: documents.php");
    exit;
}

// Fetch my organization documents
$stmt = $pdo->prepare("SELECT d.*, u.username FROM documents d JOIN users u ON d.user_id = u.id WHERE d.organization_id = ? ORDER BY d.created_at DESC");
$stmt->execute([$org_id]);
$documents = $stmt->fetchAll();

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
                <a href="users.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-users text-xl"></i>
                    <span>Team Members</span>
                </a>
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

    <main class="flex-grow p-10">
        <?php if ($toast): ?>
            <div class="fixed top-10 right-10 z-[2000] bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-green-500' : 'border-l-orange-500'; ?> flex items-center gap-4 animate-bounce-in">
                <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-green-50 text-green-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center"><i class="ph ph-check-circle text-2xl font-bold"></i></div>
                <div><p class="text-xs text-gray-400 font-bold uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p><p class="text-sm font-bold text-gray-700"><?php echo $toast['message']; ?></p></div>
            </div>
        <?php endif; ?>

        <header class="flex items-center justify-between mb-10" data-aos="fade-down">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Upload Certificates</h1>
                <p class="text-sm text-gray-400 font-medium">Keep your business papers updated for insurance coverage.</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-primary text-white px-6 py-3 rounded-2xl font-bold text-sm flex items-center gap-2 hover:bg-primary-light transition-all shadow-lg shadow-green-900/10 active:scale-[0.98]">
                <i class="ph ph-upload-simple text-xl font-bold"></i>
                Upload New Doc
            </button>
        </header>

        <!-- Document Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-aos="fade-up">
            <?php foreach ($documents as $doc): ?>
            <div class="group p-6 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 hover:border-primary/20 transition-all duration-500">
                <div class="flex items-center justify-between mb-6">
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
                    <a href="../<?php echo $doc['file_path']; ?>" download class="w-10 h-10 bg-primary/5 text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                        <i class="ph ph-download-simple font-bold"></i>
                    </a>
                </div>
                <div class="space-y-1 mb-8">
                    <h3 class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></h3>
                    <p class="text-[10px] text-gray-300 font-bold uppercase tracking-widest italic"><?php echo $doc['file_type']; ?> format</p>
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
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
