{{-- resources/views/contact.blade.php --}}
<x-dashboard.navbar />

<main class="min-h-screen bg-background">
  <div class="container px-6 py-20">
    <div class="max-w-2xl mx-auto">
      <h1 class="text-4xl font-bold text-foreground mb-4">Contact Us</h1>
      <p class="text-muted-foreground mb-12">
        Have a question or need assistance? We'd love to hear from you. Fill out the form below and we'll get back
        to you as soon as possible.
      </p>

      {{-- Laravel form --}}
      <form method="POST" action="{{ route('contact.submit') }}" class="space-y-6">
        @csrf

        <div>
          <label for="name" class="block text-sm font-medium text-foreground mb-2">Name</label>
          <input type="text" id="name" name="name" required
            class="w-full px-4 py-2 rounded-lg border border-border bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
            placeholder="Your name">
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-foreground mb-2">Email</label>
          <input type="email" id="email" name="email" required
            class="w-full px-4 py-2 rounded-lg border border-border bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
            placeholder="your@email.com">
        </div>

        <div>
          <label for="subject" class="block text-sm font-medium text-foreground mb-2">Subject</label>
          <input type="text" id="subject" name="subject" required
            class="w-full px-4 py-2 rounded-lg border border-border bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
            placeholder="How can we help?">
        </div>

        <div>
          <label for="message" class="block text-sm font-medium text-foreground mb-2">Message</label>
          <textarea id="message" name="message" required rows="6"
            class="w-full px-4 py-2 rounded-lg border border-border bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary resize-none"
            placeholder="Tell us more about your inquiry..."></textarea>
        </div>

        <button type="submit"
          class="w-full bg-primary text-white font-medium py-2 px-4 rounded-lg hover:bg-primary/90 transition">
          Send Message
        </button>
      </form>

      {{-- Contact Info --}}
      <div class="mt-16 grid gap-8 md:grid-cols-3">
        <div class="text-center">
          <div class="mb-4 flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
              <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <h3 class="font-semibold text-foreground mb-2">Email</h3>
          <p class="text-muted-foreground">support@enterprisetrackerpro.com</p>
        </div>

        <div class="text-center">
          <div class="mb-4 flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
              <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </div>
          </div>
          <h3 class="font-semibold text-foreground mb-2">Phone</h3>
          <p class="text-muted-foreground">+1 (555) 123-4567</p>
        </div>

        <div class="text-center">
          <div class="mb-4 flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
              <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
          <h3 class="font-semibold text-foreground mb-2">Address</h3>
          <p class="text-muted-foreground">Paris, France</p>
        </div>
      </div>
    </div>
  </div>
</main>

<x-Home_page.footer />
