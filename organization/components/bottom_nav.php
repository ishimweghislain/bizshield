<!-- Mobile Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 lg:hidden bg-white/80 backdrop-blur-xl border-t border-gray-100 px-6 py-4 flex items-center justify-between z-[1000] shadow-2xl safe-area-bottom">
    <a href="dashboard.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'text-primary' : 'text-gray-400'; ?>">
        <div class="p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-primary/10 rounded-xl' : ''; ?>">
            <i class="ph ph-squares-four text-2xl"></i>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-widest">Home</span>
    </a>
    
    <a href="users.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'text-primary' : 'text-gray-400'; ?>">
        <div class="p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-primary/10 rounded-xl' : ''; ?>">
            <i class="ph ph-users text-2xl"></i>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-widest">Team</span>
    </a>

    <a href="documents.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'documents.php' ? 'text-primary' : 'text-gray-400'; ?>">
        <div class="p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'documents.php' ? 'bg-primary/10 rounded-xl' : ''; ?>">
            <i class="ph ph-files text-2xl"></i>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-widest">Docs</span>
    </a>

    <a href="../logout.php" class="flex flex-col items-center gap-1 text-red-400">
        <div class="p-2">
            <i class="ph ph-sign-out text-2xl"></i>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-widest">Exit</span>
    </a>
</div>

<style>
    .safe-area-bottom {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
    }
</style>
