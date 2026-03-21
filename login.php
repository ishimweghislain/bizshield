<?php
require_once 'config.php';

$error = '';
$toast = get_toast_message();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT u.*, o.status as org_status, o.name as org_name FROM users u LEFT JOIN organizations o ON u.organization_id = o.id WHERE u.username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == 'disabled') {
            $error = "Your account has been disabled.";
        } else if ($user['role'] != 'admin' && $user['org_status'] == 'disabled') {
            $error = "Your organization has been disabled by the administrator.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['org_id'] = $user['organization_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['org_name'] = $user['org_name'];

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: organization/dashboard.php");
            }
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="text-[85%]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <title>Login | BizShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#064E3B', light: '#14532D' },
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50/50">
    <header class="sticky top-0 w-full z-[1000] bg-white">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <main class="min-h-[80vh] flex items-center justify-center px-4 py-12" data-aos="fade-up">
        <div class="max-w-md w-full bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden">
            <div class="bg-primary p-10 text-white text-center">
                <h1 class="text-3xl font-bold mb-3 font-serif">Welcome Back</h1>
                <p class="text-green-100 text-xs font-bold uppercase tracking-widest italic">Login to your dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mx-10 mt-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-sm"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($toast): ?>
                <div class="mx-10 mt-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl text-sm"><?php echo $toast['message']; ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="p-10 space-y-6 text-sm font-medium">
                <div class="space-y-2">
                    <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" name="username" required placeholder="Enter username" class="w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <i class="ph ph-lock absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" name="login" class="w-full bg-primary text-white py-5 rounded-2xl font-bold text-lg hover:bg-primary-light transition-all shadow-xl shadow-green-900/10 active:scale-[0.98]">
                        Sign In
                    </button>
                    <p class="text-xs text-center text-gray-500 mt-6 uppercase font-bold">Don't have an account? <a href="register.php" class="text-primary hover:underline">Register</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer class="mt-auto">
        <?php include 'components/footer.php'; ?>
    </footer>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>
</html>
