<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$org_id = $_SESSION['org_id'];

// Define required documents for a member
$required_docs = [
    'id_passport' => 'ID / Passport',
    'photo' => 'Passport Photo',
    'enrollment' => 'Enrollment Form',
    'medical' => 'Medical Info'
];

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['doc_file'])) {
    $label = $_POST['doc_label'];
    $input_name = $_POST['doc_type']; // e.g., 'id_passport'
    
    if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] == 0) {
        $upload_dir = '../uploads/docs/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = $_FILES['doc_file']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "mem_" . $user_id . "_" . $input_name . "_" . time() . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $target_file)) {
            // Check if a document with this label already exists for this user
            $stmt = $pdo->prepare("SELECT id FROM documents WHERE user_id = ? AND doc_label = ?");
            $stmt->execute([$user_id, $label]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing document (Reset status to pending)
                $stmt = $pdo->prepare("UPDATE documents SET file_path = ?, file_name = ?, file_type = ?, status = 'pending', rejection_reason = NULL, created_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute(['uploads/docs/' . $new_file_name, $file_name, $file_ext, $existing['id']]);
            } else {
                // Insert new document
                $stmt = $pdo->prepare("INSERT INTO documents (organization_id, user_id, file_path, file_name, file_type, doc_label, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$org_id, $user_id, 'uploads/docs/' . $new_file_name, $file_name, $file_ext, $label]);
            }
            
            set_toast_message("Document '$label' submitted for review.", "success");
        } else {
            set_toast_message("Failed to upload document.", "warning");
        }
    }
    header("Location: dashboard.php");
    exit;
}

// Fetch current documents and their statuses
$stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ?");
$stmt->execute([$user_id]);
$uploaded_docs = [];
while ($row = $stmt->fetch()) {
    $uploaded_docs[$row['doc_label']] = $row;
}

