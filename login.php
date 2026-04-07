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
            } else if ($user['role'] == 'org_admin') {
                header("Location: organization/dashboard.php");
            } else {
                header("Location: organization/documents.php");
            }
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <title>Login | BizShield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Playfair+Display:wght@400;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <style>
        :root {
            --primary: #064E3B;
            --primary-light: #14532D;
            --primary-glow: #10B981;
            --gold: #F59E0B;
            --gold-light: #FCD34D;
            --dark-bg: #0a0f0d;
            --panel-bg: #0d1a14;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        header.site-header {
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: #fff;
        }

        /* === FULL SCREEN SCENE === */
        .scene {
            display: flex;
            width: 100vw;
            height: calc(100vh - var(--header-h, 80px));
            position: relative;
            background: var(--dark-bg);
        }

        /* =========== LEFT PANEL — LAMP SIDE =========== */
        .lamp-panel {
            width: 50%;
            height: 100%;
            background: linear-gradient(160deg, #0a1a12 0%, #061009 60%, #030805 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Subtle radial background glow */
        .lamp-panel::before {
            content: '';
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        /* The rope / wire from top */
        .lamp-wire {
            width: 2px;
            background: linear-gradient(to bottom, #2a3d32, #4a6356, #6b7c6f);
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            transform-origin: top center;
            animation: wireSwing 5s ease-in-out infinite;
            z-index: 5;
        }

        /* Wire length set by JS */

        /* The lamp housing */
        .lamp-housing-wrap {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            transform-origin: top center;
            animation: lampSwing 5s ease-in-out infinite;
            z-index: 10;
            cursor: grab;
            user-select: none;
        }

        .lamp-housing {
            width: 100px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Lamp cap (top) */
        .lamp-cap {
            width: 30px;
            height: 10px;
            background: linear-gradient(to bottom, #5a6e63, #3a4e43);
            border-radius: 3px 3px 0 0;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.5);
        }

        /* Lamp shade */
        .lamp-shade {
            width: 0;
            height: 0;
            border-left: 45px solid transparent;
            border-right: 45px solid transparent;
            border-top: 60px solid #1a2e24;
            position: relative;
            filter: drop-shadow(0 3px 8px rgba(0, 0, 0, 0.6));
        }

        .lamp-shade::before {
            content: '';
            position: absolute;
            top: -60px;
            left: -45px;
            width: 90px;
            height: 60px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.07) 0%, transparent 50%);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
        }

        /* Lamp inner rim */
        .lamp-rim {
            width: 94px;
            height: 6px;
            background: linear-gradient(to bottom, #4a6356, #2a3d32);
            border-radius: 3px;
            margin-top: -3px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        /* The bulb */
        .lamp-bulb {
            width: 28px;
            height: 28px;
            border-radius: 50% 50% 45% 45%;
            background: radial-gradient(circle at 40% 30%, #fffde7, #fcd34d, #f59e0b);
            box-shadow:
                0 0 20px 10px rgba(245, 158, 11, 0.6),
                0 0 60px 20px rgba(245, 158, 11, 0.35),
                0 0 100px 40px rgba(245, 158, 11, 0.15);
            margin-top: -2px;
            position: relative;
            z-index: 2;
            animation: bulbPulse 3s ease-in-out infinite;
        }

        @keyframes bulbPulse {

            0%,
            100% {
                box-shadow: 0 0 20px 10px rgba(245, 158, 11, 0.6), 0 0 60px 20px rgba(245, 158, 11, 0.35), 0 0 100px 40px rgba(245, 158, 11, 0.15);
            }

            50% {
                box-shadow: 0 0 28px 14px rgba(245, 158, 11, 0.75), 0 0 80px 30px rgba(245, 158, 11, 0.45), 0 0 130px 55px rgba(245, 158, 11, 0.2);
            }
        }

        /* Cone light beam */
        .lamp-beam {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 220px solid transparent;
            border-right: 220px solid transparent;
            border-top: 420px solid rgba(245, 158, 11, 0.045);
            pointer-events: none;
            z-index: 1;
        }

        /* Floating dust particles in the beam */
        .dust-particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(245, 200, 80, 0.6);
            pointer-events: none;
            animation: dustFloat linear infinite;
        }

        @keyframes dustFloat {
            0% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(-260px) translateX(var(--dx)) scale(0.3);
                opacity: 0;
            }
        }

        /* Lamp sway animations */
        @keyframes lampSwing {

            0%,
            100% {
                transform: translateX(-50%) rotate(-2.5deg);
            }

            50% {
                transform: translateX(-50%) rotate(2.5deg);
            }
        }

        @keyframes wireSwing {

            0%,
            100% {
                transform: translateX(-50%) rotate(-2.5deg);
            }

            50% {
                transform: translateX(-50%) rotate(2.5deg);
            }
        }

        /* Pull indicator */
        .pull-hint {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.35);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
            animation: hintBob 2s ease-in-out infinite;
            pointer-events: none;
            z-index: 20;
        }

        .pull-hint i {
            font-size: 22px;
            color: var(--gold-light);
            opacity: 0.7;
        }

        @keyframes hintBob {

            0%,
            100% {
                transform: translateX(-50%) translateY(0);
            }

            50% {
                transform: translateX(-50%) translateY(8px);
            }
        }

        /* BizShield brand on left */
        .brand-left {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            z-index: 20;
        }

        .brand-left img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: brightness(0) invert(1) opacity(0.9);
        }

        .brand-left span {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 1px;
        }

        .brand-left small {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--primary-glow);
            font-weight: 600;
        }

        /* Floor glow reflection */
        .floor-glow {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 80px;
            background: radial-gradient(ellipse at center bottom, rgba(245, 158, 11, 0.12) 0%, transparent 70%);
            pointer-events: none;
            transition: opacity 0.5s ease;
        }

        /* =========== RIGHT PANEL — LOGIN FORM =========== */
        .login-panel {
            width: 50%;
            height: 100%;
            background: linear-gradient(145deg, #0f1f17 0%, #0a1510 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-left: 1px solid rgba(16, 185, 129, 0.08);
        }

        /* Top curtain that slides down */
        .curtain {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to bottom, #030805, #061009, #0a1510);
            z-index: 50;
            transition: height 0.7s cubic-bezier(0.4, 0, 0.2, 1);
            /* height set via JS */
        }

        .curtain-text {
            position: absolute;
            bottom: 30px;
            left: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.25);
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .curtain-text i {
            font-size: 18px;
            color: var(--gold-light);
            opacity: 0.5;
            animation: hintBob 2s ease-in-out infinite;
        }

        /* Form container */
        .form-container {
            width: 100%;
            max-width: 420px;
            padding: 0 40px;
            position: relative;
            z-index: 10;
        }

        .form-header {
            margin-bottom: 36px;
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            font-weight: 700;
            color: #fff;
            line-height: 1.15;
        }

        .form-header h1 span {
            color: var(--primary-glow);
        }

        .form-header p {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Alert messages */
        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #6ee7b7;
        }

        /* Form fields */
        .field-group {
            margin-bottom: 20px;
        }

        .field-group label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            color: var(--primary-glow);
            margin-bottom: 10px;
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: rgba(255, 255, 255, 0.25);
            transition: color 0.3s;
        }

        .field-wrap input {
            width: 100%;
            padding: 16px 18px 16px 50px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }

        .field-wrap input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        .field-wrap input:focus {
            background: rgba(16, 185, 129, 0.06);
            border-color: rgba(16, 185, 129, 0.35);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.08);
        }

        .field-wrap input:focus+i,
        .field-wrap:focus-within i {
            color: var(--primary-glow);
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 17px;
            background: linear-gradient(135deg, #064E3B, #10B981);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.5px;
            cursor: pointer;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 8px 30px rgba(6, 78, 59, 0.5);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(16, 185, 129, 0.35);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .form-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.5px;
        }

        .form-footer a {
            color: var(--primary-glow);
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
            transition: opacity 0.2s;
        }

        .form-footer a:hover {
            opacity: 0.75;
            text-decoration: underline;
        }

        /* Decorative background elements */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .bg-circle-1 {
            width: 500px;
            height: 500px;
            top: -200px;
            right: -200px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, transparent 60%);
        }

        .bg-circle-2 {
            width: 400px;
            height: 400px;
            bottom: -150px;
            left: -150px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.03) 0%, transparent 60%);
        }

        /* Divider line between panels */
        .panel-divider {
            position: absolute;
            top: 0;
            left: 50%;
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(16, 185, 129, 0.15) 30%, rgba(16, 185, 129, 0.15) 70%, transparent);
            z-index: 100;
            pointer-events: none;
        }

        /* ===== DRAGGING STATE ===== */
        body.is-dragging {
            cursor: grabbing !important;
        }

        body.is-dragging .lamp-housing-wrap {
            cursor: grabbing;
        }

        /* Lamp lit animation variant (when pulled) */
        .lamp-lit .lamp-bulb {
            box-shadow:
                0 0 35px 18px rgba(245, 158, 11, 0.9),
                0 0 90px 35px rgba(245, 158, 11, 0.55),
                0 0 160px 60px rgba(245, 158, 11, 0.25) !important;
        }

        /* Responsive: stack on small screens */
        @media (max-width: 768px) {
            .scene {
                flex-direction: column;
                overflow: auto;
            }

            .lamp-panel,
            .login-panel {
                width: 100%;
                height: 50%;
            }

            .curtain {
                display: none;
            }

            .pull-hint {
                display: none;
            }
        }
    </style>
