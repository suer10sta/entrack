<!DOCTYPE html> 
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> 
    <head> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title>FAQ | Enterprise Tracker Pro</title>
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
        .faq-item-content { display: none; } 
        .faq-item-content.open { display: block; }  
        .faq-item { 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            overflow: hidden; 
            transition: box-shadow 0.3s ease; 
        } 
        .faq-item:hover { 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        } 
        .faq-item button { 
            padding: 1rem 1.5rem; 
        } 
        .faq-item-content { 
            padding: 1rem 1.5rem; 
            background-color: #ffffff; 
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
    </style> 

</head> 
<body class="antialiased bg-white text-gray-800"> 
    {{-- Header --}} 
    <x-Home_page.header /> 
    
    {{-- Main FAQ Section --}} 
    <main class="min-h-screen pt-24 sm:pt-28 lg:pt-32 pb-12 sm:pb-16 lg:pb-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 ">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 text-center">Frequently Asked Questions</h1>
                <p class="text-base text-gray-600 mb-10 text-center">
                    Find answers to common questions about Enterprise Tracker Pro.
                    Can't find what you're looking for? Contact our support team.
                </p>

                {{-- FAQ Items --}}
                <div class="space-y-4">
                    @php $faqItems = [
                    ['id'=>1, 'question'=>'What is Enterprise Tracker Pro?',
                    'answer'=>'Enterprise Tracker Pro is a comprehensive business management platform designed to help you manage invoices, projects, team members, and finances all in one place. It\'s built for businesses of all sizes.'],
                    ['id'=>2, 'question'=>'How do I get started?',
                    'answer'=>'Getting started is easy! Simply sign up for an account, set up your company information, and start adding your clients, projects, and team members. Our onboarding guide will walk you through each step.'],
                    ['id'=>3, 'question'=>'Is my data secure?',
                    'answer'=>'Yes, we take security very seriously. All data is encrypted in transit and at rest. We comply with industry standards and regularly perform security audits to ensure your information is protected.'],
                    ['id'=>4, 'question'=>'Can I integrate with other tools?',
                    'answer'=>'Yes, Enterprise Tracker Pro supports integrations with popular accounting software, payment processors, and communication tools. Check our integrations page for the full list.'],
                    ['id'=>5, 'question'=>'What payment methods do you accept?',
                    'answer'=>'We accept all major credit cards, bank transfers, and digital payment methods. You can choose monthly or annual billing, and we offer a 14-day free trial for new users.'],
                    ['id'=>6, 'question'=>'Is there customer support available?',
                    'answer'=>'We offer 24/7 customer support via email, chat, and phone. Our support team is dedicated to helping you get the most out of Enterprise Tracker Pro.'],
                    ['id'=>7, 'question'=>'Can I export my data?',
                    'answer'=>'Yes, you can export your data in various formats including CSV, PDF, and Excel. You have full control over your data and can export it at any time.'],
                    ['id'=>8, 'question'=>'What happens if I cancel my subscription?',
                    'answer'=>'You can cancel your subscription at any time. Your data will be retained for 30 days, giving you time to export it if needed. After 30 days, your account and data will be permanently deleted.'],
                    ];
                    @endphp
                    
                    @foreach ($faqItems as $item)
                    <div class="faq-item">
                        <button onclick="toggleFAQ({{ $item['id'] }})"
                            class="w-full flex items-center justify-between text-left rounded-lg transition">
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
                <div class="support-card mt-12"> 
                    <h2 class="text-2xl font-semibold text-gray-900 mb-3">Still have questions?</h2> 
                    <p class="text-gray-700 mb-5">Can't find the answer you're looking for? Our support team is here to help.</p> 
                    <a href="{{ url('/contact') }}" class="inline-block px-5 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm"> Contact Support </a> 
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
        function toggleFAQ(id) {
            // *** THIS IS THE FIX ***
            // We use JavaScript string concatenation ('...') + id
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);

            if (!content || !icon) {
                console.error('Could not find elements for ID:', id);
                return;
            }

            const isOpen = content.classList.contains('open');

            // Close all other items
            document.querySelectorAll('.faq-item-content').forEach(c => {
                c.classList.remove('open');
                c.style.paddingTop = ''; // Reset padding
                c.style.paddingBottom = '';
            });
            
            document.querySelectorAll('.faq-icon').forEach(ic => {
                ic.classList.remove('open');
            });

            // Open the clicked item if it was closed
            if (!isOpen) {
                content.classList.add('open');
                icon.classList.add('open');
            }
        }
    </script>
</body> 

</html>