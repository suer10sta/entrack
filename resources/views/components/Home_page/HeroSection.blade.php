{{-- resources/views/components/hero-section.blade.php --}}

<section id="hero" class="hero-glow relative overflow-hidden pt-24 pb-20 lg:pt-32 lg:pb-32 transition-all duration-500">
    
    {{-- Background Image - extends to top behind header --}}
    <div class="absolute top-0 left-0 right-0 bottom-0 z-0" style="top: -80px; height: calc(100% + 80px);">
        <img src="{{ asset('img/background_hero.png') }}" alt="Hero Background" class="h-full w-full object-cover">
    </div>
    
    {{-- Overlay for better text readability --}}
    <div class="absolute top-0 left-0 right-0 bottom-0 z-0 bg-gradient-to-b from-black/20 via-transparent to-black/10" style="top: -80px; height: calc(100% + 80px);"></div>
    
    {{-- Animated Gradient Background Overlay --}}
    <div class="absolute top-0 left-0 right-0 bottom-0 z-0" style="top: -80px; height: calc(100% + 80px);">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-cyan-500/5 animate-gradient"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(14,165,233,0.08),transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(6,182,212,0.08),transparent_50%)]"></div>
    </div>

    {{-- Enterprise Management Visual Elements --}}
    <div class="absolute top-0 left-0 right-0 bottom-0 z-0 overflow-hidden" style="top: -80px; height: calc(100% + 80px);">
        {{-- Network/Connection Lines --}}
        <div class="absolute inset-0 opacity-[0.03]">
            <svg class="h-full w-full" viewBox="0 0 1200 800" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Network nodes and connections --}}
                <circle cx="200" cy="150" r="8" fill="#0EA5E9" class="animate-pulse"/>
                <circle cx="400" cy="200" r="8" fill="#06B6D4" class="animate-pulse" style="animation-delay: 0.5s"/>
                <circle cx="600" cy="180" r="8" fill="#0EA5E9" class="animate-pulse" style="animation-delay: 1s"/>
                <circle cx="800" cy="220" r="8" fill="#06B6D4" class="animate-pulse" style="animation-delay: 1.5s"/>
                <circle cx="1000" cy="160" r="8" fill="#0EA5E9" class="animate-pulse" style="animation-delay: 2s"/>
                
                <line x1="200" y1="150" x2="400" y2="200" stroke="#0EA5E9" stroke-width="1.5" opacity="0.3"/>
                <line x1="400" y1="200" x2="600" y2="180" stroke="#06B6D4" stroke-width="1.5" opacity="0.3"/>
                <line x1="600" y1="180" x2="800" y2="220" stroke="#0EA5E9" stroke-width="1.5" opacity="0.3"/>
                <line x1="800" y1="220" x2="1000" y2="160" stroke="#06B6D4" stroke-width="1.5" opacity="0.3"/>
                
                {{-- Organizational chart structure --}}
                <rect x="150" y="350" width="100" height="60" rx="4" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2" fill="none"/>
                <rect x="300" y="350" width="100" height="60" rx="4" stroke="#06B6D4" stroke-width="1.5" opacity="0.2" fill="none"/>
                <rect x="450" y="350" width="100" height="60" rx="4" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2" fill="none"/>
                <rect x="600" y="300" width="100" height="60" rx="4" stroke="#06B6D4" stroke-width="1.5" opacity="0.2" fill="none"/>
                <line x1="200" y1="300" x2="200" y2="350" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2"/>
                <line x1="200" y1="300" x2="350" y2="350" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2"/>
                <line x1="200" y1="300" x2="500" y2="350" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2"/>
                <line x1="200" y1="300" x2="650" y2="300" stroke="#0EA5E9" stroke-width="1.5" opacity="0.2"/>
                
                {{-- Data flow arrows --}}
                <path d="M100 500 L300 480 L500 500 L700 480 L900 500" stroke="#06B6D4" stroke-width="2" fill="none" opacity="0.2" stroke-dasharray="5,5" class="animate-dash"/>
                <path d="M100 600 L300 580 L500 600 L700 580 L900 600" stroke="#0EA5E9" stroke-width="2" fill="none" opacity="0.2" stroke-dasharray="5,5" class="animate-dash" style="animation-delay: 1s"/>
            </svg>
        </div>

        {{-- Dashboard Grid Pattern --}}
        <div class="absolute right-0 top-0 h-full w-1/2 opacity-[0.02]">
            <svg class="h-full w-full" viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="20" width="120" height="80" rx="8" stroke="currentColor" stroke-width="2" class="text-primary"/>
                <rect x="20" y="120" width="120" height="80" rx="8" stroke="currentColor" stroke-width="2" class="text-primary"/>
                <rect x="160" y="20" width="120" height="180" rx="8" stroke="currentColor" stroke-width="2" class="text-primary"/>
                <rect x="300" y="20" width="80" height="80" rx="8" stroke="currentColor" stroke-width="2" class="text-primary"/>
                <line x1="20" y1="220" x2="380" y2="220" stroke="currentColor" stroke-width="1" class="text-primary"/>
                <rect x="20" y="240" width="360" height="40" rx="4" stroke="currentColor" stroke-width="1" class="text-primary"/>
                <rect x="20" y="300" width="360" height="40" rx="4" stroke="currentColor" stroke-width="1" class="text-primary"/>
                <rect x="20" y="360" width="360" height="40" rx="4" stroke="currentColor" stroke-width="1" class="text-primary"/>
            </svg>
        </div>

        {{-- Chart/Graph Elements --}}
        <div class="absolute left-0 bottom-0 h-1/2 w-1/3 opacity-[0.02]">
            <svg class="h-full w-full" viewBox="0 0 300 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 300 L60 280 L100 260 L140 240 L180 220 L220 200 L260 180" stroke="currentColor" stroke-width="3" fill="none" class="text-primary"/>
                <path d="M20 300 L60 280 L100 260 L140 240 L180 220 L220 200 L260 180 L260 350 L20 350 Z" fill="currentColor" fill-opacity="0.2" class="text-primary"/>
                <line x1="20" y1="50" x2="20" y2="350" stroke="currentColor" stroke-width="2" class="text-primary"/>
                <line x1="20" y1="350" x2="280" y2="350" stroke="currentColor" stroke-width="2" class="text-primary"/>
            </svg>
        </div>

        {{-- Animated Blurred Gradient Orbs --}}
        <div class="absolute left-0 top-0 h-96 w-96 rounded-full bg-primary/5 blur-3xl animate-float"></div>
        <div class="absolute right-0 bottom-0 h-96 w-96 rounded-full bg-cyan-500/5 blur-3xl animate-float-delayed"></div>
        <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary/4 blur-3xl animate-pulse-slow"></div>
    </div>

    {{-- Enhanced Cursor-following glow --}}
    <div id="cursor-glow" class="absolute pointer-events-none z-5"></div>
    <div id="cursor-glow-secondary" class="absolute pointer-events-none z-4"></div>

    {{-- Light overlay for subtle depth --}}
    <div class="absolute inset-0 z-10 bg-gradient-to-b from-transparent via-transparent to-transparent"></div>

    {{-- Content --}}
    <div class="container relative z-20 mx-auto px-6">
        <div class="mx-auto max-w-5xl text-center">
            
            {{-- Badge --}}
            <div class="scroll-fade-in mb-6 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary backdrop-blur-sm" data-scroll-delay="0">
                <svg class="h-4 w-4 lightning-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                All-in-One Business Management Platform
            </div>

            {{-- Main Heading --}}
            <h1 class="scroll-fade-in mb-6 text-balance text-5xl font-bold leading-tight text-foreground lg:text-6xl xl:text-7xl" data-scroll-delay="100">
                Streamline Your Business Operations with
                <span class="bg-gradient-to-r from-primary via-primary/80 to-primary/60 bg-clip-text text-transparent">DalyTrac</span>
            </h1>

            {{-- Subheading --}}
            <p class="scroll-fade-in mx-auto mb-8 max-w-2xl text-pretty text-xl leading-relaxed text-gray-700 dark:text-gray-500 lg:text-2xl" data-scroll-delay="200">
                The complete solution for E-invoicing, Project Management, intelligent Time Tracking, and Team Collaboration. 
                <span class="font-semibold text-foreground">Your teams simply start and stop a timer on their projects and tasks, while DalyTrac quietly captures clean, billable hours in the background.</span>
            </p>

            {{-- Value Propositions --}}
            <div class="scroll-fade-in mb-10 flex flex-wrap items-center justify-center gap-4 sm:gap-6" data-scroll-delay="300">
                <div class="value-prop-badge group flex items-center gap-2.5 rounded-full bg-gradient-to-r from-green-500/20 to-emerald-500/20 px-5 py-3 text-base font-bold text-black backdrop-blur-sm transition-all duration-300 hover:scale-110 hover:from-green-500/30 hover:to-emerald-500/30">
                    <svg class="h-6 w-6 text-green-600 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-shadow-sm">No Credit Card Required</span>
                </div>
                <div class="value-prop-badge group flex items-center gap-2.5 rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 px-5 py-3 text-base font-bold text-black backdrop-blur-sm transition-all duration-300 hover:scale-110 hover:from-blue-500/30 hover:to-cyan-500/30">
                    <svg class="h-6 w-6 text-blue-600 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-shadow-sm">Free Trial Available</span>
                </div>
                <div class="value-prop-badge group flex items-center gap-2.5 rounded-full bg-gradient-to-r from-purple-500/20 to-pink-500/20 px-5 py-3 text-base font-bold text-black backdrop-blur-sm transition-all duration-300 hover:scale-110 hover:from-purple-500/30 hover:to-pink-500/30">
                    <svg class="h-6 w-6 text-purple-600 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-shadow-sm">Setup in Minutes</span>
                </div>
            </div>

            {{-- CTA Buttons --}}
            <div class="scroll-fade-in flex flex-col items-center justify-center gap-4 sm:flex-row" data-scroll-delay="400">
                <a href="{{ route('register') }}" class="cta-button-float group inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-8 py-4 text-base font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    Start Free Trial
                    <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>

                <a href="{{ route('demo') }}" class="cta-button-float-delayed inline-flex items-center justify-center gap-2 rounded-lg border-2 border-primary/30 bg-card px-8 py-4 text-base font-semibold text-foreground transition-all duration-300 hover:border-primary/50 hover:bg-primary/5">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="11" fill="#FF0000" stroke="#CC0000" stroke-width="1"/>
                        <path d="M9.5 7.5L16.5 12L9.5 16.5V7.5Z" fill="white"/>
                    </svg>
                    Watch Demo
                </a>
            </div>

            {{-- Trust Indicators --}}
            <div class="scroll-fade-in mt-12 flex flex-wrap items-center justify-center gap-8 text-sm text-gray-700 dark:text-gray-500" data-scroll-delay="500">
                <div class="text-center">
                    <div class="text-2xl font-bold text-foreground">10,000+</div>
                    <div>Active Users</div>
                </div>
                <div class="h-12 w-px bg-border"></div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-foreground">50,000+</div>
                    <div>Invoices Generated</div>
                </div>
                <div class="h-12 w-px bg-border"></div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-foreground">99.9%</div>
                    <div>Uptime</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Hero Glow Effect Styles --}}
