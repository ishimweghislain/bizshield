<?php
require_once dirname(__DIR__) . '/config.php';
// Fetch Global Settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch Active Notifications
$notifications = get_active_notifications($_SESSION['org_id'] ?? null);

// Build Marquee Items
$marquee_items = [];

// 1. Add Price & Deadline (Global Announcements)
if (!empty($settings['insurance_price'])) {
    $marquee_items[] = "📢 Premium Coverage Registration Fee: RWF " . number_format($settings['insurance_price']);
}
if (!empty($settings['portal_deadline'])) {
    $marquee_items[] = "🕒 Registration Deadline: " . date('M d, Y', strtotime($settings['portal_deadline']));
}

// 2. Add Notifications
foreach ($notifications as $n) {
    if (!empty($n['message'])) {
        $marquee_items[] = "💡 " . $n['message'];
    }
}

// 3. HARDCOLDED FALLBACK to ensure it's ALWAYS visible
if (empty($marquee_items)) {
    $marquee_items[] = "✅ Welcome to BizShield | Secure Group Insurance for Small Businesses";
    $marquee_items[] = "🚀 Join now to enjoy premium protection with lower group rates.";
    $marquee_items[] = "🔒 100% Digital & Paperless Registration Process.";
}

$is_in_system = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/organization/') !== false);
?>

<div class="marquee-bar <?php echo $is_in_system ? 'system-marquee' : 'site-marquee'; ?>">
    <div class="marquee-content">
        <div class="marquee-track">
            <?php for($i=0; $i<4; $i++): // 4 sets for guaranteed coverage ?>
                <?php foreach ($marquee_items as $item): ?>
                    <span class="marquee-item"><?php echo htmlspecialchars($item); ?></span>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>

<style>
.marquee-bar {
     height: 36px;
     display: flex;
     align-items: center;
     width: 100%;
     overflow: hidden;
     position: relative;
     z-index: 5000;
     border-bottom: 2px solid rgba(255,255,255,0.05);
}
.site-marquee {
    background: #064E3B; /* Deep Green */
    color: #4ADE80; /* Brighter Green */
    font-weight: 800;
}
.system-marquee {
    background: #022c22; /* Darker Green for system */
    color: #fff;
    font-weight: 700;
}
.marquee-content {
     width: 100%;
     overflow: hidden;
}
.marquee-track {
    display: inline-block;
    white-space: nowrap;
    animation: marquee-roll 60s linear infinite;
    will-change: transform;
}
.marquee-item {
    display: inline-flex;
    align-items: center;
    margin-right: 100px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.15em;
}
.marquee-track:hover {
    animation-play-state: paused;
}
@keyframes marquee-roll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); } 
}
</style>
