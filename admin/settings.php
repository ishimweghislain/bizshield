<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $price = $_POST['insurance_price'];
    $deadline = $_POST['portal_deadline'];
    $disable_all = isset($_POST['disable_all']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('insurance_price', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$price, $price]);

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('portal_deadline', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$deadline, $deadline]);

    if ($disable_all) {
        $pdo->query("UPDATE organizations SET status = 'disabled' WHERE status != 'pending'");
        set_toast_message("All active organizations have been disabled.", "warning");
    } else {
        set_toast_message("Portal settings updated successfully.");
    }
    header("Location: settings.php");
    exit;
}

// Fetch settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Settings | BizShield</title>
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
<body class="bg-gray-50/50 flex min-h-screen font-sans">
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
                <a href="organizations.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-buildings text-xl"></i>
                    <span>Organizations</span>
                </a>
                <a href="documents.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-files text-xl"></i>
                    <span>Documents</span>
                </a>
                <a href="notifications.php" class="sidebar-link text-gray-400 hover:text-primary hover:bg-green-50/50 flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
                    <i class="ph ph-bell text-xl"></i>
                    <span>Notifications</span>
                </a>
                <a href="settings.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
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
            <div id="toast" class="fixed top-10 right-4 lg:right-10 z-[2000] bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-green-500' : 'border-l-orange-500'; ?> flex items-center gap-4 animate-bounce-in">
                <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-green-50 text-green-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center"><i class="ph <?php echo $toast['type'] == 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-2xl font-bold"></i></div>
                <div><p class="text-xs text-gray-400 font-bold uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p><p class="text-sm font-bold text-gray-700"><?php echo $toast['message']; ?></p></div>
            </div>
            <script>setTimeout(() => { document.getElementById('toast')?.remove(); }, 3000);</script>
        <?php endif; ?>

        <header class="mb-10" data-aos="fade-down">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Portal Configuration</h1>
            <p class="text-sm text-gray-400 font-medium">Control the global parameters and portal access.</p>
        </header>

        <div class="max-w-3xl">
            <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-12" data-aos="fade-up">
                <form action="" method="POST" class="space-y-10 font-bold">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-4">
                            <label class="text-[10px] text-primary font-black uppercase tracking-widest">Global Insurance Fee (RWF)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-black">RWF</span>
                                <input type="text" name="insurance_price" value="<?php echo $settings['insurance_price'] ?? '30,000'; ?>" placeholder="30,000" class="w-full pl-16 pr-6 py-5 bg-gray-50 border border-gray-100 rounded-[1.5rem] focus:ring-4 focus:ring-primary/10 outline-none transition-all text-gray-700">
                            </div>
                            <p class="text-[10px] text-gray-400 italic">This fee will be displayed to all organizations across the platform.</p>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[10px] text-primary font-black uppercase tracking-widest">Portal Entry Deadline</label>
                            <div class="relative">
                                <i class="ph ph-calendar-blank absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 text-xl font-black"></i>
                                <input type="date" name="portal_deadline" value="<?php echo $settings['portal_deadline'] ?? ''; ?>" class="w-full pl-16 pr-6 py-5 bg-gray-50 border border-gray-100 rounded-[1.5rem] focus:ring-4 focus:ring-primary/10 outline-none transition-all text-gray-700">
                            </div>
                            <p class="text-[10px] text-gray-400 italic">After this date, new registrations may be automatically rejected.</p>
                        </div>
                    </div>

                    <div class="p-8 bg-red-50/50 rounded-[2rem] border border-red-100/50 mt-10">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 bg-red-500 text-white rounded-xl flex items-center justify-center font-black">
                                <i class="ph ph-skull text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-red-600 uppercase tracking-widest">Danger Zone</h3>
                                <p class="text-[10px] text-red-400 font-semibold">Immediate action affecting all users.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="disable_all" class="peer hidden">
                                <div class="w-12 h-6 bg-gray-200 rounded-full peer-checked:bg-red-500 transition-all after:content-[''] after:absolute after:top-1 after:left-1 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-6"></div>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-700 uppercase tracking-tight">Disable All Organizations</p>
                                <p class="text-[10px] text-gray-400 font-medium">Instantly lock access for all currently approved organizations.</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-6">
                        <button type="submit" name="update_settings" class="w-full bg-primary text-white py-6 rounded-[2rem] font-black text-xl hover:bg-primary-light transition-all shadow-2xl shadow-green-900/10 active:scale-[0.98] flex items-center justify-center gap-4">
                            <i class="ph ph-floppy-disk-back"></i>
                            Save Config Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
