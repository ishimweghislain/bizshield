<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'org_admin') {
    header("Location: ../login.php");
    exit;
}

$org_id = $_SESSION['org_id'];

// Handle User Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'org_user';

    try {
        $stmt = $pdo->prepare("INSERT INTO users (organization_id, username, password, email, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$org_id, $username, $password, $email, $role]);
        set_toast_message("Team member created successfully.");
    } catch (Exception $e) {
        set_toast_message("Error: " . $e->getMessage(), "warning");
    }
    header("Location: users.php");
    exit;
}

// Handle User Deletion
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND organization_id = ? AND role != 'org_admin'");
    $stmt->execute([$_GET['delete'], $org_id]);
    set_toast_message("Team member removed.", "warning");
    header("Location: users.php");
    exit;
}

// Fetch users
$stmt = $pdo->prepare("SELECT * FROM users WHERE organization_id = ? ORDER BY created_at DESC");
$stmt->execute([$org_id]);
$users = $stmt->fetchAll();

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management | <?php echo $_SESSION['org_name']; ?></title>
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
                <a href="users.php" class="sidebar-link active flex items-center gap-3 px-4 py-3.5 rounded-2xl text-sm font-semibold transition-all group">
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
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Team Members</h1>
                <p class="text-sm text-gray-400 font-medium">Manage who has access to your organization portal.</p>
            </div>
            <button onclick="document.getElementById('userModal').classList.remove('hidden')" class="bg-primary text-white px-6 py-3 rounded-2xl font-bold text-sm flex items-center gap-2 hover:bg-primary-light transition-all shadow-lg shadow-green-900/10">
                <i class="ph ph-user-plus text-xl font-bold"></i>
                Add Member
            </button>
        </header>

        <div class="bg-white border border-gray-100 rounded-[2.5rem] shadow-soft shadow-green-900/5 p-8" data-aos="fade-up">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-50">
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Team Member</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Username</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Role</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($users as $user): ?>
                        <tr class="group hover:bg-gray-50/50 transition-all">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-primary font-bold">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900"><?php echo $user['email']; ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-gray-600"><?php echo $user['username']; ?></td>
                            <td class="px-6 py-5">
                                <span class="bg-primary/10 text-primary text-[9px] font-black uppercase px-2 py-1 rounded-full tracking-widest border border-primary/20">
                                    <?php echo $user['role'] == 'org_admin' ? 'ADMIN' : 'MEMBER'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="bg-green-50 text-green-600 text-[9px] font-black uppercase px-2 py-1 rounded-full tracking-widest border border-green-100">
                                    <?php echo $user['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <?php if ($user['role'] != 'org_admin'): ?>
                                <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to remove this member?')" class="p-2 text-gray-300 hover:text-red-500 transition-all">
                                    <i class="ph ph-trash text-xl"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <div id="userModal" class="fixed inset-0 z-[1000] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl overflow-hidden animate-zoom-in" data-aos="zoom-in">
                <div class="bg-primary p-10 text-white text-center">
                    <h2 class="text-2xl font-bold mb-1 font-serif">Add New Team Member</h2>
                    <p class="text-green-100 text-[10px] font-bold uppercase tracking-widest italic">Give them portal access</p>
                </div>
                <form action="" method="POST" class="p-10 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Username</label>
                        <input type="text" name="username" required placeholder="member_username" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" required placeholder="email@company.rw" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 outline-none transition-all text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] text-primary font-bold ml-1 uppercase tracking-widest">Login Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 outline-none transition-all text-sm">
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="document.getElementById('userModal').classList.add('hidden')" class="flex-1 bg-gray-50 text-gray-400 py-4 rounded-2xl font-bold text-sm hover:bg-gray-100 transition-all">Cancel</button>
                        <button type="submit" name="create_user" class="flex-2 bg-primary text-white px-8 py-4 rounded-2xl font-bold text-sm hover:bg-primary-light transition-all shadow-xl shadow-green-900/10">Create Access</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
