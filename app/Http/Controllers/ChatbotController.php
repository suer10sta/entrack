<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Handle chatbot questions from the landing page.
     *
     * This is a lightweight, FAQ-style “smart” bot that uses
     * simple keyword matching to return helpful answers about DalyTrac.
     * You can later replace this logic with an external AI API if needed.
     */
    public function ask(Request $request)
    {
        $question = (string) $request->input('question', '');
        $normalized = Str::lower($question);

        // Basic guard
        if (trim($normalized) === '') {
            return response()->json([
                'answer' => "I’m here to help with questions about DalyTrac. For example, you can ask about pricing, features, free trial, or integrations.",
            ]);
        }

        $answer = $this->matchAnswer($normalized);

        return response()->json([
            'answer' => $answer,
        ]);
    }

    /**
     * Very small “knowledge base” about DalyTrac.
     *
     * This keeps all logic server-side so you don’t expose rules in JS.
     */
    protected function matchAnswer(string $q): string
    {
        // Pricing
        if (Str::contains($q, ['price', 'pricing', 'cost', 'expensive', 'fee', 'fees'])) {
            return "DalyTrac is offered as a subscription-based SaaS solution. Pricing depends on the number of users and modules you activate (time tracking, invoicing, projects, etc.). Contact us from the Contact page for a tailored quote or to discuss your needs.";
        }

        // Subscription / contract length / cancellation
        if (Str::contains($q, ['cancel', 'cancellation', 'commitment', 'engagement', 'contract', 'resilier', 'résilier', 'abonnement'])) {
            return "DalyTrac is typically offered as a flexible subscription with no long-term lock-in. If you ever wish to cancel or adjust your plan, simply contact our team and we will guide you through the process and data export if needed.";
        }

        // Free trial / demo
        if (Str::contains($q, ['trial', 'free', 'demo', 'test', 'essai'])) {
            return "Yes, DalyTrac offers a free trial so you can test the platform with your team. You can start from the “Start Free Trial” button on the homepage or request a guided demo from our team.";
        }

        // Setup / onboarding
        if (Str::contains($q, ['start', 'getting started', 'setup', 'set up', 'configure', 'configurer', 'onboard', 'onboarding', 'installation'])) {
            return "Getting started with DalyTrac is simple. We help you configure your company, teams, projects and invoicing rules, and you can import existing clients and projects. Our team can also accompany you during onboarding if you prefer a guided setup.";
        }

        // Time tracking
        if (Str::contains($q, ['time tracking', 'timer', 'chrono', 'temps', 'pointage'])) {
            return "DalyTrac provides chrono-based time tracking: employees simply start and stop a timer on their tasks and projects. The system automatically generates clean timesheets and gives you visibility on billable hours and productivity.";
        }

        // Invoicing
        if (Str::contains($q, ['invoice', 'invoicing', 'facture', 'billing', 'e-invoicing', 'peppol'])) {
            return "DalyTrac lets you create, send and track invoices, credit notes and quotes (devis). It supports traditional PDF/email invoicing and can connect to modern e-invoicing flows such as PEPPOL, depending on your country’s requirements.";
        }

        // Expenses / note de frais
        if (Str::contains($q, ['expense', 'expenses', 'note de frais', 'frais', 'kilometric', 'kilométrique'])) {
            return "With DalyTrac you can manage employee expenses (notes de frais), attach receipts, validate them and link them to projects or clients. This helps you track real project costs and reimburse employees quickly.";
        }

        // Projects & tasks
        if (Str::contains($q, ['project', 'projects', 'task', 'tasks', 'projet'])) {
            return "With DalyTrac you can manage projects, subprojects and tasks, assign them to teams or employees, follow progress, and link time tracking and billing directly to those projects.";
        }

        // Employees / HR
        if (Str::contains($q, ['employee', 'employees', 'team', 'staff', 'collaborateur', 'hr'])) {
            return "DalyTrac centralizes your employee data, timesheets and activity. You can organize teams, set roles and permissions, and follow who is working on which project in real time.";
        }

        // Security / data protection
        if (Str::contains($q, ['security', 'secure', 'sécurité', 'securite', 'data protection', 'rgpd', 'gdpr', 'privacy', 'confidentiality'])) {
            return "Security and data protection are a priority for DalyTrac. Your data is hosted on secure infrastructure, regularly backed up and only accessible to authorized users in your organization. We follow good practices for data protection and can provide more details on request.";
        }

        // Hosting / cloud / access
        if (Str::contains($q, ['cloud', 'saas', 'hosted', 'hosting', 'hébergé', 'heberge', 'online', 'web', 'browser', 'navigateur'])) {
            return "DalyTrac is a cloud-based (SaaS) solution accessible from a web browser. This means you don’t have to install heavy software on your machines, and your team can connect securely from anywhere with an internet connection.";
        }

        // Mobile / tablet
        if (Str::contains($q, ['mobile', 'smartphone', 'phone', 'android', 'ios', 'tablet', 'tablette'])) {
            return "DalyTrac is designed to be usable on modern browsers, including on tablets and smartphones. This makes it easy for teams on the move to track their time and access key information. For specific mobile app availability, please contact our team.";
        }

        // Reports / analytics
        if (Str::contains($q, ['report', 'analytics', 'kpi', 'dashboard', 'rapport'])) {
            return "DalyTrac includes dashboards and exportable reports for invoices, projects, time tracking and expenses. You can quickly see your revenue, outstanding invoices, workload and profitability.";
        }

        // Integrations / export
        if (Str::contains($q, ['integrate', 'integration', 'export', 'api', 'excel', 'csv'])) {
            return "DalyTrac lets you export key data (for example invoices or time reports) in formats suitable for accounting tools or further analysis. For more advanced integrations or API access, please contact us via the contact form.";
        }

        // Training / accompagnement
        if (Str::contains($q, ['training', 'formation', 'accompagnement', 'help getting started', 'support utilisateur'])) {
            return "We can provide training sessions and onboarding support for your administrators and end users so that your team quickly adopts DalyTrac. Contact us to discuss the format that suits you best (remote, on-site, or video sessions).";
        }

        // Types / size of companies
        if (Str::contains($q, ['small business', 'sme', 'pme', 'large company', 'enterprise', 'independent', 'freelance'])) {
            return "DalyTrac is used by a wide range of organizations: from small teams and independent professionals to larger SMEs. The solution is modular, so you can start small and activate more features as you grow.";
        }

        // Support
        if (Str::contains($q, ['support', 'help', 'assistance', 'contact'])) {
            return "If you need help or have a specific question, you can reach us via the Contact page in the main menu. Our team will answer you as soon as possible.";
        }

        // Languages / region
        if (Str::contains($q, ['language', 'langue', 'french', 'anglais', 'english'])) {
            return "DalyTrac is available in multiple languages (including French and English). If you have a specific localization requirement, please mention it when you contact us.";
        }

        // Default fallback
        return "I’m not sure I understood your question, but I can help with topics like pricing, features, time tracking, invoicing, projects, teams and reports in DalyTrac. If your question is specific to your company, feel free to use the contact form so our team can answer you personally.";
    }
}


