<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PeriodInvoiceEditViewTest extends TestCase
{
    private function renderPage(string $role = 'Admin', string $locale = 'fr', array $errors = []): string
    {
        $this->withoutVite();
        app()->setLocale($locale);
        $user = \Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1]);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($candidate) => $candidate === $role);
        $this->actingAs($user);
        request()->setLaravelSession(app('session.store'));
        $template = file_get_contents(resource_path('views/admin/invoices/editPeriodInvoice.blade.php'));
        // Exercise the actual page/form, with the unrelated dashboard shell omitted.
        $template = preg_replace('#<x-(?:dashboard\.(?:sidebar|navbar)|employees\.sidebar|favicon)\s*/>#', '', $template);
        $template = str_replace(['@livewireStyles', '@livewireScripts'], '', $template);
        return Blade::render($template, [
            'invoice' => (object) ['id' => 1123, 'number' => 3, 'created_at' => '2026-10-21', 'project_id' => 7,
                'project_name' => 'Original project', 'start_date' => '2026-10-01', 'end_date' => '2026-10-21',
                'comment' => 'Original note <script>example</script>', 'total_htva' => 1200, 'total_tva' => 252, 'total_ttc' => 1452],
            'entreprise' => (object) ['invoice_prefix' => 'INV'],
            'projects' => collect([(object) ['id' => 7, 'name' => 'Original project', 'budget' => 1200, 'tva' => 21], (object) ['id' => 8, 'name' => 'Second project', 'budget' => 2000, 'tva' => 21]]),
            'errors' => (new ViewErrorBag)->put('default', new MessageBag($errors)),
        ]);
    }

    private function xpath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8" ?>'.$html);
        return new \DOMXPath($dom);
    }

    public function test_redesigned_form_preserves_routes_and_values_for_each_role(): void
    {
        foreach (['Admin' => 'admin', 'Employee' => 'employee', 'Responsable de projet' => 'Responsable_de_projet', 'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire'] as $role => $prefix) {
            $html = $this->renderPage($role);
            $xpath = $this->xpath($html);
            $this->assertSame(route($prefix.'.invoices.updatePeriodInvoice', 1123), $xpath->evaluate('string(//form[@id="periodInvoiceForm"]/@action)'));
            $this->assertSame('PUT', $xpath->evaluate('string(//input[@name="_method"]/@value)'));
            $this->assertSame(1, $xpath->query('//input[@name="_token"]')->length);
            $this->assertSame('7', $xpath->evaluate('string(//select[@name="project_id"]/option[@selected]/@value)'));
            $this->assertSame('2026-10-01', $xpath->evaluate('string(//input[@name="start_date"]/@value)'));
            $this->assertSame('2026-10-21', $xpath->evaluate('string(//input[@name="end_date"]/@value)'));
            $this->assertSame('Original note <script>example</script>', $xpath->evaluate('string(//textarea[@name="comment"])'));
            $this->assertStringNotContainsString('<script>example</script>', $html);
            $this->assertStringContainsString('INV-26103', $html);
            $this->assertStringContainsString(route($prefix.'.invoices.invoicehistory'), $html);
        }
    }

    public function test_all_page_labels_render_in_the_four_locales(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $html = $this->renderPage('Admin', $locale);
            $this->assertStringNotContainsString('messages.period_edit_', $html);
            $this->assertStringContainsString(__('messages.period_edit_summary'), $html);
            $this->assertStringContainsString(__('messages.period_edit_recalculation'), $html);
        }
    }

    public function test_failed_validation_restores_user_input_and_associates_field_errors(): void
    {
        session()->flashInput(['project_id' => 8, 'start_date' => '2026-10-05', 'end_date' => '2026-10-04', 'comment' => 'Edited note']);
        $html = $this->renderPage('Admin', 'fr', ['end_date' => ['Choose an end date after the start date.']]);
        $xpath = $this->xpath($html);
        $this->assertSame('8', $xpath->evaluate('string(//select[@name="project_id"]/option[@selected]/@value)'));
        $this->assertSame('2026-10-05', $xpath->evaluate('string(//input[@name="start_date"]/@value)'));
        $this->assertSame('Edited note', $xpath->evaluate('string(//textarea[@name="comment"])'));
        $this->assertSame('true', $xpath->evaluate('string(//input[@id="end_date"]/@aria-invalid)'));
        $this->assertSame('end_date-error', $xpath->evaluate('string(//input[@id="end_date"]/@aria-describedby)'));
        $this->assertStringContainsString('Choose an end date after the start date.', $html);
    }
}
