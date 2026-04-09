<nav class="md:hidden fixed bottom-6 inset-x-0 mx-auto w-[90%] bg-white border border-gray-100 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] z-[9999] px-6 py-4">
    <div class="flex justify-between items-center text-gray-500">
        <a href="index.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] font-bold uppercase">Home</span>
        </a>
        <a href="about.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'text-primary' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[10px] font-bold uppercase">About</span>
        </a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'organization/dashboard.php'; ?>" class="flex flex-col items-center gap-1 text-primary">
            <i class="ph ph-user-circle text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Portal</span>
        </a>
        <?php else: ?>
        <a href="login.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'text-primary' : ''; ?>">
            <i class="ph ph-sign-in text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Sign In</span>
        </a>
        <!-- <a href="register.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'text-primary' : ''; ?>">
            <i class="ph ph-user-plus text-2xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Join</span>
        </a> -->
        <?php endif; ?>
    </div>
</nav>
