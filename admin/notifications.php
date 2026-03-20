<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {
    $message = $_POST['message'];
    $org_id = !empty($_POST['org_id']) ? $_POST['org_id'] : null;
    $type = $_POST['type'];

    $stmt = $pdo->prepare("INSERT INTO notifications (message, organization_id, type) VALUES (?, ?, ?)");
    $stmt->execute([$message, $org_id, $type]);
    set_toast_message("Notification sent successfully.");
    header("Location: notifications.php");
    exit;
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    set_toast_message("Notification deleted.", "warning");
    header("Location: notifications.php");
    exit;
}

// Organizations for selection
$orgs = $pdo->query("SELECT id, name FROM organizations WHERE status = 'approved'")->fetchAll();

// Notifications list
$notifications = $pdo->query("SELECT n.*, o.name as org_name FROM notifications n LEFT JOIN organizations o ON n.organization_id = o.id ORDER BY n.created_at DESC")->fetchAll();

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Manage Notifications | BizShield</title>
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
                <a href="organizations.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-buildings text-xl"></i>
                    <span>Organizations</span>
                </a>
                <a href="documents.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-files text-xl"></i>
                    <span>Documents</span>
                </a>
                <a href="notifications.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
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

        <header class="mb-10" data-aos="fade-down">
            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 mb-1">Broadcast Notifications</h1>
            <p class="text-sm text-gray-400 font-medium">Send important updates and payment fees to organizations.</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Form -->
            <div class="xl:col-span-1" data-aos="fade-right">
                <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8 sticky top-10">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Send New Message</h2>
                    <form action="" method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Target Platform</label>
                            <select name="org_id" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm font-medium">
                                <option value="">Global (All Organizations)</option>
                                <?php foreach ($orgs as $o): ?>
                                <option value="<?php echo $o['id']; ?>"><?php echo $o['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Notification Type</label>
                            <select name="type" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm font-medium">
                                <option value="info">Information Update</option>
                                <option value="payment">Payment/Invoice Fee</option>
                                <option value="deadline">Deadline Alert</option>
                                <option value="warning">System Warning</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Message Content</label>
                            <textarea name="message" required rows="4" placeholder="Type your message here..." class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm font-medium"></textarea>
                        </div>
                        <button type="submit" name="send_notification" class="w-full bg-primary text-white py-5 rounded-2xl font-bold text-lg hover:bg-primary-light transition-all shadow-xl shadow-green-900/10 active:scale-[0.98] flex items-center justify-center gap-3">
                            <i class="ph ph-paper-plane-tilt"></i>
                            Broadcast Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="xl:col-span-2" data-aos="fade-left">
                <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-8">Recently Broadcasted</h2>
                    <div class="space-y-4">
                        <?php foreach ($notifications as $n): ?>
                        <div class="group p-6 bg-gray-50/50 rounded-[2rem] border border-gray-50 hover:bg-white hover:border-primary/20 transition-all duration-300">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <?php 
                                    $icons = [
                                        'info' => 'ph-info bg-blue-100 text-blue-600',
                                        'payment' => 'ph-wallet bg-primary-light bg-opacity-10 text-primary',
                                        'deadline' => 'ph-timer bg-orange-100 text-orange-600',
                                        'warning' => 'ph-warning bg-red-100 text-red-600'
                                    ];
                                    $icon_class = $icons[$n['type']] ?? $icons['info'];
                                    ?>
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center <?php echo $icon_class; ?>">
                                        <i class="ph <?php echo explode(' ', $icon_class)[0]; ?> text-xl font-bold"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                            <?php echo $n['org_name'] ? "To: " . $n['org_name'] : "To: Global Audience"; ?>
                                        </p>
                                        <p class="text-xs text-gray-400 font-medium"><?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?></p>
                                    </div>
                                </div>
                                <a href="?delete=<?php echo $n['id']; ?>" class="p-2 text-gray-300 hover:text-red-500 transition-all">
                                    <i class="ph ph-trash text-lg"></i>
                                </a>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed font-medium"><?php echo $n['message']; ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($notifications)): ?>
                        <div class="py-12 text-center">
                            <p class="text-gray-400 font-medium">No broadcasted messages yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
