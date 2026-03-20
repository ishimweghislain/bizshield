<?php
$latest_notif = get_latest_notification($_SESSION['org_id'] ?? null);
if ($latest_notif):
?>
<div class="bg-primary text-white py-2 overflow-hidden sticky top-0 z-[3000] shadow-md border-b border-white/10">
    <div class="whitespace-nowrap inline-block animate-marquee font-bold text-[10px] lg:text-xs uppercase tracking-[0.2em]">
        <span class="px-10"><i class="ph ph-bell-ringing mr-2"></i> <?php echo $latest_notif['message']; ?></span>
        <span class="px-10"><i class="ph ph-bell-ringing mr-2"></i> <?php echo $latest_notif['message']; ?></span>
        <span class="px-10"><i class="ph ph-bell-ringing mr-2"></i> <?php echo $latest_notif['message']; ?></span>
    </div>
</div>

<style>
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-33.33%); }
}
.animate-marquee {
    display: inline-block;
    animation: marquee 15s linear infinite;
}
</style>
<?php endif; ?>
