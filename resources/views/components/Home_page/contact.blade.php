<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact | Enterprise Tracker Pro</title>

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="welcome/css/bootstrap.min.css">
    <link rel="stylesheet" href="welcome/css/aos.css">
    <link rel="stylesheet" href="welcome/css/owl.carousel.min.css">
    <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css">

    <style>
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #d1d5db;
        }

        .contact-btn {
            background-color: #0ea5e9;
            transition: background-color 0.3s ease;
        }

        .contact-btn:hover {
            background-color: #0284c7;
        }

        .contact-icon-box {
            background-color: #e0f2fe;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0 auto;
        }

        .contact-icon-box svg {
            color: #0ea5e9;
            width: 24px;
            height: 24px;
        }
    </style>
</head>

<body class="antialiased bg-white text-gray-800">

    {{-- Header --}}
    <x-Home_page.header />

    {{-- Main Contact Section --}}
    <main class="min-h-screen pt-24 sm:pt-28 lg:pt-32 pb-12 sm:pb-16 lg:pb-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
            <div class="max-w-xl mx-auto bg-white p-6 sm:p-8 lg:p-10 rounded-2xl shadow-xl">

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Contact Us</h1>
                <p class="text-sm text-gray-600 mb-8 text-center">
                    Have a question or need assistance? We'd love to hear from you. Fill out the form below and we'll get back
                    to you as soon as possible.
                </p>

                {{-- Contact Form --}}
                <form onsubmit="submitContactForm(event)" class="space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md placeholder-gray-400 bg-white"
                            placeholder="Your name">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md placeholder-gray-400 bg-white"
                            placeholder="your@email.com">
                    </div>

                    <div>
                        <label for="subject" class="block text-xs font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md placeholder-gray-400 bg-white"
                            placeholder="How can we help?">
                    </div>

                    <div>
                        <label for="message" class="block text-xs font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md placeholder-gray-400 resize-none bg-white"
                            placeholder="Tell us more about your inquiry..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full text-white text-sm font-medium py-3 rounded-md contact-btn">
                        Send Message
                    </button>
                </form>

                {{-- Contact Info --}}
                <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
                    {{-- Email --}}
                    <div>
                        <div class="mb-4">
                            <div class="contact-icon-box">
                                <!-- Heroicons Inbox -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6M20 13H4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Email</h3>
                        <p class="text-xs text-gray-600">support@enterprisetrackerpro.com</p>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <div class="mb-4">
                            <div class="contact-icon-box">
                                <!-- Heroicons Phone -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V9a1 1 0 01-1 1H5a2 2 0 01-2-2V5zM17 13l2 2-2 2-2-2 2-2z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Phone</h3>
                        <p class="text-xs text-gray-600">+1 (555) 123-4567</p>
                    </div>

                    {{-- Address --}}
                    <div>
                        <div class="mb-4">
                            <div class="contact-icon-box">
                                <!-- Heroicons Location Marker -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 11c1.38 0 2.5-1.12 2.5-2.5S13.38 6 12 6 9.5 7.12 9.5 8.5 10.62 11 12 11z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Address</h3>
                        <p class="text-xs text-gray-600">Paris, France</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <x-Home_page.footer />

    {{-- JS --}}
<script>
        function submitContactForm(e) {
            e.preventDefault();
            alert("Thank you for your message! We'll get back to you soon.");
            e.target.reset();
        }
</script>

</body>
</html>
