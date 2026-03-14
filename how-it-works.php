<!DOCTYPE html>
<html lang="en" class="text-[85%]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works | Simple Insurance</title>
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
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6 font-serif">A Simple Process</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto italic">Getting insured shouldn't be hard. Here is how we do it.</p>
        </header>

        <section class="py-20 px-4">
            <div class="max-w-4xl mx-auto space-y-12">
                <div class="grid grid-cols-1 md:grid-cols-[100px_1fr] gap-8 items-center p-8 bg-white rounded-[3rem] shadow-soft border border-gray-100" data-aos="fade-left">
                    <div class="w-20 h-20 bg-primary/10 text-primary font-bold text-3xl rounded-3xl flex items-center justify-center">1</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Fill the Form</h3>
                        <p class="text-gray-600 text-sm">Send us your business name and details during the opening period (March to May).</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-[100px_1fr] gap-8 items-center p-8 bg-gray-50 rounded-[3rem] shadow-sm border border-gray-100" data-aos="fade-left" data-aos-delay="100">
                    <div class="w-20 h-20 bg-white text-primary font-bold text-3xl rounded-3xl flex items-center justify-center border-2 border-primary">2</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Business Check</h3>
                        <p class="text-gray-600 text-sm">We verify your RDB papers and TIN quickly to make sure everything is legal.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-[100px_1fr] gap-8 items-center p-8 bg-white rounded-[3rem] shadow-soft border border-gray-100" data-aos="fade-left" data-aos-delay="200">
                    <div class="w-20 h-20 bg-primary/10 text-primary font-bold text-3xl rounded-3xl flex items-center justify-center">3</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Wait for the Group</h3>
                        <p class="text-gray-600 text-sm">We put your business in a group. The more businesses we have, the cheaper the deal gets.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-[100px_1fr] gap-8 items-center p-8 bg-primary rounded-[3rem] shadow-2xl" data-aos="fade-left" data-aos-delay="300">
                    <div class="w-20 h-20 bg-white text-primary font-bold text-3xl rounded-3xl flex items-center justify-center">4</div>
                    <div class="text-white">
                        <h3 class="text-xl font-bold mb-2">Get Insured</h3>
                        <p class="text-green-50 text-sm">Your policy starts! You pay a low group price for one full year of protection.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>
</body>
</html>
