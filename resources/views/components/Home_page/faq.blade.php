<!DOCTYPE html> 
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> 
    <head> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title>{{ __('messages.FAQ') }} | DalyTrac</title>
        <link rel="preconnect" href="https://fonts.bunny.net"> 
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" /> 
        <link rel="stylesheet" href="welcome/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="welcome/css/font-awesome.min.css"> 
        <link rel="stylesheet" href="welcome/css/aos.css"> 
        <link rel="stylesheet" href="welcome/css/owl.carousel.min.css"> 
        <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css"> 
        <link href="{{ asset('css/app.css') }}" rel="stylesheet"> @vite('resources/css/app.css') 
        <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css"> 
        
    <style> 
        .faq-item button:focus { outline: none; } 
        .faq-item-content { 
            max-height: 0; 
            overflow: hidden; 
            transition: max-height 0.3s ease, padding-top 0.3s ease, padding-bottom 0.3s ease; 
            padding-top: 0; 
            padding-bottom: 0; 
        } 
        .faq-item-content.open { 
            max-height: 500px; 
            padding-top: 0.75rem; 
            padding-bottom: 1rem; 
        }  
        .faq-item { 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            overflow: hidden; 
            transition: box-shadow 0.3s ease, transform 0.2s ease; 
            background-color: #ffffff; 
        } 
        .faq-item:hover { 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            transform: translateY(-2px); 
        } 
        .faq-item button { 
            padding: 1rem 1.5rem; 
            background-color: #ffffff; 
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            gap: 1rem; 
        } 
        .faq-item-content { 
            padding-left: 1.5rem; 
            padding-right: 1.5rem; 
            background-color: #ffffff; 
        } 
        .faq-icon { 
            transition: transform 0.25s ease; 
        } 
        .faq-icon.open { 
            transform: rotate(180deg); 
        }
        .support-card { 
            border-radius: 12px; 
            padding: 2rem; 
            background-color: #f0f9ff; 
            border: 1px solid #bae6fd; 
            margin-top: 3rem; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        } 
        .support-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
            }
        .faq-page-bg {
            background: radial-gradient(circle at top left, rgba(59,130,246,0.12), transparent 55%),
                        radial-gradient(circle at bottom right, rgba(14,165,233,0.12), transparent 55%);
        }
        .faq-tabs {
            padding: 0.25rem;
            border-radius: 9999px;
            background-color: rgba(255,255,255,0.7);
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }
        .faq-tab {
            border-radius: 9999px;
            padding: 0.35rem 0.9rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            border: none;
            background-color: transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .faq-tab-active {
            background-color: #eff6ff;
            color: #0ea5e9;
            box-shadow: 0 1px 3px rgba(59,130,246,0.25);
        }
    </style> 

</head> 
<body class="antialiased bg-white text-gray-800"> 
    {{-- Header --}} 
    <x-Home_page.header /> 
    
    {{-- Main FAQ Section --}} 
    <main class="min-h-screen pt-24 sm:pt-28 lg:pt-32 pb-12 sm:pb-16 lg:pb-20 faq-page-bg">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
            <div class="max-w-5xl mx-auto">
                <div class="text-center">
                    <span class="inline-flex items-center px-3 py-1 mb-4 text-xs font-semibold tracking-wide text-sky-700 uppercase bg-sky-50 border border-sky-100 rounded-full">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('messages.Help & Support') }}
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('messages.Frequently Asked Questions') }}</h1>
                    <p class="text-base text-gray-600 max-w-2xl mx-auto">
                        {{ __('messages.Everything you need to know about using DalyTrac for your team, projects, and billing.') }}
                    </p>
                </div>

                <div class="mt-12 max-w-3xl mx-auto">
                    {{-- FAQ Items --}}
                    <div class="inline-flex items-center justify-center mb-6 space-x-2 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <span class="px-3 py-1 rounded-full bg-sky-50 text-sky-600 border border-sky-100">{{ __('messages.General / Product & Billing') }}</span>
                    </div>

                    <div class="space-y-4">
                    @php $faqItems = [
                    ['id'=>1, 'question'=>__('messages.What is DalyTrac?'),
                    'answer'=>__('messages.DalyTrac is a comprehensive business management platform designed to help you manage invoices, projects, team members, and finances all in one place. It\'s built for businesses of all sizes.')],
                    ['id'=>2, 'question'=>__('messages.How do I get started?'),
                    'answer'=>__('messages.Getting started is easy! Simply sign up for an account, set up your company information, and start adding your clients, projects, and team members. Our onboarding guide will walk you through each step.')],
                    ['id'=>3, 'question'=>__('messages.Is my data secure?'),
                    'answer'=>__('messages.Yes, we take security very seriously. All data is encrypted in transit and at rest. We comply with industry standards and regularly perform security audits to ensure your information is protected.')],
                    ['id'=>4, 'question'=>__('messages.Can I integrate with other tools?'),
                    'answer'=>__('messages.We focus on built-in time tracking, projects, invoicing, and calendar. If you need a specific integration, contact support and we\'ll discuss options.')],
                    ['id'=>5, 'question'=>__('messages.What payment methods do you accept?'),
                    'answer'=>__('messages.We accept all major credit cards, bank transfers, and digital payment methods. You can choose monthly or annual billing, and we offer a 14-day free trial for new users.')],
                    ['id'=>6, 'question'=>__('messages.Is there customer support available?'),
                    'answer'=>__('messages.We offer 24/7 customer support via email, chat, and phone. Our support team is dedicated to helping you get the most out of DalyTrac.')],
                    ['id'=>7, 'question'=>__('messages.Can I export my data?'),
                    'answer'=>__('messages.Data export isn\'t offered in-app today. If you need an export, contact support and we\'ll review options.')],
                    ['id'=>8, 'question'=>__('messages.What happens if I cancel my subscription?'),
                    'answer'=>__('messages.You can cancel your subscription at any time. Your data will be retained for 30 days, giving you time to export it if needed. After 30 days, your account and data will be permanently deleted.')],
                    ];
                    @endphp
                    
                        @foreach ($faqItems as $item)
                        <div class="faq-item">
                            <button 
                                type="button"
                                onclick="toggleFAQ({{ $item['id'] }})"
                                class="rounded-lg transition"
                                aria-expanded="false"
                                aria-controls="content-{{ $item['id'] }}"
                                id="faq-button-{{ $item['id'] }}">
                                <h3 class="text-base font-semibold text-gray-900">{{ $item['question'] }}</h3>
                                <!-- Chevron Icon -->
                                <svg id="icon-{{ $item['id'] }}" class="faq-icon h-5 w-5 text-sky-500 flex-shrink-0"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div id="content-{{ $item['id'] }}" class="faq-item-content">
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $item['answer'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Contact Support Section --}}
                    <div class="support-card shadow-md shadow-sky-100 max-w-xl mx-auto">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.Need a quick answer?') }}</h2>
                        <p class="text-sm text-gray-700 mb-4 text-center">
                            {{ __('messages.Talk to our team and get best practices for tracking time, invoices, and projects in DalyTrac.') }}
                        </p>
                        <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center w-full px-5 py-2.5 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm font-medium">
                            {{ __('messages.Contact support') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
     {{-- Footer --}} 
     <div class="mt-16">
     </div> 
     {{-- space before footer --}} 
     <x-Home_page.footer /> 
     {{-- JS Accordion --}} 
     <script>
        // Open the first FAQ item by default on load for better guidance
        document.addEventListener('DOMContentLoaded', function () {
            const firstContent = document.getElementById('content-1');
            const firstIcon = document.getElementById('icon-1');
            const firstButton = document.getElementById('faq-button-1');

            if (firstContent && firstIcon && firstButton) {
                firstContent.classList.add('open');
                firstIcon.classList.add('open');
                firstButton.setAttribute('aria-expanded', 'true');
            }
        });

        function toggleFAQ(id) {
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);
            const button = document.getElementById('faq-button-' + id);

            if (!content || !icon || !button) {
                console.error('Could not find elements for ID:', id);
                return;
            }

            const isOpen = content.classList.contains('open');

            // Close all other items
            document.querySelectorAll('.faq-item-content').forEach(c => {
                c.classList.remove('open');
            });
            
            document.querySelectorAll('.faq-icon').forEach(ic => {
                ic.classList.remove('open');
            });

            document.querySelectorAll('[id^="faq-button-"]').forEach(btn => {
                btn.setAttribute('aria-expanded', 'false');
            });

            // Open the clicked item if it was closed
            if (!isOpen) {
                content.classList.add('open');
                icon.classList.add('open');
                button.setAttribute('aria-expanded', 'true');
            }
        }
    </script>
</body> 

</html>
