<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>DalyTrac – E-invoicing, Project Management & Time Tracking</title>
    <meta name="description" content="DalyTrac is an all-in-one business platform for E-invoicing, project management, intelligent time tracking and team collaboration, helping you capture clean, billable hours with ease.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('welcome') }}">

    <meta property="og:title" content="DalyTrac – E-invoicing, Project Management & Time Tracking">
    <meta property="og:description" content="Centralize your invoices, projects, time tracking and teams in one powerful, easy-to-use platform with DalyTrac.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('welcome') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DalyTrac – E-invoicing, Project Management & Time Tracking">
    <meta name="twitter:description" content="Manage projects, invoices and time tracking in one place and simplify your business operations with DalyTrac.">

    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "DalyTrac",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ route('welcome') }}",
        "description": "All-in-one platform for e-invoicing, project management, time tracking and team collaboration.",
        "offers": {
          "@type": "Offer",
          "price": "0.00",
          "priceCurrency": "EUR",
          "description": "Free trial available. Paid plans for teams and enterprises."
        },
        "publisher": {
          "@type": "Organization",
          "name": "DalyTrac"
        }
      }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="welcome/css/bootstrap.min.css">
    <link rel="stylesheet" href="welcome/css/font-awesome.min.css">
    <link rel="stylesheet" href="welcome/css/aos.css">
    <link rel="stylesheet" href="welcome/css/owl.carousel.min.css">
    <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css">
    

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css">
    
    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')
    
    <style>
        /* Custom Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #3AB0E2 0%, #2BA3D8 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand:hover {
            color: white !important;
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3AB0E2;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .navbar-nav {
            align-items: center;
            gap: 2rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            font-size: 1rem;
            transition: opacity 0.3s ease;
            padding: 0.5rem 0 !important;
        }
        
        .nav-link:hover {
            opacity: 0.8;
        }
        
        .login-btn {
            background: white;
            color: #3AB0E2 !important;
            padding: 0.6rem 2rem !important;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid white;
        }
        
        .login-btn:hover {
            background: transparent;
            color: white !important;
            transform: translateY(-2px);
        }
        
        .navbar-toggler {
            border-color: white;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
    </style>

    <style>
        /* Prevent horizontal overflow + layout shift (scrollbar) */
        html {
            overflow-x: hidden !important;
            scrollbar-gutter: stable;
            width: 100%;
        }
        body {
            overflow-x: hidden !important;
            width: 100%;
            max-width: 100%;
        }
        img, svg, video, canvas {
            max-width: 100%;
        }
        @supports (overflow: clip) {
            html, body {
                overflow-x: clip !important;
            }
        }

        /* Floating chatbot button & panel */
        .chatbot-toggle-btn {
            position: fixed;
            right: 1.75rem;
            bottom: 1.75rem;
            z-index: 50;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #0EA5E9, #22C55E);
            color: white;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35);
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .chatbot-toggle-btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.45);
        }

        .chatbot-panel {
            position: fixed;
            right: 1.5rem;
            bottom: 5.25rem;
            width: 22rem;
            max-width: calc(100% - 2rem);
            height: 26rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.96);
            color: #E5E7EB;
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 50;
            transform-origin: bottom right;
            opacity: 0;
            transform: scale(0.9) translateY(10px);
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .chatbot-panel.open {
            opacity: 1;
            transform: scale(1) translateY(0);
            pointer-events: auto;
        }

        .chatbot-header {
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #0EA5E9, #22C55E);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .chatbot-header-title {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .chatbot-header-title span:nth-child(1) {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .chatbot-header-title span:nth-child(2) {
            font-size: 0.7rem;
            opacity: 0.9;
        }

        .chatbot-close-btn {
            background: rgba(15, 23, 42, 0.18);
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.35);
            width: 1.8rem;
            height: 1.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
        }

        .chatbot-messages {
            flex: 1;
            padding: 0.75rem 0.75rem 0.5rem;
            overflow-y: auto;
            font-size: 0.8rem;
        }

        .chatbot-message-row {
            display: flex;
            margin-bottom: 0.4rem;
        }

        .chatbot-message-row.bot {
            justify-content: flex-start;
        }

        .chatbot-message-row.user {
            justify-content: flex-end;
        }

        .chatbot-bubble {
            max-width: 85%;
            padding: 0.5rem 0.7rem;
            border-radius: 0.75rem;
            line-height: 1.4;
        }

        .chatbot-bubble.bot {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.5);
        }

        .chatbot-bubble.user {
            background: rgba(37, 99, 235, 0.95);
            border: 1px solid rgba(59, 130, 246, 0.9);
        }

        .chatbot-input-row {
            padding: 0.45rem 0.6rem 0.55rem;
            border-top: 1px solid rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.98);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .chatbot-input-row input[type="text"] {
            flex: 1;
            border-radius: 999px;
            border: 1px solid rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.9);
            color: #E5E7EB;
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            outline: none;
        }

        .chatbot-input-row input[type="text"]::placeholder {
            color: #6B7280;
        }

        .chatbot-send-btn {
            border-radius: 999px;
            border: none;
            width: 2.2rem;
            height: 2.2rem;
            background: linear-gradient(135deg, #22C55E, #0EA5E9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .chatbot-suggested {
            padding: 0 0.75rem 0.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
        }

        .chatbot-suggested button {
            border-radius: 999px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.9);
            color: #9CA3AF;
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
            cursor: pointer;
        }

        .chatbot-status {
            font-size: 0.68rem;
            color: #9CA3AF;
            padding: 0 0.85rem 0.3rem;
        }

        @media (max-width: 640px) {
            .chatbot-panel {
                right: 0.75rem;
                bottom: 5rem;
                width: calc(100% - 1.5rem);
                height: 60vh;
            }
        }
    </style>
  </head>

  <body class="antialiased pt-20 lg:pt-36 overflow-x-hidden">

    <x-Home_page.header />


    <!-- HERO -->
    <x-Home_page.HeroSection />                                  


    <!-- ABOUT -->
    <x-Home_page.FeaturesSection />

    
    <!-- ACCOUNT_TYPES -->
    <x-Home_page.Account_Types />


    <!-- PROJECT -->
    <x-Home_page.ClientTestimonials />



    <x-Home_page.footer/>

    <!-- Floating Chatbot Widget -->
    <button id="chatbot-toggle" class="chatbot-toggle-btn" aria-label="Open DalyTrac assistant">
        <!-- Simple chat icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5A8.5 8.5 0 0 1 21 11v.5z"></path>
        </svg>
    </button>

    <div id="chatbot-panel" class="chatbot-panel" aria-live="polite">
        <div class="chatbot-header">
            <div class="chatbot-header-title">
                <span>DalyTrac Assistant</span>
                <span>Ask about features, pricing, and more</span>
            </div>
            <button type="button" id="chatbot-close" class="chatbot-close-btn" aria-label="Close chat">
                &times;
            </button>
        </div>
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="chatbot-message-row bot">
                <div class="chatbot-bubble bot">
                    Bonjour 👋&nbsp;I’m the DalyTrac assistant. Ask me about features, pricing, time tracking, invoicing, or how DalyTrac can help your business.
                </div>
            </div>
        </div>
        <div class="chatbot-suggested">
            <button type="button" data-question="What can DalyTrac do for my business?">What can DalyTrac do?</button>
            <button type="button" data-question="Do you offer a free trial?">Free trial</button>
            <button type="button" data-question="How does time tracking work?">Time tracking</button>
        </div>
        <div class="chatbot-status" id="chatbot-status"></div>
        <div class="chatbot-input-row">
            <input type="text" id="chatbot-input" placeholder="Type your question..." autocomplete="off">
            <button type="button" id="chatbot-send" class="chatbot-send-btn">
                ➤
            </button>
        </div>
    </div>


    <!-- SCRIPTS -->
    <script src="welcome/js/jquery.min.js"></script>
    <script src="welcome/js/bootstrap.min.js"></script>
    <script src="welcome/js/aos.js"></script>
    <script src="welcome/js/owl.carousel.min.js"></script>
    <script src="welcome/js/smoothscroll.js"></script>
    <script src="welcome/js/custom.js"></script>

    <script>
        (function() {
            const panel = document.getElementById('chatbot-panel');
            const toggleBtn = document.getElementById('chatbot-toggle');
            const closeBtn = document.getElementById('chatbot-close');
            const messagesEl = document.getElementById('chatbot-messages');
            const inputEl = document.getElementById('chatbot-input');
            const sendBtn = document.getElementById('chatbot-send');
            const statusEl = document.getElementById('chatbot-status');

            function scrollToBottom() {
                if (messagesEl) {
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                }
            }

            function appendMessage(text, sender) {
                const row = document.createElement('div');
                row.className = 'chatbot-message-row ' + (sender === 'user' ? 'user' : 'bot');

                const bubble = document.createElement('div');
                bubble.className = 'chatbot-bubble ' + (sender === 'user' ? 'user' : 'bot');
                bubble.textContent = text;

                row.appendChild(bubble);
                messagesEl.appendChild(row);
                scrollToBottom();
            }

            async function sendQuestion(rawQuestion) {
                const question = rawQuestion || (inputEl ? inputEl.value : '');
                if (!question || !question.trim()) return;

                appendMessage(question, 'user');
                if (inputEl) inputEl.value = '';

                if (statusEl) statusEl.textContent = 'Thinking...';

                try {
                    const resp = await fetch("{{ route('chatbot.ask') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ question })
                    });

                    if (!resp.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await resp.json();
                    const answer = (data && data.answer) ? data.answer : "Sorry, I couldn’t get an answer right now. Please try again or use the contact form.";
                    appendMessage(answer, 'bot');
                } catch (e) {
                    appendMessage("I’m having trouble reaching the server. Please check your connection or try again in a moment.", 'bot');
                } finally {
                    if (statusEl) statusEl.textContent = '';
                }
            }

            if (toggleBtn && panel) {
                toggleBtn.addEventListener('click', function() {
                    panel.classList.toggle('open');
                });
            }

            if (closeBtn && panel) {
                closeBtn.addEventListener('click', function() {
                    panel.classList.remove('open');
                });
            }

            if (sendBtn) {
                sendBtn.addEventListener('click', function() {
                    sendQuestion();
                });
            }

            if (inputEl) {
                inputEl.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendQuestion();
                    }
                });
            }

            // Suggested questions
            document.querySelectorAll('.chatbot-suggested button').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const q = btn.getAttribute('data-question') || btn.textContent;
                    sendQuestion(q);
                });
            });
        })();
    </script>

    {{-- Tracking JS moved to the shared footer component --}}

  </body>

</html>
