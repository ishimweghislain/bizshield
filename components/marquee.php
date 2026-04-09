<?php
require_once dirname(__DIR__) . '/config.php';

// Detect Context
$is_admin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$is_org = (strpos($_SERVER['PHP_SELF'], '/organization/') !== false);
$is_member = (strpos($_SERVER['PHP_SELF'], '/member/') !== false);
$is_in_dashboard = ($is_admin || $is_org || $is_member);

// Fetch Global Settings (Used mostly for public site)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Build Marquee Items
$marquee_items = [];

if ($is_in_dashboard) {
    // DASHBOARD MARQUEE: Show specific notifications
    $notifications = get_active_notifications($_SESSION['org_id'] ?? null);
    
    // For members, we might want to filter or show their specific ones too
    // get_active_notifications probably handles org-wide ones.
    
    foreach ($notifications as $n) {
        if (!empty($n['message'])) {
            $marquee_items[] = "💡 " . $n['message'];
        }
    }

    // fallback for dashboard if no notifications
    if (empty($marquee_items)) {
        if ($is_admin) $marquee_items[] = "🛠️ Global Administration Mode: Monitoring system performance and security.";
        else if ($is_org) $marquee_items[] = "🏢 Organization Portal: Ensure all team members have completed their compliance profiles.";
        else if ($is_member) $marquee_items[] = "👤 Member Dashboard: Keep your documentation updated to maintain active protection.";
    }
} else {
    // PUBLIC WEBSITE MARQUEE: Only show global announcements
    if (!empty($settings['insurance_price'])) {
        $price = $settings['insurance_price'];
        $formatted_price = is_numeric($price) ? number_format((float)$price) : $price;
        $marquee_items[] = "📢 Premium Coverage Registration Fee: RWF " . $formatted_price;
    }
    if (!empty($settings['portal_deadline'])) {
        $marquee_items[] = "🕒 Registration Deadline: " . date('M d, Y', strtotime($settings['portal_deadline']));
    }

    // Fallback for public site
    if (empty($marquee_items)) {
        $marquee_items[] = "✅ Welcome to BizShield | Secure Group Insurance for Small Businesses";
        $marquee_items[] = "🚀 Join now to enjoy premium protection with lower group rates.";
        $marquee_items[] = "🔒 100% Digital & Paperless Registration Process.";
    }
}
?>

<div class="marquee-bar <?php echo $is_in_dashboard ? 'system-marquee' : 'site-marquee'; ?>">
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
     height: 38px;
     display: flex;
     align-items: center;
     width: 100%;
     overflow: hidden;
     position: relative;
     z-index: 5000;
}

.site-marquee {
    background: linear-gradient(90deg, #064E3B, #065F46);
    color: #4ADE80;
    font-weight: 800;
    border-bottom: 2px solid #065F46;
}

.system-marquee {
    background: linear-gradient(90deg, #022c22, #064E3B);
    color: #fff;
    font-weight: 700;
    border-bottom: 2px solid #10B981;
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
    margin-right: 120px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.2em;
}

.system-marquee .marquee-item {
    letter-spacing: 0.1em;
    font-family: 'Inter', sans-serif;
    text-transform: none;
    font-size: 12px;
}

.marquee-track:hover {
    animation-play-state: paused;
}

@keyframes marquee-roll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); } 
}
</style>
