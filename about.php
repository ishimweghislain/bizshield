<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en" class="text-[85%]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About BizShield | Our Story</title>
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
<body class="font-sans text-gray-900 bg-white">

    <header class="sticky top-0 w-full z-[1000] bg-white">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <?php include 'components/mobile-nav.php'; ?>

    <main class="relative">
        <header class="py-16 md:py-24 px-4 text-center bg-gray-50" data-aos="fade-down">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">Our Mission</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">We want to help small companies in Rwanda get the same insurance deals as big corporations.</p>
        </header>

        <section class="py-24 px-4 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-sm font-bold text-primary uppercase tracking-widest mb-4 italic underline underline-offset-8">Important Rules</h2>
                    <h3 class="text-3xl font-bold mb-8">Registration Window</h3>
                    <div class="space-y-6">
                        <div class="p-8 bg-green-50 rounded-[2.5rem] border border-green-100 shadow-sm" data-aos="zoom-in">
                            <h4 class="text-xl font-bold text-primary mb-3">📅 March to May</h4>
                            <p class="text-gray-700 leading-relaxed text-sm font-medium">Registration is only open for 3 months. Once May ends, you must wait for the next year to join us.</p>
                        </div>
                        <div class="p-8 bg-gray-50 rounded-[2.5rem] border border-gray-100 shadow-sm" data-aos="zoom-in" data-aos-delay="100">
                            <h4 class="text-xl font-bold text-gray-900 mb-3">⏱️ 1 Year Cover</h4>
                            <p class="text-gray-600 leading-relaxed text-sm font-medium">Your insurance lasts for exactly one year. We handle renewals automatically if you stay in the group.</p>
                        </div>
                    </div>
                </div>
                <div class="relative rounded-[3rem] overflow-hidden shadow-2xl" data-aos="fade-left">
                    <img src="images/insurance.jpg" alt="Insurance Life" class="w-full h-[400px] object-cover">
                    <div class="absolute inset-0 bg-primary/20"></div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>
</body>
</html>
