<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['org_admin', 'org_user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] === 'org_user') {
    header("Location: documents.php");
    exit;
}

$org_id = $_SESSION['org_id'];

// Check organization status
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$org_id]);
$org = $stmt->fetch();

if ($org['status'] == 'disabled') {
    die("Your organization has been disabled by the administrator.");
}

// Stats
$user_count = $pdo->prepare("SELECT COUNT(*) FROM users WHERE organization_id = ?");
$user_count->execute([$org_id]);
$total_users = $user_count->fetchColumn();

$doc_count = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE organization_id = ?");
$doc_count->execute([$org_id]);
$total_docs = $doc_count->fetchColumn();

// Notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE organization_id = ? OR organization_id IS NULL ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$org_id]);
$notifications = $notifs->fetchAll();

// Price and Deadline
$price_stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'insurance_price'");
$price_stmt->execute();
$price = $price_stmt->fetchColumn() ?: "30,000";

$deadline_stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'portal_deadline'");
$deadline_stmt->execute();
$deadline = $deadline_stmt->fetchColumn() ?: "Not Set";

// Check for rejected docs
$rejected_docs_stmt = $pdo->prepare("SELECT * FROM documents WHERE organization_id = ? AND status = 'rejected'");
$rejected_docs_stmt->execute([$org_id]);
$rejected_docs = $rejected_docs_stmt->fetchAll();

// Check for pending docs
$pending_docs_stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE organization_id = ? AND status = 'pending'");
$pending_docs_stmt->execute([$org_id]);
$has_pending_docs = $pending_docs_stmt->fetchColumn() > 0;

// Check for approved docs
$approved_docs_stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE organization_id = ? AND status = 'approved'");
$approved_docs_stmt->execute([$org_id]);
$has_approved_docs = $approved_docs_stmt->fetchColumn() > 0;

$toast = get_toast_message();

