<?php

namespace Tests\Feature;

use App\Services\AnalyticsBusinessEvents;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalyticsBusinessEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'analytics.api_secret' => null]);
        (require base_path('database/migrations/2026_10_26_000001_create_analytics_business_events_tables.php'))->up();
        Http::preventStrayRequests();
    }

    private function context(): array
    {
        return ['granted' => true, 'client_id' => '123456.789012', 'session_id' => '1234567890', 'captured_at' => now()->timestamp];
    }

    public function test_denied_or_missing_consent_never_records_a_conversion(): void
    {
        $events = app(AnalyticsBusinessEvents::class);
        $events->registration(1, 1, []);
        $events->registration(2, 2, ['granted' => false, 'client_id' => '123.456']);
        $this->assertSame(0, DB::table('analytics_business_events')->count());
        Http::assertNothingSent();
    }

    public function test_registration_is_unique_and_contains_no_email_or_private_url(): void
    {
        $events = app(AnalyticsBusinessEvents::class);
        $events->registration(1, 1, $this->context());
        $events->registration(1, 1, $this->context());
        $this->assertSame(1, DB::table('analytics_business_events')->count());
        $this->assertEqualsCanonicalizing(['sign_up'], DB::table('analytics_business_events')->pluck('event_name')->all());
        $payload = $this->payload('sign_up');
        $this->assertSame('email', $payload['events'][0]['params']['method']);
        $this->assertArrayNotHasKey('page_location', $payload['events'][0]['params']);
        $this->assertStringNotContainsString('123456.789012', DB::table('analytics_business_events')->first()->payload);
    }

    private function payload(string $name): array
    {
        return json_decode(Crypt::decryptString(DB::table('analytics_business_events')->where('event_name', $name)->first()->payload), true);
    }

    public function test_only_first_paid_subscription_invoice_counts_and_webhook_replays_are_deduplicated(): void
    {
        $events = app(AnalyticsBusinessEvents::class);
        $events->rememberContext(1, $this->context());
        $invoice = ['id' => 'in_test', 'subscription' => 'sub_test', 'billing_reason' => 'subscription_create', 'paid' => true, 'amount_paid' => 4900, 'currency' => 'eur', 'lines' => ['data' => [['price' => ['recurring' => ['interval' => 'month']]]]]];
        $events->payment(1, $invoice, 'premium');
        $events->payment(1, $invoice, 'premium');
        $events->payment(1, array_replace($invoice, ['id' => 'in_renewal', 'billing_reason' => 'subscription_cycle']), 'premium');
        $events->payment(1, array_replace($invoice, ['id' => 'in_unpaid', 'paid' => false]), 'premium');
        $this->assertSame(1, DB::table('analytics_business_events')->count());
        $params = $this->payload('subscription_success')['events'][0]['params'];
        $this->assertSame('monthly', $params['billing']);
        $this->assertEquals(49, $params['value']);
        $this->assertSame('in_test', $params['transaction_id']);
    }

    public function test_revocation_blocks_later_webhook_and_erases_client_identifiers(): void
    {
        $events = app(AnalyticsBusinessEvents::class);
        $events->rememberContext(1, $this->context());
        $events->rememberContext(1, ['granted' => false, 'client_id' => null]);
        $events->payment(1, ['id' => 'in_test', 'subscription' => 'sub_test', 'billing_reason' => 'subscription_create', 'paid' => true, 'amount_paid' => 1900, 'currency' => 'eur'], 'solo');
        $this->assertSame(0, DB::table('analytics_business_events')->count());
    }

    public function test_consent_endpoint_rejects_stale_update_and_tracks_no_events_itself(): void
    {
        $this->postJson('/analytics/consent', ['granted' => true, 'client_id' => '123.456', 'session_id' => '123', 'revision' => 100])->assertNoContent();
        $this->postJson('/analytics/consent', ['granted' => false, 'revision' => 200])->assertNoContent();
        $this->postJson('/analytics/consent', ['granted' => true, 'client_id' => '123.456', 'revision' => 150])->assertNoContent();
        $this->assertFalse(session('analytics_context.granted'));
        $this->assertNull(session('analytics_context.client_id'));
        $this->assertSame(0, DB::table('analytics_business_events')->count());
    }

    public function test_delivery_is_claimed_once_even_when_business_action_repeats(): void
    {
        config(['analytics.api_secret' => 'test-only-secret']);
        Http::fake(['www.google-analytics.com/*' => Http::response('', 204)]);
        $events = app(AnalyticsBusinessEvents::class);
        $events->record('sign_up', 'user:1', $this->context(), ['method' => 'email']);
        $events->record('sign_up', 'user:1', $this->context(), ['method' => 'email']);
        $this->app->terminate();
        Http::assertSentCount(1);
        $this->assertSame('sent', DB::table('analytics_business_events')->first()->status);
    }

    public function test_removed_events_are_never_recorded_even_if_called_directly(): void
    {
        $events = app(AnalyticsBusinessEvents::class);
        foreach (['trial_start', 'begin_checkout'] as $name) {
            $events->record($name, 'obsolete:'.$name, $this->context(), []);
        }
        $this->assertSame(0, DB::table('analytics_business_events')->count());
        Http::assertNothingSent();
    }

    public function test_replayed_payment_sends_only_one_subscription_conversion(): void
    {
        config(['analytics.api_secret' => 'test-only-secret']);
        Http::fake(['www.google-analytics.com/*' => Http::response('', 204)]);
        $events = app(AnalyticsBusinessEvents::class);
        $events->rememberContext(1, $this->context());
        $invoice = ['id' => 'in_paid', 'subscription' => 'sub_unique', 'billing_reason' => 'subscription_create', 'paid' => true, 'amount_paid' => 4900, 'currency' => 'eur'];
        $events->payment(1, $invoice, 'premium');
        $events->payment(1, $invoice, 'premium');
        $events->payment(1, array_replace($invoice, ['id' => 'in_other']), 'premium');
        $this->app->terminate();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['events'][0]['name'] === 'subscription_success'
            && $request['events'][0]['params']['subscription_id'] === 'sub_unique');
    }
}