<style>
    .hero-glow {
        position: relative;
    }
    
    /* Yellow glow effect for lightning bolt icon */
    .lightning-glow {
        filter: drop-shadow(0 0 4px rgba(255, 235, 59, 0.8)) 
                drop-shadow(0 0 8px rgba(255, 235, 59, 0.6))
                drop-shadow(0 0 12px rgba(255, 235, 59, 0.4));
        animation: glow-pulse 2s ease-in-out infinite;
    }
    
    @keyframes glow-pulse {
        0%, 100% {
            filter: drop-shadow(0 0 4px rgba(255, 235, 59, 0.8)) 
                    drop-shadow(0 0 8px rgba(255, 235, 59, 0.6))
                    drop-shadow(0 0 12px rgba(255, 235, 59, 0.4));
        }
        50% {
            filter: drop-shadow(0 0 6px rgba(255, 235, 59, 1)) 
                    drop-shadow(0 0 12px rgba(255, 235, 59, 0.8))
                    drop-shadow(0 0 18px rgba(255, 235, 59, 0.6));
        }
    }
    
    /* Value Proposition Badges - Attention Grabbing Styles */
    .value-prop-badge {
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
        animation: value-prop-pulse-1 3s ease-in-out infinite;
    }
    
    .value-prop-badge:nth-child(1) {
        animation: value-prop-pulse-1 3s ease-in-out infinite;
        animation-delay: 0s;
    }
    
    .value-prop-badge:nth-child(2) {
        animation: value-prop-pulse-2 3s ease-in-out infinite;
        animation-delay: 0.3s;
    }
    
    .value-prop-badge:nth-child(3) {
        animation: value-prop-pulse-3 3s ease-in-out infinite;
        animation-delay: 0.6s;
    }
    
    @keyframes value-prop-pulse-1 {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2),
                        0 0 20px rgba(34, 197, 94, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3),
                        0 0 30px rgba(34, 197, 94, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
    }
    
    @keyframes value-prop-pulse-2 {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2),
                        0 0 20px rgba(59, 130, 246, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3),
                        0 0 30px rgba(59, 130, 246, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
    }
    
    @keyframes value-prop-pulse-3 {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2),
                        0 0 20px rgba(168, 85, 247, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3),
                        0 0 30px rgba(168, 85, 247, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
    }
    
    .text-shadow-sm {
        text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5),
                     0 0 4px rgba(255, 255, 255, 0.3);
    }
    
    /* CTA Button Glow and Float Animations */
    .cta-button-float {
        animation: button-float-glow 3s ease-in-out infinite;
    }
    
    .cta-button-float-delayed {
        animation: button-float-glow-secondary 3s ease-in-out infinite;
        animation-delay: 0.5s;
    }
    
    @keyframes button-float-glow {
        0%, 100% {
            transform: translateY(0px);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.6),
                        0 0 40px rgba(14, 165, 233, 0.4),
                        0 0 60px rgba(14, 165, 233, 0.2),
                        0 4px 15px rgba(0, 0, 0, 0.2);
        }
        50% {
            transform: translateY(-10px);
            box-shadow: 0 0 30px rgba(14, 165, 233, 0.8),
                        0 0 60px rgba(14, 165, 233, 0.6),
                        0 0 90px rgba(14, 165, 233, 0.3),
                        0 4px 20px rgba(0, 0, 0, 0.3);
        }
    }
    
    @keyframes button-float-glow-secondary {
        0%, 100% {
            transform: translateY(0px);
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.4),
                        0 0 30px rgba(14, 165, 233, 0.3),
                        0 0 45px rgba(14, 165, 233, 0.15),
                        0 4px 15px rgba(0, 0, 0, 0.1);
        }
        50% {
            transform: translateY(-10px);
            box-shadow: 0 0 25px rgba(14, 165, 233, 0.6),
                        0 0 50px rgba(14, 165, 233, 0.4),
                        0 0 75px rgba(14, 165, 233, 0.2),
                        0 4px 20px rgba(0, 0, 0, 0.2);
        }
    }
    
    /* Ensure hover scale works with floating animation */
    .cta-button-float:hover,
    .cta-button-float-delayed:hover {
        animation-play-state: paused;
    }
    
    /* Enhanced Cursor-following glow - Primary */
    #cursor-glow {
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.35) 0%, rgba(14, 165, 233, 0.25) 20%, rgba(6, 182, 212, 0.15) 40%, rgba(6, 182, 212, 0.08) 60%, transparent 80%);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: opacity 0.4s ease, transform 0.1s linear;
        opacity: 0;
        filter: blur(80px);
        will-change: transform, opacity;
        pointer-events: none;
    }
    
    /* Secondary glow for depth */
    #cursor-glow-secondary {
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.3) 0%, rgba(6, 182, 212, 0.2) 30%, rgba(14, 165, 233, 0.12) 50%, transparent 75%);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: opacity 0.4s ease, transform 0.15s linear;
        opacity: 0;
        filter: blur(50px);
        will-change: transform, opacity;
        pointer-events: none;
    }
    
    /* Animated gradient background */
    @keyframes gradient {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }
    
    .animate-gradient {
        background-size: 200% 200%;
        animation: gradient 15s ease infinite;
    }
    
    /* Floating animation for orbs */
    @keyframes float {
        0%, 100% {
            transform: translate(0, 0) scale(1);
        }
        33% {
            transform: translate(30px, -30px) scale(1.1);
        }
        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }
    }
    
    .animate-float {
        animation: float 20s ease-in-out infinite;
    }
    
    .animate-float-delayed {
        animation: float 25s ease-in-out infinite;
        animation-delay: -5s;
    }
    
    /* Slow pulse animation */
    @keyframes pulse-slow {
        0%, 100% {
            opacity: 0.5;
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            opacity: 0.8;
            transform: translate(-50%, -50%) scale(1.2);
        }
    }
    
    .animate-pulse-slow {
        animation: pulse-slow 8s ease-in-out infinite;
    }
    
    /* Animated dash for data flow */
    @keyframes dash {
        to {
            stroke-dashoffset: -20;
        }
    }
    
    .animate-dash {
        animation: dash 3s linear infinite;
    }
    
    /* Initial state for scroll animations in hero */
    .scroll-fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .scroll-fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    // Make hero animations visible immediately on load (no scroll needed)
    document.addEventListener('DOMContentLoaded', function() {
        const heroElements = document.querySelectorAll('#hero .scroll-fade-in');
        heroElements.forEach((el, index) => {
            const delay = parseInt(el.getAttribute('data-scroll-delay') || 0);
            setTimeout(() => {
                el.classList.add('visible');
            }, delay);
        });
        
        // Enhanced Cursor-following glow effect
        const heroSection = document.getElementById('hero');
        const cursorGlow = document.getElementById('cursor-glow');
        const cursorGlowSecondary = document.getElementById('cursor-glow-secondary');
        
        if (heroSection && cursorGlow && cursorGlowSecondary) {
            let isHovering = false;
            let mouseX = 0;
            let mouseY = 0;
            let glowX = 0;
            let glowY = 0;
            let secondaryX = 0;
            let secondaryY = 0;
            
            // Smooth interpolation function
            function lerp(start, end, factor) {
                return start + (end - start) * factor;
            }
            
            // Animation loop for smooth following
            function animateGlow() {
                // Primary glow follows with slight delay for smooth effect
                glowX = lerp(glowX, mouseX, 0.1);
                glowY = lerp(glowY, mouseY, 0.1);
                
                // Secondary glow follows with more delay for depth
                secondaryX = lerp(secondaryX, mouseX, 0.15);
                secondaryY = lerp(secondaryY, mouseY, 0.15);
                
                cursorGlow.style.left = glowX + 'px';
                cursorGlow.style.top = glowY + 'px';
                
                cursorGlowSecondary.style.left = secondaryX + 'px';
                cursorGlowSecondary.style.top = secondaryY + 'px';
                
                requestAnimationFrame(animateGlow);
            }
            
            // Start animation loop
            animateGlow();
            
            // Track mouse movement
            heroSection.addEventListener('mousemove', function(e) {
                const rect = heroSection.getBoundingClientRect();
                mouseX = e.clientX - rect.left;
                mouseY = e.clientY - rect.top;
                
                if (!isHovering) {
                    cursorGlow.style.opacity = '1';
                    cursorGlowSecondary.style.opacity = '1';
                    isHovering = true;
                }
            });
            
            // Hide glow when mouse leaves
            heroSection.addEventListener('mouseleave', function() {
                cursorGlow.style.opacity = '0';
                cursorGlowSecondary.style.opacity = '0';
                isHovering = false;
            });
            
            // Show glow on mouse enter
            heroSection.addEventListener('mouseenter', function(e) {
                const rect = heroSection.getBoundingClientRect();
                mouseX = e.clientX - rect.left;
                mouseY = e.clientY - rect.top;
                cursorGlow.style.opacity = '1';
                cursorGlowSecondary.style.opacity = '1';
                isHovering = true;
            });
        }
    });
</script>
