<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Stats
$total_orgs = $pdo->query("SELECT COUNT(*) FROM organizations")->fetchColumn();
$pending_orgs = $pdo->query("SELECT COUNT(*) FROM organizations WHERE status = 'pending'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_docs = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();

// Latest Organizations
$latest_orgs = $pdo->query("SELECT * FROM organizations ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Price and Deadline from settings
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
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Admin Dashboard | BizShield</title>
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
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active {
            background-color: #064E3B;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.1), 0 4px 6px -2px rgba(6, 78, 59, 0.05);
        }
    </style>
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
                    <a href="organizations.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                        <i class="ph ph-buildings text-xl"></i>
                        <span>Organizations</span>
                        <?php if ($pending_orgs > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?php echo $pending_orgs; ?></span>
                        <?php endif; ?>
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
                    <div>
                        <p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['username']; ?></p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Administrator</p>
                    </div>
                </div>
                <a href="../logout.php" class="w-full bg-red-50 text-red-600 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 hover:bg-red-100 transition-all">
                    <i class="ph ph-sign-out"></i>
                    Logout
                </a>
            </div>
        </aside>

        <main class="flex-grow p-6 lg:p-10 pb-32">
            <!-- Header -->
            <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10" data-aos="fade-down">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black text-gray-900 mb-1">Administrative Overview</h1>
                    <p class="text-sm text-gray-400 font-medium">Global operational status and management.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10" data-aos="fade-up" data-aos-delay="100">
                <a href="organizations.php" class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group hover:border-primary/20 transition-all duration-300">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6">
                        <i class="ph ph-buildings text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Total Organizations</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-gray-900"><?php echo $total_orgs; ?></h3>
                    </div>
                </a>

                <a href="organizations.php" class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group hover:border-red-100 transition-all duration-300">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 mb-6">
                        <i class="ph ph-warning-circle text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Pending Approval</p>
                    <h3 class="text-3xl font-black text-gray-900"><?php echo $pending_orgs; ?></h3>
                </a>

                <div class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 mb-6">
                        <i class="ph ph-users text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Total Users</p>
                    <h3 class="text-3xl font-black text-gray-900"><?php echo $total_users; ?></h3>
                </div>

                <a href="documents.php" class="p-8 bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 relative overflow-hidden group hover:border-orange-100 transition-all duration-300">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-orange-50 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 mb-6">
                        <i class="ph ph-file-arrow-down text-2xl font-bold"></i>
                    </div>
                    <p class="text-sm text-gray-400 mb-1 font-semibold">Total Documents</p>
                    <h3 class="text-3xl font-black text-gray-900"><?php echo $total_docs; ?></h3>
                </a>
            </div>

            <!-- Recent Organizations Table -->
            <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between mb-8 px-2 text-wrap">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Recent Registrations</h2>
                        <p class="text-xs text-gray-400 font-medium">Review and approve new businesses.</p>
                    </div>
                    <a href="organizations.php" class="text-primary font-bold text-xs hover:underline flex items-center gap-1">View All <i class="ph ph-arrow-right"></i></a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left py-4 border-b border-gray-50">
                                <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Organization</th>
                                <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Owner</th>
                                <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Registered At</th>
                                <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($latest_orgs as $org): ?>
                            <tr class="group hover:bg-gray-50/50 transition-all">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-primary font-bold">
                                            <?php echo strtoupper(substr($org['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 truncate w-32 lg:w-48"><?php echo $org['name']; ?></p>
                                            <p class="text-[10px] text-gray-400"><?php echo $org['email']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-gray-600">
                                    <?php echo $org['owner_name']; ?>
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-400">
                                    <?php echo date('M d, Y', strtotime($org['created_at'])); ?>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center">
                                        <?php 
                                        $status_classes = [
                                            'pending' => 'bg-orange-50 text-orange-600',
                                            'approved' => 'bg-green-50 text-green-600',
                                            'rejected' => 'bg-red-50 text-red-600',
                                            'disabled' => 'bg-gray-100 text-gray-500'
                                        ];
                                        $status_class = $status_classes[$org['status']] ?? 'bg-gray-100 text-gray-500';
                                        ?>
                                        <span class="<?php echo $status_class; ?> text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                                            <?php echo $org['status']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="view_org_docs.php?id=<?php echo $org['id']; ?>" class="p-2 text-gray-400 hover:text-primary transition-all">
                                        <i class="ph ph-caret-right text-xl font-bold"></i>
                                    </a>
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
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>
