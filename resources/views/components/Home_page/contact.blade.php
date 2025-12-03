<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact | DailyTrac</title>

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