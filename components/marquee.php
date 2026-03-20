<?php
$notifications = get_active_notifications($_SESSION['org_id'] ?? null);
if (!empty($notifications)):
?>
<div class="bg-primary text-white py-2.5 overflow-hidden relative z-[100] border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 flex items-center">
        <div class="shrink-0 bg-white/10 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest mr-6 border border-white/10 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
            Updates
        </div>
        <div class="flex-grow overflow-hidden relative">
            <div class="whitespace-nowrap inline-block animate-marquee hover:pause">
                <?php for($i=0; $i<3; $i++): ?>
                    <?php foreach ($notifications as $n): ?>
                        <span class="inline-flex items-center gap-2 mx-12 text-[11px] font-bold uppercase tracking-wider">
                            <span class="w-1 h-1 bg-white/30 rounded-full"></span>
                            <?php echo $n['message']; ?>
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
    100% { transform: translateX(-33.333%); }
}
.animate-marquee {
    display: inline-block;
    animation: marquee 30s linear infinite;
}
.hover\:pause:hover {
    animation-play-state: paused;
}
</style>
<?php endif; ?>