</head>

<body>

    <header class="sticky top-0 w-full z-[1000] bg-white site-header">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <div class="scene">
        <!-- Panel divider glow line -->
        <div class="panel-divider"></div>

        <!-- ============ LEFT: LAMP PANEL ============ -->
        <div class="lamp-panel" id="lampPanel">

            <!-- Brand -->
            <div class="brand-left">
                <img src="images/favicon.png" alt="BizShield">
                <span>BizShield</span>
                <small>Secure Business Platform</small>
            </div>

            <!-- Wire from ceiling -->
            <div class="lamp-wire" id="lampWire" style="height: 180px;"></div>

            <!-- Lamp housing (draggable) -->
            <div class="lamp-housing-wrap" id="lampHousing" style="top: 180px;">
                <div class="lamp-housing" id="lampInner">
                    <div class="lamp-cap"></div>
                    <div class="lamp-shade"></div>
                    <div class="lamp-rim"></div>
                    <div class="lamp-bulb" id="lampBulb"></div>
                    <!-- Light beam cone -->
                    <div class="lamp-beam" id="lampBeam"></div>
                </div>
            </div>

            <!-- Dust particles container -->
            <div id="dustContainer"
                style="position:absolute;bottom:0;left:0;width:100%;height:100%;pointer-events:none;overflow:hidden;z-index:3;">
            </div>

            <!-- Floor glow -->
            <div class="floor-glow" id="floorGlow"></div>

            <!-- Pull hint -->
            <div class="pull-hint" id="pullHint">
                <i class="ph ph-arrow-down"></i>
                <span>Pull the lamp</span>
            </div>
        </div>

        <!-- ============ RIGHT: LOGIN PANEL ============ -->
        <div class="login-panel" id="loginPanel">
            <div class="bg-circle bg-circle-1"></div>
            <div class="bg-circle bg-circle-2"></div>

            <!-- Curtain overlay -->
            <div class="curtain" id="curtain" style="height: 100%;">
                <div class="curtain-text" id="curtainText">
                    <i class="ph ph-arrow-up"></i>
                    <span>Pull lamp to reveal</span>
                </div>
            </div>

            <!-- Login Form -->
            <div class="form-container">
                <div class="form-header">
                    <h1>Welcome<br><span>Back.</span></h1>
                    <p>Sign in to access your BizShield dashboard</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="ph ph-warning-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($toast): ?>
                    <div class="alert alert-success">
                        <i class="ph ph-check-circle"></i>
                        <?php echo $toast['message']; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="field-group">
                        <label for="username">Username</label>
                        <div class="field-wrap">
                            <input type="text" id="username" name="username" required placeholder="Enter your username"
                                autocomplete="username">
                            <i class="ph ph-user"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="password">Password</label>
                        <div class="field-wrap">
                            <input type="password" id="password" name="password" required placeholder="••••••••"
                                autocomplete="current-password">
                            <i class="ph ph-lock"></i>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login" id="loginBtn">
                        Sign In →
                    </button>

                    <p class="form-footer">
                        Don't have an account?
                        <a href="register.php">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
            (function () {
                /* ─────────────────────────────────────────
                   CONFIG
                ───────────────────────────────────────── */
                const WIRE_MIN = 60;      // px from top when lamp is at ceiling
                const WIRE_MAX = 220;     // px – max pull distance to prevent scrolling
                const REVEAL_THRESH = 120;     // px – curtain starts opening
                const FULL_OPEN_DIST = 200;     // px – curtain fully open
                const LAMP_TOP_OFFSET = 20;      // extra px for lamp below wire bottom

                /* ─────────────────────────────────────────
                   ELEMENTS
                ───────────────────────────────────────── */
                const lampHousing = document.getElementById('lampHousing');
                const lampWire = document.getElementById('lampWire');
                const lampInner = document.getElementById('lampInner');
                const lampBulb = document.getElementById('lampBulb');
                const lampBeam = document.getElementById('lampBeam');
                const curtain = document.getElementById('curtain');
                const curtainText = document.getElementById('curtainText');
                const floorGlow = document.getElementById('floorGlow');
                const pullHint = document.getElementById('pullHint');
                const dustCont = document.getElementById('dustContainer');
                const lampPanel = document.getElementById('lampPanel');

                /* ─────────────────────────────────────────
                   STATE
                ───────────────────────────────────────── */
                let isDragging = false;
                let startY = 0;
                let currentWire = WIRE_MIN;      // current wire height
                let targetWire = WIRE_MIN;
                let animFrame = null;
                let released = false;         // after mouse-up, spring back

                /* ─────────────────────────────────────────
                   INIT POSITIONS
                ───────────────────────────────────────── */
                function applyWireLen(len) {
                    len = Math.max(WIRE_MIN, Math.min(WIRE_MAX, len));
                    lampWire.style.height = len + 'px';
                    lampHousing.style.top = (len + LAMP_TOP_OFFSET) + 'px';
                    currentWire = len;
                    updateScene(len);
                }

                function updateScene(wireLen) {
                    const pullRatio = Math.max(0, (wireLen - WIRE_MIN) / (WIRE_MAX - WIRE_MIN));

                    /* curtain */
                    const panelH = document.getElementById('loginPanel').offsetHeight;
                    if (wireLen <= REVEAL_THRESH) {
                        curtain.style.height = panelH + 'px';
                        curtainText.style.opacity = 1;
                    } else {
                        const openAmt = (wireLen - REVEAL_THRESH) / (FULL_OPEN_DIST - REVEAL_THRESH);
                        const clamped = Math.min(openAmt, 1);
                        curtain.style.height = (panelH * (1 - clamped)) + 'px';
                        curtainText.style.opacity = Math.max(0, 1 - clamped * 3);
                    }

                    /* bulb glow intensity */
                    const glowScale = 0.4 + pullRatio * 0.6;
                    lampBulb.style.filter = `brightness(${0.7 + pullRatio * 0.5})`;
                    lampBulb.style.transform = `scale(${0.9 + pullRatio * 0.15})`;

                    /* beam opacity */
                    lampBeam.style.opacity = (0.3 + pullRatio * 0.7).toString();

                    /* floor glow */
                    floorGlow.style.opacity = (0.3 + pullRatio).toString();

                    /* pull hint fades out when dragging */
                    pullHint.style.opacity = wireLen > WIRE_MIN + 30 ? '0' : '1';
                }

                applyWireLen(WIRE_MIN);

                /* ─────────────────────────────────────────
                   DRAG — MOUSE
                ───────────────────────────────────────── */
                lampHousing.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    released = false;
                    startY = e.clientY - (currentWire - WIRE_MIN);
                    document.body.classList.add('is-dragging');
                    lampPanel.style.animation = 'none'; // pause panel swing while dragging
                    e.preventDefault();
                });

                document.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    const raw = e.clientY - startY + WIRE_MIN;
                    applyWireLen(raw);
                });

                document.addEventListener('mouseup', () => {
                    if (!isDragging) return;
                    isDragging = false;
                    document.body.classList.remove('is-dragging');
                    released = true;

                    /* If pulled far enough, keep open; otherwise spring back */
                    if (currentWire < FULL_OPEN_DIST) {
                        springBack();
                    }
                    // If fully open, keep it open
                });

                /* ─────────────────────────────────────────
                   DRAG — TOUCH
                ───────────────────────────────────────── */
                lampHousing.addEventListener('touchstart', (e) => {
                    isDragging = true;
                    released = false;
                    startY = e.touches[0].clientY - (currentWire - WIRE_MIN);
                    lampPanel.style.animation = 'none';
                    e.preventDefault();
                }, { passive: false });

                document.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    const raw = e.touches[0].clientY - startY + WIRE_MIN;
                    applyWireLen(raw);
                    e.preventDefault();
                }, { passive: false });

                document.addEventListener('touchend', () => {
                    if (!isDragging) return;
                    isDragging = false;
                    released = true;
                    if (currentWire < FULL_OPEN_DIST) {
                        springBack();
                    }
                });

                /* ─────────────────────────────────────────
                   SPRING BACK ANIMATION
                ───────────────────────────────────────── */
                function springBack() {
                    if (animFrame) cancelAnimationFrame(animFrame);
                    function tick() {
                        const diff = WIRE_MIN - currentWire;
                        if (Math.abs(diff) < 0.5) {
                            applyWireLen(WIRE_MIN);
                            // restore swing
                            lampPanel.style.animation = '';
                            return;
                        }
                        applyWireLen(currentWire + diff * 0.1);
                        animFrame = requestAnimationFrame(tick);
                    }
                    tick();
                }

                /* ─────────────────────────────────────────
                   DUST PARTICLES
                ───────────────────────────────────────── */
                function spawnDust() {
                    if (currentWire < WIRE_MIN + 40) return;
                    const pullRatio = (currentWire - WIRE_MIN) / (WIRE_MAX - WIRE_MIN);
                    const count = Math.floor(pullRatio * 2);
                    for (let i = 0; i < count; i++) {
                        const p = document.createElement('div');
                        p.className = 'dust-particle';
                        const size = Math.random() * 3 + 1;
                        p.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                left: ${30 + Math.random() * 40}%;
                bottom: ${10 + Math.random() * 40}%;
                --dx: ${(Math.random() - 0.5) * 60}px;
                animation-duration: ${3 + Math.random() * 4}s;
                animation-delay: ${Math.random() * 2}s;
                opacity: 0;
            `;
                        dustCont.appendChild(p);
                        setTimeout(() => p.remove(), 8000);
                    }
                }
                setInterval(spawnDust, 600);

                /* ─────────────────────────────────────────
                   PHP ERROR: auto-open if there's an error
                ───────────────────────────────────────── */
                <?php if ($error || $toast): ?>
                        // Auto-reveal the form if there's a server message
                        (function autoReveal() {
                            let w = WIRE_MIN;
                            function tick() {
                                if (w >= FULL_OPEN_DIST) { applyWireLen(FULL_OPEN_DIST); return; }
                                w += 6;
                                applyWireLen(w);
                                requestAnimationFrame(tick);
                            }
                            setTimeout(tick, 300);
                        })();
                <?php endif; ?>

            })();
    </script>
</body>

</html>