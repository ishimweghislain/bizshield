<!DOCTYPE html>
<html lang="en" class="text-[85%]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join BizShield | Get Protected</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23064E3B'><path d='M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5zm0 10.5h7c-.51 4.12-3.1 7.82-7 9.09V12.5H5v-4.47l7-3.89v4.36z'/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
</head>
<body class="font-sans text-gray-900 bg-gray-50/50">

    <header class="sticky top-0 w-full z-[1000] bg-white">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <?php include 'components/mobile-nav.php'; ?>

    <main class="relative pb-24 px-4" data-aos="zoom-in">
        <div class="max-w-2xl mx-auto bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden mt-12">
            <div class="bg-primary p-10 text-white text-center">
                <h1 class="text-3xl font-bold mb-3 font-serif">Secure Your Spot</h1>
                <p class="text-green-100 text-xs font-bold uppercase tracking-widest italic">Only open March - May</p>
            </div>
            
            <form action="#" method="POST" class="p-10 space-y-6 text-sm font-medium">
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Business Name</label>
                        <input type="text" placeholder="Your Company Name" class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Owner Name</label>
                        <input type="text" placeholder="Full Legal Name" class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Email</label>
                            <input type="email" placeholder="email@company.rw" class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Phone</label>
                            <input type="tel" placeholder="+250 7XX XXX XXX" class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs text-primary font-bold ml-1 uppercase tracking-wider">Business Papers</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center hover:border-primary transition-colors cursor-pointer bg-gray-50">
                            <p class="text-xs text-gray-500 italic uppercase">Upload RDB & TIN Certificates here</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-primary text-white py-5 rounded-2xl font-bold text-lg hover:bg-primary-light transition-all shadow-xl shadow-green-900/10 active:scale-[0.98]">
                        Join the Protection Group
                    </button>
                    <p class="text-[10px] text-center text-gray-400 mt-6 uppercase font-bold px-8">Fullstack Ltd will check your papers and call you within 24 hours.</p>
                </div>
            </form>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>
</body>
</html>