$toast = get_toast_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Member Compliance | BizShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: { DEFAULT: '#064E3B', light: '#14532D', dark: '#022c22' } },
                }
            }
        }
    </script>
    <style>
        body { background: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .doc-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .doc-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="font-sans text-slate-900">
    <?php include '../components/marquee.php'; ?>

    <header class="sticky top-0 w-full z-[1000] bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <i class="ph ph-shield-check text-2xl"></i>
            </div>
            <div>
                <span class="text-xl font-bold text-primary block leading-none">BizShield</span>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Member Portal</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo htmlspecialchars($_SESSION['org_name']); ?></p>
            </div>
            <a href="../logout.php" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all">
                <i class="ph ph-sign-out text-xl"></i>
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto py-12 px-6">
        <?php if ($toast): ?>
            <div id="toast" class="fixed top-24 right-6 z-[2000] bg-white border border-slate-100 rounded-2xl shadow-2xl p-6 border-l-4 <?php echo $toast['type'] == 'success' ? 'border-l-emerald-500' : 'border-l-orange-500'; ?> flex items-center gap-4 animate-bounce-in">
                <div class="w-10 h-10 rounded-full <?php echo $toast['type'] == 'success' ? 'bg-emerald-50 text-emerald-500' : 'bg-orange-50 text-orange-500'; ?> flex items-center justify-center"><i class="ph <?php echo $toast['type'] == 'success' ? 'ph-check-circle' : 'ph-warning-circle'; ?> text-2xl font-bold"></i></div>
                <div><p class="text-[10px] text-slate-400 font-black uppercase tracking-widest"><?php echo ucfirst($toast['type']); ?></p><p class="text-sm font-bold text-slate-700"><?php echo $toast['message']; ?></p></div>
            </div>
            <script>setTimeout(() => { document.getElementById('toast')?.remove(); }, 4000);</script>
        <?php endif; ?>

        <div class="mb-12" data-aos="fade-down">
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">My Compliance Documents</h1>
            <p class="text-slate-500 font-medium">Please ensure all required documents are uploaded to maintain your active protection status.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <?php 
            $icons = [
                'id_passport' => 'ph-identification-card',
                'photo' => 'ph-camera',
                'enrollment' => 'ph-file-check',
                'medical' => 'ph-heartbeat'
            ];
            foreach ($required_docs as $key => $label): 
                $doc = $uploaded_docs[$label] ?? null;
                $status = $doc['status'] ?? 'pending_upload';
            ?>
            <div class="doc-card glass-card rounded-[2.5rem] p-8 relative overflow-hidden group shadow-sm hover:shadow-xl transition-all" data-aos="fade-up">
                <div class="flex items-start justify-between mb-8">
                    <div class="w-16 h-16 bg-slate-50 rounded-[1.5rem] flex items-center justify-center text-slate-300 group-hover:bg-primary/5 group-hover:text-primary transition-all duration-500">
                        <i class="ph <?php echo $icons[$key]; ?> text-3xl font-bold"></i>
                    </div>
                    <div>
                        <?php 
                        $status_ui = [
                            'pending_upload' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-400', 'label' => 'Missing'],
                            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Under Review'],
                            'verified' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'label' => 'Awaiting Global Approval'],
                            'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Finalized & Policy Active'],
                            'rejected' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'label' => 'Rejected']
                        ];
                        $ui = $status_ui[$status] ?? $status_ui['pending'];
                        ?>
                        <span class="<?php echo $ui['bg']; ?> <?php echo $ui['text']; ?> text-[10px] font-black uppercase tracking-[0.2em] px-4 py-2 rounded-full border border-white shadow-sm">
                            <?php echo $ui['label']; ?>
                        </span>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-1"><?php echo $label; ?></h3>
                    <p class="text-xs text-slate-400 font-medium">Required for personal health insurance coverage.</p>
                </div>

                <?php if ($status === 'rejected'): ?>
                    <div class="mb-6 p-4 bg-red-50/50 border border-red-100 rounded-2xl">
                        <p class="text-[10px] text-red-400 font-black uppercase tracking-widest mb-1 flex items-center gap-2">
                            <i class="ph ph-warning-circle font-bold"></i> Rejection Reason
                        </p>
                        <p class="text-xs font-bold text-red-700 italic">"<?php echo htmlspecialchars($doc['rejection_reason']); ?>"</p>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-3">
                    <?php if ($status === 'pending_upload' || $status === 'rejected'): ?>
                    <form action="" method="POST" enctype="multipart/form-data" class="w-full">
                        <input type="hidden" name="doc_label" value="<?php echo $label; ?>">
                        <input type="hidden" name="doc_type" value="<?php echo $key; ?>">
                        <label class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 cursor-pointer hover:bg-primary-dark transition-all shadow-lg shadow-primary/10">
                            <i class="ph ph-cloud-arrow-up text-lg"></i>
                            <?php echo $status === 'rejected' ? 'Re-upload Document' : 'Upload Document'; ?>
                            <input type="file" name="doc_file" class="hidden" required onchange="this.form.submit()">
                        </label>
                    </form>
                    <?php elseif ($status === 'pending'): ?>
                    <div class="w-full bg-slate-100 text-slate-400 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 italic">
                        <i class="ph ph-hourglass-medium text-lg"></i>
                        Awaiting Review...
                    </div>
                    <?php elseif ($status === 'verified'): ?>
                    <div class="w-full bg-blue-50 text-blue-600 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="ph ph-shield-check text-lg font-bold"></i>
                        Verified by Org
                    </div>
                    <?php elseif ($status === 'approved'): ?>
                    <div class="w-full bg-emerald-50 text-emerald-600 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="ph ph-check-circle text-lg font-bold"></i>
                        Verified & Secure
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Subtle background decoration -->
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-1000"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-16 p-10 bg-slate-900 rounded-[3rem] text-white relative overflow-hidden group shadow-2xl" data-aos="zoom-in">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h2 class="text-2xl font-extrabold mb-2 tracking-tight">Need assistance?</h2>
                    <p class="text-slate-400 text-sm font-medium">If you're having trouble with your documents, your organization admin can help.</p>
                </div>
                <div class="px-8 py-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 flex items-center gap-4">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white"><i class="ph ph-buildings"></i></div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Admin Contact</p>
                        <p class="text-sm font-bold truncate w-40"><?php echo htmlspecialchars($_SESSION['org_name']); ?></p>
                    </div>
                </div>
            </div>
            <!-- Animated background elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/20 blur-[100px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-500/10 blur-[80px] rounded-full"></div>
        </div>
    </main>

    <footer class="py-12 text-center text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em]">
        &copy; <?php echo date('Y'); ?> BizShield Protection Network. All Rights Reserved.
    </footer>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
