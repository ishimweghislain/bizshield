<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    exit("Document ID required.");
}

$id = $_GET['id'];
$org_id = $_SESSION['org_id'];
$role = $_SESSION['role'];

// Fetch document
if ($role === 'admin') {
    // Admin can see everything
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$id]);
} else {
    // Member can only see their org docs
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND organization_id = ?");
    $stmt->execute([$id, $org_id]);
}
$doc = $stmt->fetch();

if (!$doc) {
    exit("Document not found or access denied.");
}

$file_url = '../' . $doc['file_path'];
$file_ext = strtolower($doc['file_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title><?php echo $doc['doc_label']; ?> Review | BizShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #000; }
        .viewer-header { background: #064E3B; color: white; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; z-index: 100; position: relative; }
        .viewer-content { height: calc(100vh - 64px); width: 100%; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    <div class="viewer-header shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                <i class="ph ph-shield-check text-2xl font-bold"></i>
            </div>
            <div>
                <h1 class="text-sm font-black uppercase tracking-widest"><?php echo $doc['doc_label']; ?> Verification</h1>
                <p class="text-[10px] opacity-60 font-medium">Uploaded by ID: <?php echo $doc['user_id']; ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo $file_url; ?>" download class="bg-white/10 hover:bg-white/20 px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Download</a>
            <button onclick="window.close()" class="bg-red-500/20 text-red-100 px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-500/40 transition-all border border-red-500/30">Close Viewer</button>
        </div>
    </div>

    <div class="viewer-content">
        <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'svg'])): ?>
            <div class="w-full h-full flex items-center justify-center p-12 overflow-auto bg-[radial-gradient(circle_at_center,_#111_0%,_#000_100%)]">
                <img src="<?php echo $file_url; ?>" class="max-w-full max-h-full shadow-[0_0_100px_rgba(0,0,0,0.5)] rounded-lg">
            </div>
        <?php else: ?>
            <iframe src="<?php echo $file_url; ?>#toolbar=0&navpanes=0&scrollbar=0"></iframe>
        <?php endif; ?>
    </div>
</body>
</html>
