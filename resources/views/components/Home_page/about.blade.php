<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About | Enterprise Tracker Pro</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
      href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
      rel="stylesheet"
    />
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/aos.css" />
    <!-- This script is for the "fas fa-arrow-right" icon in your CTA button -->
    <script
      src="https://kit.fontawesome.com/a076d05399.js"
      crossorigin="anonymous"
    ></script>
  </head>

  <!-- 
    CHANGES:
    - bg-white -> bg-card (or bg-white, they are the same in your config)
    - text-slate-600 -> text-muted
  -->
  <body class="antialiased bg-card text-muted font-figtree">
    <x-Home_page.header />

    <main>
      
      <section class="relative pt-40 pb-24 lg:pt-48 lg:pb-32 overflow-hidden" data-aos="fade-in">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,theme(colors.secondary)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.secondary)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        
        <div class="relative z-10 container mx-auto px-6 text-center">
          <div class="inline-block mb-6 px-4 py-1.5 rounded-full bg-secondary border border-border text-primary text-sm font-semibold tracking-wide uppercase">
            About Us
          </div>
          <h1 class="text-5xl lg:text-7xl font-bold text-foreground mb-8 tracking-tight">
            Building the future of <br />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent">Enterprise Management</span>
          </h1>
          <p class="text-xl text-muted max-w-3xl mx-auto leading-relaxed">
            We strip away the complexity of business operations, giving you a clean, blue-sky environment where your team can thrive.
          </p>
        </div>
      </section>

      <section class="py-24 bg-secondary">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12 items-center">
                <div class="md:w-1/2" data-aos="fade-right">
                    <h2 class="text-3xl font-bold text-foreground mb-6 border-l-4 border-primary pl-4">
                        Our Mission
                    </h2>
                    <p class="text-lg text-muted mb-6">
                        We believe software shouldn't force you to change how you work. It should adapt to you.
                    </p>
                    <p class="text-lg text-muted">
                        Our mission is to provide a unified platform where invoices, projects, and people connect seamlessly, removing the "digital friction" that slows down modern businesses.
                    </p>
                </div>
                <div class="md:w-1/2" data-aos="fade-left">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-card p-6 rounded-2xl shadow-sm border-t-4 border-primary">
                            <h3 class="text-4xl font-bold text-foreground mb-1">99%</h3>
                            <p class="text-sm text-muted-foreground">Uptime Reliability</p>
                        </div>
                        <div class="bg-primary p-6 rounded-2xl shadow-sm text-primary-foreground mt-8">
                            <h3 class="text-4xl font-bold mb-1">24/7</h3>
                            <p class="text-sm opacity-90">Dedicated Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </section>

      <section class="py-24 bg-card" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-7xl">
          
          <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Everything you need</h2>
            <p class="text-muted max-w-2xl mx-auto">A comprehensive suite of tools designed to replace your fragmented software stack.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Invoice Management</h3>
              <p class="text-muted leading-relaxed">Automated billing and payment tracking to keep cash flow healthy.</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Project Tracking</h3>
              <p class="text-muted leading-relaxed">Real-time milestones and task delegation for complex workflows.</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Team Management</h3>
              <p class="text-muted leading-relaxed">HR tools to monitor performance and allocate resources efficiently.</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Advanced Analytics</h3>
              <p class="text-muted leading-relaxed">Data-driven insights to guide your strategic decisions.</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Client Relations</h3>
              <p class="text-muted leading-relaxed">Maintain client relationships and history in one centralized place.</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">Expense Control</h3>
              <p class="text-muted leading-relaxed">Digital logs for all company spending and budget adherence.</p>
            </div>

          </div>
        </div>
      </section>

      <section class="py-24 bg-secondary border-t border-border" data-aos="zoom-in">
        <div class="container mx-auto px-6 text-center">
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-card border border-border text-primary text-xs font-bold tracking-wide uppercase mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                Personalized Solutions
            </div>
            
            <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-6 tracking-tight">
                Does your business need <br/> something unique?
            </h2>
            
            <p class="text-lg text-muted mb-10 max-w-2xl mx-auto leading-relaxed">
                Every workflow is different. We can personalize Enterprise Tracker Pro to fit your specific needs—adding custom fields, reports, or integrations just for you.
            </p>

            <a href="/contact" class="inline-flex items-center gap-3 px-8 py-4 bg-primary text-primary-foreground font-bold rounded-lg shadow-lg shadow-primary/20 hover:bg-primary/90 hover:-translate-y-1 transition-all duration-300">
                <span>Contact Us for Customization</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
      </section>

    </main>

    <x-Home_page.footer />

    <script src="welcome/js/aos.js"></script>
    <script>
      AOS.init({ duration: 800, once: true, offset: 50 });
    </script>
  </body>
</html>