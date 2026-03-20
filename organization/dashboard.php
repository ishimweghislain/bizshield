<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['org_admin', 'org_user'])) {
    header("Location: ../login.php");
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $_SESSION['org_name']; ?></title>
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

    <main class="flex-grow p-10">
        <!-- Header -->
        <header class="flex items-center justify-between mb-10" data-aos="fade-down">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back, <?php echo $_SESSION['username']; ?>!</h1>
                <p class="text-sm text-gray-400 font-medium">Here's what's happening in your organization dashboard.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-6 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Insurance Price</p>
                    <p class="text-sm font-bold text-primary">RWF <?php echo $price; ?></p>
                </div>
                <div class="px-6 py-3 bg-white border border-gray-100 rounded-2xl shadow-sm text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Entry Deadline</p>
                    <p class="text-sm font-bold text-red-500"><?php echo $deadline; ?></p>
                </div>
            </div>
        </header>

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

            <div class="p-8 bg-primary text-white rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                    <i class="ph ph-user-check text-2xl font-bold"></i>
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

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
