<nav class="bg-white hidden md:block select-none border-0 m-0 p-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-primary tracking-tight">BizShield</span>
                </a>
            </div>
            <div class="flex items-center space-x-8 text-sm font-bold">
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary underline decoration-2 underline-offset-4' : 'text-gray-600 hover:text-primary transition-colors'; ?>">Home</a>
                <a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-primary underline decoration-2 underline-offset-4' : 'text-gray-600 hover:text-primary transition-colors'; ?>">About Us</a>
                <a href="how-it-works.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'how-it-works.php' ? 'text-primary underline decoration-2 underline-offset-4' : 'text-gray-600 hover:text-primary transition-colors'; ?>">How It Works</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="admin/dashboard.php" class="bg-primary text-white px-6 py-2 rounded-full hover:bg-primary-light transition-all shadow-lg active:scale-95">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="organization/dashboard.php" class="bg-primary text-white px-6 py-2 rounded-full hover:bg-primary-light transition-all shadow-lg active:scale-95">My Portal</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="text-primary hover:underline">Login</a>
                    <a href="register.php" class="bg-primary text-white px-6 py-2 rounded-full hover:bg-primary-light transition-all shadow-lg active:scale-95">Join Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

