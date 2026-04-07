<?php 
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $type = $_POST['reg_type'] ?? 'org'; // 'org' or 'member'
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Organization specific
    $biz_name = $_POST['biz_name'] ?? '';
    $owner_name = $_POST['owner_name'] ?? '';
    
    // Member specific
    $full_name = $_POST['full_name'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Essential account fields are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // Check if user/email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                throw new Exception("Username or Email already exists.");
            }

            // 1. Create Organization entry (even for members, to keep it unified for admin review)
            $org_display_name = ($type === 'org') ? $biz_name : $full_name;
            $org_owner = ($type === 'org') ? $owner_name : $full_name;
            
            $stmt = $pdo->prepare("INSERT INTO organizations (name, owner_name, email, phone, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$org_display_name, $org_owner, $email, $phone]);
            $org_id = $pdo->lastInsertId();

            // 2. Create User entry
            $role = ($type === 'org') ? 'org_admin' : 'member';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (organization_id, username, password, email, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$org_id, $username, $hashed_password, $email]);
            $user_id = $pdo->lastInsertId();

            // 3. Handle File Uploads
            $upload_dir = 'uploads/docs/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $docs_to_upload = [];
            if ($type === 'org') {
                $docs_to_upload = [
                    'reg_cert' => 'Registration Certificate',
                    'tin_rssb' => 'TIN & RSSB',
                    'statutes' => 'Statutes / Profile',
                    'member_list' => 'Member List',
                    'app_letter' => 'Application Letter'
                ];
            } else {
                $docs_to_upload = [
                    'id_passport' => 'ID / Passport',
                    'photo' => 'Photo',
                    'enrollment' => 'Enrollment Form',
                    'medical' => 'Medical Info'
                ];
            }

            foreach ($docs_to_upload as $input_name => $label) {
                if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
                    $file_name = $_FILES[$input_name]['name'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_file_name = "doc_" . $org_id . "_" . $input_name . "_" . time() . "." . $file_ext;
                    $target_file = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_file)) {
                        $stmt = $pdo->prepare("INSERT INTO documents (organization_id, user_id, file_path, file_name, file_type, doc_label) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$org_id, $user_id, $target_file, $file_name, $file_ext, $label]);
                    }
                }
            }

            $pdo->commit();
            set_toast_message("Registration submitted! Our team will review your documents.", "success");
            header("Location: login.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <title>Join BizShield | Step-by-Step Registration</title>
    
    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons & Animation -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: { DEFAULT: '#064E3B', light: '#14532D', dark: '#022c22' },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { transform: 'translateY(20px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --primary: #064E3B;
            --primary-light: #14532D;
            --accent: #10B981;
        }

        body {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(6, 78, 59, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(6, 78, 59, 0.1);
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e2e8f0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-dot.active {
            background: var(--primary);
            transform: scale(1.5);
            box-shadow: 0 0 15px rgba(6, 78, 59, 0.3);
        }

        .step-dot.completed {
            background: var(--accent);
        }

        .input-group:focus-within label {
            color: var(--primary);
            font-weight: 700;
        }

        .reg-type-card.selected {
            border-color: var(--primary);
            background: rgba(6, 78, 59, 0.05);
            box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.1);
        }

        /* Hide scrollbars but keep functionality */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        .btn-hover-effect {
            position: relative;
            overflow: hidden;
        }

        .btn-hover-effect::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: rgba(255, 255, 255, 0.1);
            transition: transform 0.6s;
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
        }

        .btn-hover-effect:hover::after {
            transform: translate(-50%, -50%) scale(1);
        }

        .file-upload-item {
            transition: all 0.3s ease;
        }

        .file-upload-item:hover {
            border-color: var(--primary);
            background-color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800">

    <header class="sticky top-0 w-full z-[1000] bg-white/80 backdrop-blur-md border-b border-slate-100">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <main class="py-12 px-4 flex flex-col items-center justify-center">
        <!-- Progress Stepper -->
        <div class="max-w-md w-full mb-8 flex items-center justify-between px-6" id="stepper">
            <div class="flex flex-col items-center gap-2">
                <div class="step-dot active" data-step="1"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-primary">Identity</span>
            </div>
            <div class="h-[2px] flex-1 bg-slate-200 mx-4 -mt-6"></div>
            <div class="flex flex-col items-center gap-2">
                <div class="step-dot" data-step="2"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Profile</span>
            </div>
            <div class="h-[2px] flex-1 bg-slate-200 mx-4 -mt-6"></div>
            <div class="flex flex-col items-center gap-2">
                <div class="step-dot" data-step="3"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assets</span>
            </div>
            <div class="h-[2px] flex-1 bg-slate-200 mx-4 -mt-6"></div>
            <div class="flex flex-col items-center gap-2">
                <div class="step-dot" data-step="4"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Security</span>
            </div>
        </div>

        <!-- Registration Container -->
        <div class="max-w-xl w-full glass-card rounded-[3rem] overflow-hidden" data-aos="zoom-in">
            <div class="bg-primary p-12 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-4xl font-extrabold mb-2 font-outfit tracking-tight">Become a Member</h1>
                    <p class="text-green-100/80 text-sm font-medium">Join Africa's elite protection network in minutes.</p>
                </div>
                <!-- Abstract patterns -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-48 h-48 bg-accent/20 rounded-full blur-2xl"></div>
            </div>

            <?php if ($error): ?>
                <div class="mx-8 mt-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-xs font-bold flex items-center gap-3 animate-shake">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                        <i class="ph ph-warning-circle text-lg font-bold"></i>
                    </div>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" id="multistep-form" class="p-8 lg:p-12 space-y-8">
                
                <!-- Step 1: Account Type Selection -->
                <div id="step-1" class="step-content space-y-8">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-bold text-slate-900">Organization Enrolment</h2>
                        <p class="text-xs text-slate-400 font-medium">Register your company or group to start the protection process.</p>
                    </div>

                    <div class="p-8 border-2 border-primary rounded-[2rem] bg-primary/5 text-center relative group">
                        <input type="hidden" name="reg_type" value="org">
                        <div class="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center text-primary mx-auto mb-4 group-hover:rotate-6 transition-transform">
                            <i class="ph ph-buildings text-4xl font-bold"></i>
                        </div>
                        <h3 class="text-2xl font-black text-primary mb-1">Corporate Account</h3>
                        <p class="text-xs text-slate-500 font-medium">For Companies, NGOs, and Professional Groups.</p>
                        
                        <div class="absolute -top-3 -right-3 bg-primary text-white text-[10px] font-black px-4 py-2 rounded-full shadow-lg uppercase tracking-widest">Selected</div>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 mt-8">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0"><i class="ph ph-info font-bold"></i></div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 mb-1">Membership for Individuals</h4>
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed italic">Individual members cannot register directly. Please contact your organization administrator to be added to the BizShield network.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Business Info -->
                <div id="step-2" class="step-content hidden space-y-6">
                    <div class="space-y-6">
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest transition-all">Company / Group Name</label>
                            <div class="relative">
                                <i class="ph ph-briefcase absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 text-xl"></i>
                                <input type="text" name="biz_name" required placeholder="Africa Trading Ltd" class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 focus:bg-white focus:border-primary/20 outline-none transition-all text-sm font-semibold">
                            </div>
                        </div>
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest transition-all">Legal Representative</label>
                            <div class="relative">
                                <i class="ph ph-signature absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 text-xl"></i>
                                <input type="text" name="owner_name" required placeholder="Full Legal Name" class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 focus:bg-white focus:border-primary/20 outline-none transition-all text-sm font-semibold">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest">Business Email</label>
                            <input type="email" name="email" required placeholder="hello@company.rw" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 outline-none transition-all text-sm font-semibold">
                        </div>
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest">Phone Contact</label>
                            <input type="tel" name="phone" required placeholder="+250 7..." class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 outline-none transition-all text-sm font-semibold">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Document Uploads -->
                <div id="step-3" class="step-content hidden space-y-8">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-slate-900">Upload Corporate Assets</h3>
                        <p class="text-xs text-slate-400 font-medium">Please provide high-quality scans of the following:</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php 
                        $org_docs = [
                            'reg_cert' => ['icon' => 'ph-certificate', 'label' => 'Registration Certificate'],
                            'tin_rssb' => ['icon' => 'ph-file-text', 'label' => 'TIN & RSSB Papers'],
                            'statutes' => ['icon' => 'ph-book-open', 'label' => 'Statutes / Profile'],
                            'member_list' => ['icon' => 'ph-users-three', 'label' => 'Member List (XLS/PDF)'],
                            'app_letter' => ['icon' => 'ph-envelope-simple', 'label' => 'Application Letter'],
                        ];
                        foreach($org_docs as $key => $doc): 
                        ?>
                        <div class="file-upload-item relative p-4 border-2 border-dashed border-slate-100 rounded-2xl flex flex-col items-center justify-center text-center group cursor-pointer" onclick="document.getElementById('file-<?php echo $key; ?>').click()">
                            <input type="file" name="<?php echo $key; ?>" id="file-<?php echo $key; ?>" required class="hidden" onchange="previewFile('<?php echo $key; ?>')">
                            <i class="ph <?php echo $doc['icon']; ?> text-2xl text-slate-300 group-hover:text-primary transition-colors mb-2"></i>
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-tighter group-hover:text-primary"><?php echo $doc['label']; ?></span>
                            <p id="name-<?php echo $key; ?>" class="text-[9px] text-emerald-600 font-bold mt-1 truncate w-full px-2"></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 4: Security -->
                <div id="step-4" class="step-content hidden space-y-6">
                    <div class="text-center mb-8">
                        <h3 class="text-lg font-bold text-slate-900">Secure Your Account</h3>
                        <p class="text-xs text-slate-400 font-medium">Choose a strong username and password.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest">Portal Username</label>
                            <input type="text" name="username" placeholder="j.doe" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 outline-none transition-all text-sm font-semibold">
                        </div>
                        <div class="space-y-2 input-group">
                            <label class="text-[10px] text-slate-400 font-black ml-1 uppercase tracking-widest">Access Password</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/5 outline-none transition-all text-sm font-semibold">
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 mt-8">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0"><i class="ph ph-info font-bold"></i></div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 mb-1">Final Review</h4>
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed">By clicking 'Submit Registration', you agree to our terms. BizShield administrators will verify your documents within 24-48 hours.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center gap-4 pt-8 border-t border-slate-100">
                    <button type="button" id="prev-btn" onclick="moveStep(-1)" class="hidden flex-1 py-5 bg-slate-100 text-slate-400 font-extrabold text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all active:scale-95">
                        Back
                    </button>
                    <button type="button" id="next-btn" onclick="moveStep(1)" class="flex-2 w-full py-5 bg-primary text-white font-extrabold text-xs uppercase tracking-widest rounded-2xl hover:bg-primary-dark transition-all shadow-xl shadow-primary/20 btn-hover-effect active:scale-95">
                        Continue to Profile
                    </button>
                    <button type="submit" name="register" id="submit-btn" class="hidden flex-2 w-full py-5 bg-emerald-600 text-white font-extrabold text-xs uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-900/20 btn-hover-effect active:scale-95">
                        Complete Registration
                    </button>
                </div>
            </form>
            
            <div class="p-8 text-center border-t border-slate-50 bg-slate-50/50">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Already have an account? <a href="login.php" class="text-primary hover:underline ml-2">Secure Login</a></p>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        let currentStep = 1;
        const totalSteps = 4;
        let userType = 'org';

        function toggleRegType(type) {
            userType = type;
            
            // Toggle highlight
            document.querySelectorAll('.reg-type-card').forEach(card => card.classList.remove('selected', 'border-primary'));
            const selectedCard = document.querySelector(`input[value="${type}"]`).closest('.reg-type-card');
            selectedCard.classList.add('selected', 'border-primary');

            // Toggle specific fields
            if (type === 'org') {
                document.getElementById('org-fields').classList.remove('hidden');
                document.getElementById('member-fields').classList.add('hidden');
                document.getElementById('org-docs').classList.remove('hidden');
                document.getElementById('member-docs').classList.add('hidden');
            } else {
                document.getElementById('org-fields').classList.add('hidden');
                document.getElementById('member-fields').classList.remove('hidden');
                document.getElementById('org-docs').classList.add('hidden');
                document.getElementById('member-docs').classList.remove('hidden');
            }
        }

        function moveStep(delta) {
            const nextStep = currentStep + delta;
            if (nextStep < 1 || nextStep > totalSteps) return;

            // Simple validation before moving forward
            if (delta > 0 && !validateStep(currentStep)) return;

            // Update UI
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            document.getElementById(`step-${nextStep}`).classList.remove('hidden');
            
            // Animation reset
            document.getElementById(`step-${nextStep}`).style.animation = 'none';
            document.getElementById(`step-${nextStep}`).offsetHeight; /* trigger reflow */
            document.getElementById(`step-${nextStep}`).style.animation = null;
            document.getElementById(`step-${nextStep}`).classList.add('animate-slide-up');

            currentStep = nextStep;
            updateUI();
        }

        function validateStep(step) {
            // Optional: Add specific validation for each step
            // For now, we trust the user or the backend
            return true;
        }

        function updateUI() {
            // Update dots
            document.querySelectorAll('.step-dot').forEach(dot => {
                const step = parseInt(dot.dataset.step);
                dot.classList.remove('active', 'completed');
                if (step === currentStep) dot.classList.add('active');
                if (step < currentStep) dot.classList.add('completed');
            });

            // Update buttons
            document.getElementById('prev-btn').classList.toggle('hidden', currentStep === 1);
            
            if (currentStep === totalSteps) {
                document.getElementById('next-btn').classList.add('hidden');
                document.getElementById('submit-btn').classList.remove('hidden');
            } else {
                document.getElementById('next-btn').classList.remove('hidden');
                document.getElementById('submit-btn').classList.add('hidden');
                
                // Update next button text
                const texts = ['', 'Continue to Profile', 'Proceed to Assets', 'Final Security Checks'];
                document.getElementById('next-btn').textContent = texts[currentStep];
            }

            // Scroll to top of form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function previewFile(id) {
            const input = document.getElementById(`file-${id}`);
            const nameEl = document.getElementById(`name-${id}`);
            if (input.files && input.files[0]) {
                nameEl.textContent = "Selected: " + input.files[0].name;
                input.closest('.file-upload-item').classList.add('border-primary', 'bg-emerald-50/20');
            }
        }
    </script>
</body>
</html>
