<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact | Enterprise Tracker Pro</title>

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    {{-- <link rel="stylesheet" href="welcome/css/bootstrap.min.css"> --}}
    <link rel="stylesheet" href="welcome/css/aos.css">
    <link rel="stylesheet" href="welcome/css/owl.carousel.min.css">
    <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css">
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css">

    <style>
        input:focus,
        textarea:focus {
            outline: 2px solid theme('colors.primary');
            outline-offset: 2px;
            border-color: transparent;
        }
    </style>
</head>

<body class="antialiased bg-card text-foreground">

    {{-- Header --}}
    <x-Home_page.header />

    {{-- Main Contact Section --}}
    <main class="min-h-screen pt-24 sm:pt-28 lg:pt-32 pb-12 sm:pb-16 lg:pb-20 bg-background">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
            <div class="max-w-xl mx-auto bg-card p-6 sm:p-8 lg:p-10 rounded-2xl shadow-xl">

                <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-3 text-center">Contact Us</h1>
                <p class="text-sm text-muted mb-8 text-center">
                    Have a question or need assistance? We'd love to hear from you. Fill out the form below and we'll get
                    back to you as soon as possible.
                </p>

                {{-- Contact Form --}}
                <form onsubmit="submitContactForm(event)" class="space-y-5">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-xs font-medium text-muted mb-2">Name</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                                placeholder="Your name">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-medium text-muted mb-2">Email</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                                placeholder="your@email.com">
                        </div>
                    </div>
                    <div>
                        <label for="subject" class="block text-xs font-medium text-muted mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                            placeholder="How can we help?">
                    </div>

                    <div>
                        <label for="message" class="block text-xs font-medium text-muted mb-2">Message</label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground resize-none bg-card"
                            placeholder="Tell us more about your inquiry..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full text-primary-foreground text-sm font-medium py-3 rounded-md bg-primary hover:bg-primary/90 transition-colors">
                        Send Message
                    </button>
                </form>

                {{-- Contact Info --}}
                <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
                    {{-- Email --}}
                    <div>
                        <div class="mb-4">
                            <!-- Icon box uses bg-secondary and text-primary -->
                            <div class="w-14 h-14 bg-secondary text-primary flex items-center justify-center rounded-lg mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6M20 13H4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-foreground mb-1">Email</h3>
                        <p class="text-xs text-muted">support@enterprisetrackerpro.com</p>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <div class="mb-4">
                            <div class="w-14 h-14 bg-secondary text-primary flex items-center justify-center rounded-lg mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <!-- Simplified phone icon -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.108l-4.423-1.106c-.44-.11-.902.055-1.173.417l-1.096 1.096c-1.026-1.913-2.333-3.22-3.22-3.22l1.096-1.096c.362-.362.527-.833.417-1.173l-1.106-4.423c-.142-.501-.602-.852-1.108-.852H3a.75.75 0 00-.75.75v.038z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-foreground mb-1">Phone</h3>
                        <p class="text-xs text-muted">+1 (555) 123-4567</p>
                    </div>

                    {{-- Address --}}
                    <div>
                        <div class="mb-4">
                            <div class="w-14 h-14 bg-secondary text-primary flex items-center justify-center rounded-lg mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-foreground mb-1">Address</h3>
                        <p class="text-xs text-muted">Paris, France</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <x-Home_page.footer />

    {{-- JS --}}
    <script>
        // Using a less-intrusive message box instead of alert()
        function submitContactForm(e) {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');
            const originalButtonText = button.innerHTML;

            // Show loading state
            button.innerHTML = "Sending...";
            button.disabled = true;

            // Simulate network delay
            setTimeout(() => {
                // Create a success message
                let successMessage = form.querySelector('.success-message');
                if (!successMessage) {
                    successMessage = document.createElement('p');
                    successMessage.className = 'text-sm text-center text-green-600 success-message';
                    form.parentNode.insertBefore(successMessage, form);
                }
                successMessage.textContent = "Thank you for your message! We'll get back to you soon.";
                
                // Reset form
                form.reset();
                button.innerHTML = originalButtonText;
                button.disabled = false;

                // Hide message after a few seconds
                setTimeout(() => {
                    successMessage.textContent = "";
                }, 3000);

            }, 1000);
        }
    </script>

</body>
</html>