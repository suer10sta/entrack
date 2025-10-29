<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About | Enterprise Tracker Pro</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
      href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap"
      rel="stylesheet"
    />
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/aos.css" />
    <script
      src="https://kit.fontawesome.com/a076d05399.js"
      crossorigin="anonymous"
    ></script>
  </head>

  <body class="antialiased bg-gray-50 text-gray-800">
    <x-Home_page.header />

    <main class="pt-32 sm:pt-36 lg:pt-40">
      <!-- Hero -->
      <section
        class="relative bg-gradient-to-r from-primary to-#0EA5E9 text-white py-24"
        data-aos="fade-down"
      >
        <div
          class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/asfalt-light.png')] opacity-20"
        ></div>
        <div class="relative z-10 container mx-auto px-6 text-center">
          <h1 class="text-5xl font-bold mb-4">About Enterprise Tracker Pro</h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Simplify operations, empower teams, and scale smarter with an
            all-in-one management platform.
          </p>
        </div>
      </section>

      <!-- Mission -->
      <section class="py-20 bg-white" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-4xl text-center">
          <h2 class="text-3xl font-semibold mb-6 text-gray-900">
            Our Mission
          </h2>
          <p class="text-gray-700 text-lg leading-relaxed">
            We provide every business — big or small — with powerful, intuitive
            tools to manage daily tasks, projects, and finances, allowing
            leaders to focus on innovation and growth.
          </p>
        </div>
      </section>

      <!-- Offerings -->
      <section
        class="py-20 bg-gradient-to-br from-blue-50 via-white to-indigo-50"
        data-aos="fade-up"
      >
        <div class="container mx-auto px-6 max-w-6xl">
          <h2
            class="text-3xl font-semibold text-center text-gray-900 mb-12"
          >
            What We Offer
          </h2>
          <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 text-center"
          >
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-file-invoice text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Invoice Management</h3>
              <p>Automate billing, track payments, and stay organized.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-diagram-project text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Project Tracking</h3>
              <p>Keep visibility on tasks, milestones, and deadlines.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-users text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Team Management</h3>
              <p>Monitor performance and allocate resources efficiently.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-chart-line text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Analytics</h3>
              <p>Access insightful reports to guide decisions.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-handshake text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Client Relations</h3>
              <p>Manage communication and build long-term trust.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
              <i class="fa-solid fa-wallet text-4xl text-blue-600 mb-4"></i>
              <h3 class="text-xl font-semibold mb-2">Expense Tracking</h3>
              <p>Stay on budget and control spending effortlessly.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Values -->
      <section class="py-20 bg-white" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-6xl">
          <h2
            class="text-3xl font-semibold text-center text-gray-900 mb-12"
          >
            Our Core Values
          </h2>
          <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-center"
          >
            <div class="bg-blue-50 p-8 rounded-xl shadow-sm">
              <h3 class="text-xl font-bold mb-3">Simplicity</h3>
              <p>Designed for clarity and ease of use.</p>
            </div>
            <div class="bg-green-50 p-8 rounded-xl shadow-sm">
              <h3 class="text-xl font-bold mb-3">Reliability</h3>
              <p>Secure, stable, and always available.</p>
            </div>
            <div class="bg-yellow-50 p-8 rounded-xl shadow-sm">
              <h3 class="text-xl font-bold mb-3">Innovation</h3>
              <p>Constantly improving to stay ahead.</p>
            </div>
            <div class="bg-pink-50 p-8 rounded-xl shadow-sm">
              <h3 class="text-xl font-bold mb-3">Support</h3>
              <p>Responsive help whenever you need it.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section
        class="text-center py-28 bg-gradient-to-r from-primary to-#0EA5E9 text-white relative overflow-hidden"
        data-aos="zoom-in"
      >
        <div class="absolute inset-0 bg-blue-900/10 backdrop-blur-sm"></div>
        <div class="relative container mx-auto px-6">
          <h2 class="text-4xl font-bold mb-6">
            Ready to Streamline Your Business?
          </h2>
          <p
            class="text-lg text-blue-100 mb-10 max-w-2xl mx-auto"
          >
            Join hundreds of businesses that trust Enterprise Tracker Pro to
            simplify operations and boost efficiency.
          </p>
          <a
            href="/contact"
            class="inline-block px-10 py-4 bg-white text-blue-700 font-semibold rounded-full shadow-lg hover:bg-blue-50 hover:shadow-xl transition-all duration-300"
            >Contact Us</a
          >
        </div>
      </section>

      <!-- breathing room above footer -->
      <div class="mt-16 sm:mt-24"></div>
    </main>

    <x-Home_page.footer />

    <script src="welcome/js/aos.js"></script>
    <script>
      AOS.init({ duration: 1000, once: true });
    </script>
  </body>
</html>