// Handle new upload from dashboard if needed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_doc'])) {
    $upload_dir = '../uploads/docs/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_name = $_FILES['new_doc']['name'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = "org_" . $org_id . "_re_" . time() . "." . $file_ext;
    $target_file = $upload_dir . $new_file_name;
    
    if (move_uploaded_file($_FILES['new_doc']['tmp_name'], $target_file)) {
        $stmt = $pdo->prepare("INSERT INTO documents (organization_id, user_id, file_path, file_name, file_type, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$org_id, $_SESSION['user_id'], 'uploads/docs/' . $new_file_name, $file_name, $file_ext]);
        
        // Update org status back to pending if it was rejected
        $pdo->prepare("UPDATE organizations SET status = 'pending' WHERE id = ?")->execute([$org_id]);
        
        set_toast_message("New document submitted for review.");
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Dashboard | BizShield</title>
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
                    <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                        <i class="ph ph-squares-four text-xl"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="users.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                        <i class="ph ph-users text-xl"></i>
                        <span>Team Members</span>
                    </a>
                    <a href="documents.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
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
                    <div>
                        <p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest truncate w-32"><?php echo $_SESSION['org_name']; ?></p>
                    </div>
                </div>
                <a href="../logout.php" class="w-full bg-red-50 text-red-600 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-red-100 transition-all">
                    <i class="ph ph-sign-out"></i>
                    Logout
                </a>
            </div>
        </aside>

        <main class="flex-grow p-6 lg:p-10 pb-32">
            <?php if ($toast): ?>
            <div id="toast" class="fixed top-24 right-4 lg:right-10 z-[2000] bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-green-500' : 'border-l-orange-500'; ?> flex items-center gap-4 animate-bounce-in">
                <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-green-50 text-green-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center"><i class="ph <?php echo $toast['type'] == 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-2xl font-bold"></i></div>
                <div><p class="text-xs text-gray-400 font-bold uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p><p class="text-sm font-bold text-gray-700"><?php echo $toast['message']; ?></p></div>
            </div>
            <script>setTimeout(() => { document.getElementById('toast')?.remove(); }, 4000);</script>
            <?php endif; ?>

            <!-- Header -->
            <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10" data-aos="fade-down">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black text-gray-900 mb-1">Welcome back, <?php echo $_SESSION['username']; ?>!</h1>
                    <p class="text-sm text-gray-400 font-medium">Operations hub for <?php echo $_SESSION['org_name']; ?>.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="px-6 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Insurance Price</p>
                        <?php $show_price = is_numeric($price) ? number_format((float)$price) : $price; ?>
                        <p class="text-sm font-bold text-primary">RWF <?php echo $show_price; ?></p>
                    </div>
                    <div class="px-6 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Entry Deadline</p>
                        <p class="text-sm font-bold text-red-500"><?php echo $deadline; ?></p>
                    </div>
                </div>
            </header>

            <!-- Status Alert System -->
            <?php if ($org['status'] != 'approved'): ?>
                <?php if (!empty($rejected_docs)): ?>
                <!-- Rejection Alert -->
                <div class="mb-10" data-aos="fade-up">
                    <div class="bg-red-50 border border-red-100 rounded-[2.5rem] p-8 lg:p-12 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-48 h-48 bg-red-500/5 rounded-full group-hover:scale-110 transition-transform"></div>
                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-red-500 text-white rounded-xl flex items-center justify-center font-black animate-pulse">
                                        <i class="ph ph-warning-octagon text-xl"></i>
                                    </div>
                                    <h2 class="text-2xl font-black text-red-600 uppercase tracking-tight">Documents Rejected</h2>
                                </div>
                                <p class="text-sm text-red-800 font-medium max-w-lg leading-relaxed mb-6">Your organization's admission is on hold because some documents were rejected. Please review the reasons and re-upload.</p>
                                
                                <div class="space-y-4 max-w-xl">
                                    <?php foreach ($rejected_docs as $rd): ?>
                                    <div class="bg-white/50 backdrop-blur-sm border border-red-200/50 p-6 rounded-2xl">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="ph ph-file-x text-red-500 font-bold"></i>
                                            <p class="text-[10px] text-red-400 font-black uppercase tracking-[.2em]"><?php echo $rd['file_name']; ?></p>
                                        </div>
                                        <p class="text-xs font-bold text-gray-700 italic">"<?php echo $rd['rejection_reason']; ?>"</p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-red-100 w-full lg:w-96 shrink-0">
                                <h3 class="text-sm font-black text-gray-900 mb-4 uppercase tracking-widest flex items-center gap-2">
                                    <i class="ph ph-upload-simple"></i> Re-upload Document
                                </h3>
                                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    <label class="group border-2 border-dashed border-red-100 rounded-2xl p-10 flex flex-col items-center justify-center cursor-pointer hover:bg-red-50 transition-all border-spacing-4">
                                        <i class="ph ph-cloud-arrow-up text-4xl text-red-300 group-hover:scale-110 transition-transform mb-3"></i>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Click to Select<br>New Certificate</span>
                                        <input type="file" name="new_doc" required class="hidden" onchange="this.form.submit()">
                                    </label>
                                    <p class="text-[8px] text-gray-400 text-center uppercase font-bold tracking-[.3em]">PDF, JPG, PNG up to 10MB</p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php elseif ($has_approved_docs && !$has_pending_docs): ?>
                <!-- Docs Approved, Org Admission Pending -->
                <div class="mb-10" data-aos="fade-up">
                    <div class="bg-orange-50 border border-orange-100 rounded-[2.5rem] p-10 lg:p-14 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-64 h-64 bg-orange-500/5 rounded-full group-hover:scale-110 transition-transform duration-700"></div>
                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-8">
                            <div class="w-16 h-16 bg-orange-500 text-white rounded-3xl flex items-center justify-center text-3xl font-black shadow-lg shadow-orange-900/20">
                                <i class="ph ph-circle-dashed animate-spin"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-orange-600 mb-2 uppercase tracking-tight">Documents Verified ✅</h2>
                                <p class="text-sm text-gray-600 font-medium max-w-xl leading-relaxed">Your business papers are now successfully verified! You are now only awaiting the **Final Admission** from the global administrator. You will be notified once complete.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- General Pending Alert -->
                <div class="mb-10" data-aos="fade-up">
                    <div class="bg-blue-50 border border-blue-100 rounded-[2.5rem] p-10 lg:p-14 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-64 h-64 bg-blue-500/5 rounded-full group-hover:scale-110 transition-transform duration-700"></div>
                        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-8">
                            <div class="w-16 h-16 bg-blue-500 text-white rounded-3xl flex items-center justify-center text-3xl font-black shadow-lg shadow-blue-900/20">
                                <i class="ph ph-hourglass-medium animate-pulse"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-blue-600 mb-2 uppercase tracking-tight">Review in Progress 🕒</h2>
                                <p class="text-sm text-gray-600 font-medium max-w-xl leading-relaxed">Our administrative team is currently reviewing your uploaded papers. This usually takes 24-48 hours. Please check back soon!</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10" data-aos="fade-up">
                <a href="users.php" class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group hover:border-primary/20 transition-all duration-300">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6">
                        <i class="ph ph-users text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Total Users</p>
                    <h3 class="text-3xl font-black text-gray-900"><?php echo $total_users; ?></h3>
                </a>

                <a href="documents.php" class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group hover:border-blue-100 transition-all duration-300">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 mb-6">
                        <i class="ph ph-files text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Uploaded Papers</p>
                    <h3 class="text-3xl font-black text-gray-900"><?php echo $total_docs; ?></h3>
                </a>

                <div class="p-8 <?php echo $org['status'] == 'rejected' ? 'bg-red-500' : 'bg-primary'; ?> text-white rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                        <i class="ph <?php echo $org['status'] == 'rejected' ? 'ph-warning' : 'ph-user-check'; ?> text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-green-100 mb-1 font-semibold">Account Status</p>
                    <h3 class="text-2xl font-black uppercase tracking-widest text-white"><?php echo $org['status']; ?></h3>
                </div>
            </div>

            <!-- Requirements Guidance -->
            <div class="mb-10" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-primary/5 border border-primary/10 rounded-[2.5rem] p-8 lg:p-12 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-primary/5 rounded-full group-hover:scale-110 transition-transform"></div>
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <div>
                            <h2 class="text-2xl font-black text-primary mb-2">Required Insurance Papers</h2>
                            <p class="text-sm text-gray-500 font-medium max-w-lg leading-relaxed">To qualify for the BizShield insurance coverage, ensure your organization has uploaded the following clear scanned documents.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <div class="px-5 py-3 bg-white rounded-2xl border border-primary/20 shadow-sm flex items-center gap-3">
                                <i class="ph ph-check-circle text-primary font-bold"></i>
                                <span class="text-xs font-bold text-gray-700">RDB Certificate</span>
                            </div>
                            <div class="px-5 py-3 bg-white rounded-2xl border border-primary/20 shadow-sm flex items-center gap-3">
                                <i class="ph ph-check-circle text-primary font-bold"></i>
                                <span class="text-xs font-bold text-gray-700">VAT/TIN Registration</span>
                            </div>
                            <div class="px-5 py-3 bg-white rounded-2xl border border-primary/20 shadow-sm flex items-center gap-3">
                                <i class="ph ph-check-circle text-primary font-bold"></i>
                                <span class="text-xs font-bold text-gray-700">Sector Recommendation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up" data-aos-delay="100">
                <h2 class="text-xl font-bold text-gray-900 mb-8 px-2 flex items-center gap-3">
                    <i class="ph ph-bell-ringing text-primary font-bold"></i>
                    Latest Notifications
                </h2>
                <div class="space-y-4">
                    <?php foreach ($notifications as $n): ?>
                    <div class="p-6 bg-gray-50 border border-gray-50 rounded-[2rem] hover:bg-white hover:border-primary/20 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <?php 
                            $icons = [
                                'info' => 'ph-info bg-blue-100 text-blue-600',
                                'payment' => 'ph-wallet bg-primary-light bg-opacity-10 text-primary',
                                'deadline' => 'ph-timer bg-orange-100 text-orange-600',
                                'warning' => 'ph-warning bg-red-100 text-red-600'
                            ];
                            $icon_class = $icons[$n['type']] ?? $icons['info'];
                            ?>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center <?php echo $icon_class; ?> shrink-0">
                                <i class="ph <?php echo explode(' ', $icon_class)[0]; ?> text-xl font-bold"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary"><?php echo $n['type']; ?></span>
                                    <span class="text-[10px] text-gray-300">•</span>
                                    <span class="text-[10px] text-gray-400 font-bold"><?php echo date('M d, Y', strtotime($n['created_at'])); ?></span>
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed font-medium"><?php echo $n['message']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($notifications)): ?>
                    <div class="py-12 text-center">
                        <p class="text-gray-400 font-medium">No notifications yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
