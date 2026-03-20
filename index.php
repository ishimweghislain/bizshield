<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en" class="text-[85%]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizShield | Group Insurance for Everyone</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
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
    <style>
        .hero-pattern {
            background-image: linear-gradient(to bottom, rgba(6, 78, 59, 0.8), rgba(6, 78, 59, 0.95)), url('images/insurance.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="font-sans text-gray-900 bg-white selection:bg-primary selection:text-white">

    <header class="sticky top-0 w-full z-[1000] bg-white">
        <?php include 'components/marquee.php'; ?>
        <?php include 'components/navbar.php'; ?>
    </header>

    <?php include 'components/mobile-nav.php'; ?>

    <main class="relative">
        <section class="hero-pattern min-h-screen flex items-center justify-center px-4 relative">
            <div class="max-w-4xl mx-auto text-center relative z-10" data-aos="fade-up" data-aos-duration="1000">
                <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                    Safe Insurance for <br><span class="text-green-300">Small</span> Businesses
                </h1>
                <p class="text-xl text-green-50 mb-10 max-w-2xl mx-auto">
                    We help companies join together to buy insurance. When more businesses join, the price goes down for everyone.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="register.php" class="w-full sm:w-auto bg-white text-primary px-8 py-4 rounded-full font-bold text-lg hover:bg-green-50 transition-all shadow-xl">Join BizShield</a>
                    <a href="about.php" class="w-full sm:w-auto bg-transparent border-2 border-white/50 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/10 transition-all">Learn More</a>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white" data-aos="fade-up">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h2 class="text-xs font-bold text-primary uppercase tracking-[0.3em] mb-4">Our Features</h2>
                <h3 class="text-3xl font-bold text-gray-900 mb-16">Why Businesses Love Us</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-10 bg-gray-50 rounded-[2.5rem] shadow-soft border border-gray-100" data-aos="fade-up" data-aos-delay="100">
                        <div class="text-4xl mb-6 text-primary">💰</div>
                        <h4 class="text-xl font-bold mb-4">Lower Prices</h4>
                        <p class="text-gray-600 text-sm italic">"The more people join, the less you pay."</p>
                    </div>
                    <div class="p-10 bg-gray-50 rounded-[2.5rem] shadow-soft border border-gray-100" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-4xl mb-6 text-primary">📱</div>
                        <h4 class="text-xl font-bold mb-4">100% Digital</h4>
                        <p class="text-gray-600 text-sm italic">"No paperwork. Everything happens online."</p>
                    </div>
                    <div class="p-10 bg-primary text-white rounded-[2.5rem] shadow-2xl" data-aos="fade-up" data-aos-delay="300">
                        <div class="text-4xl mb-6">🤝</div>
                        <h4 class="text-xl font-bold mb-4">Group Power</h4>
                        <p class="text-green-50 text-sm italic">"We fight for better deals as one big group."</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>
</body>
</html>
