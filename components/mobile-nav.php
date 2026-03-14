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
        <a href="how-it-works.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'how-it-works.php' ? 'text-primary' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="text-[10px] font-bold uppercase">How</span>
        </a>
        <a href="register.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'text-primary' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span class="text-[10px] font-bold uppercase">Join</span>
        </a>
    </div>
</nav>
