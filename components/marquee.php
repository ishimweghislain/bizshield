<?php
$notifications = get_active_notifications($_SESSION['org_id'] ?? null);
if (!empty($notifications)):
    // Check if we are in the system (admin or organization folders)
    $is_in_system = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/organization/') !== false);
?>
<div class="<?php echo $is_in_system ? 'bg-primary text-white' : 'bg-gray-900 text-green-400'; ?> py-1.5 overflow-hidden relative z-[100] border-b border-white/5 select-none h-9 flex items-center">
    <div class="max-w-7xl mx-auto px-4 flex items-center w-full">
        <div class="shrink-0 flex items-center gap-2 mr-6">
            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></div>
            <span class="text-[9px] font-black uppercase tracking-[0.2em] opacity-80">Announcements</span>
        </div>
        
        <div class="flex-grow overflow-hidden relative h-full flex items-center">
            <div class="whitespace-nowrap inline-block animate-marquee hover:pause cursor-default">
                <?php for($i=0; $i<3; $i++): // Seamless loop duplication ?>
                    <?php foreach ($notifications as $n): ?>
                        <span class="inline-flex items-center gap-2 mx-16 text-[11px] font-bold tracking-wide">
                            <i class="ph ph-shield-check text-green-500 opacity-50"></i>
                            <?php echo htmlspecialchars($n['message']); ?>
                        </span>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-33.33333%); }
}
.animate-marquee {
    display: inline-block;
    animation: marquee 40s linear infinite;
    will-change: transform;
}
.hover\:pause:hover {
    animation-play-state: paused;
}
</style>
<?php endif; ?>
