<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
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
        <header class="mb-10" data-aos="fade-down">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Company Documents</h1>
            <p class="text-sm text-gray-400 font-medium">Review and download uploaded certificates from all organizations.</p>
        </header>

        <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($documents as $doc): ?>
                <div class="group p-6 bg-gray-50/50 border border-gray-50 rounded-[2rem] hover:bg-white hover:border-primary/20 transition-all duration-500">
                    <div class="flex items-center justify-between mb-4">
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
                        <a href="../<?php echo $doc['file_path']; ?>" download class="w-10 h-10 bg-primary/5 text-primary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                            <i class="ph ph-download-simple font-bold"></i>
                        </a>
                    </div>
                    <div class="space-y-1 mb-6">
                        <h3 class="text-sm font-bold text-gray-900 truncate"><?php echo $doc['file_name']; ?></h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $doc['org_name']; ?></p>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
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

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
