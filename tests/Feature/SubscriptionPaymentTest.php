<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    private ?string $paymentSchema = null;

    protected function beforeRefreshingDatabase()
    {
        $this->guardAgainstNonTestingDatabase();
        $this->paymentSchema = 'payment_test_'.bin2hex(random_bytes(8));
        \Illuminate\Support\Facades\DB::statement('CREATE SCHEMA "'.$this->paymentSchema.'"');
        config(['database.connections.pgsql.search_path' => $this->paymentSchema]);
        \Illuminate\Support\Facades\DB::purge('pgsql');
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
    }

    protected function afterRefreshingDatabase()
    {
        $schema = $this->paymentSchema;
        $this->beforeApplicationDestroyed(function () use ($schema) {
            // Registered after RefreshDatabase's rollback/disconnect callback.
            config(['database.connections.pgsql.search_path' => 'public']);
            \Illuminate\Support\Facades\DB::purge('pgsql');
            \Illuminate\Support\Facades\DB::statement('DROP SCHEMA "'.$schema.'" CASCADE');
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        });
    }

    private const PREMIUM_TEST_PRICE = 'price_1SVBpHB4LVww0Nzztgb3hUSH';

    private function adminUser(?int $entrepriseId = null): array
    {
        $entreprise = null;
        if ($entrepriseId !== -1) {
            $entreprise = Entreprise::create([
                'name' => 'Acme',
                'is_activated' => true,
                'image' => 'default.jpg',
                'country' => 'Belgium',
            ]);
        }

        $user = User::factory()->create([
            'entreprise_id' => $entreprise?->id ?? 999999,
        ]);

        Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['name' => 'Admin', 'guard_name' => 'web']
        );
        $user->assignRole('Admin');

        return [$user, $entreprise];
    }

    private function stripeSignatureHeader(string $payload): array
    {
        $timestamp = time();
        $hmac = hash_hmac('sha256', $timestamp.'.'.$payload, (string) config('cashier.webhook.secret'));

        return ['HTTP_Stripe-Signature' => "t={$timestamp},v1={$hmac}", 'CONTENT_TYPE' => 'application/json'];
    }

    private function invoicePayload(string $customer, ?string $priceId): array
    {
        $lines = $priceId
            ? ['data' => [['price' => ['id' => $priceId]]]]
            : ['data' => []];

        return [
            'id' => 'evt_test_123',
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => [
                'id' => 'in_test_123',
                'customer' => $customer,
                'amount_paid' => 4900,
                'currency' => 'eur',
                'lines' => $lines,
            ]],
        ];
    }

    public function test_choose_plan_rejects_invalid_plan(): void
    {
        [$user] = $this->adminUser();

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'gold']))
            ->assertRedirect(route('profile.upgrade'));
    }

    public function test_choose_plan_requires_entreprise(): void
    {
        // entreprise_id points to a missing row -> Auth::user()->entreprise is null.
        [$user] = $this->adminUser(-1);

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'solo']))
            ->assertRedirect(route('profile.upgrade'));
    }

    public function test_choose_plan_requires_country(): void
    {
        [$user, $entreprise] = $this->adminUser();
        $entreprise->forceFill(['country' => null])->save();

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'solo']))
            ->assertRedirect(route('profile.upgrade'));
    }

    public function test_choose_plan_blocks_active_subscriber(): void
    {
        [$user, $entreprise] = $this->adminUser();
        Subscription::forceCreate([
            'entreprise_id' => $entreprise->id,
            'type' => 'default',
            'stripe_id' => 'sub_test_active',
            'stripe_status' => 'active',
            'stripe_price' => self::PREMIUM_TEST_PRICE,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'pro']))
            ->assertRedirect(route('dashboard'));
    }

    public function test_choose_plan_fails_fast_without_configured_price(): void
    {
        // STRIPE_PRICE_SOLO is intentionally unset: clear error, no exception.
        [$user] = $this->adminUser();
        config()->set('services.stripe.prices.solo', null);

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'solo']))
            ->assertRedirect(route('profile.upgrade'));
    }

    public function test_choose_plan_saves_missing_country_from_query(): void
    {
        // The Upgrade page carries the typed country in plan links (?country=);
        // choosePlan persists it when the entreprise has none, then continues.
        // Solo has no configured price in testing, so the flow stops at the
        // fail-fast step (no Stripe call) — after the country was saved.
        [$user, $entreprise] = $this->adminUser();
        $entreprise->forceFill(['country' => null])->save();
        config()->set('services.stripe.prices.solo', null);

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'solo', 'country' => 'Belgium']))
            ->assertRedirect(route('profile.upgrade'));

        $this->assertSame('Belgium', $entreprise->fresh()->country);
    }

    public function test_choose_plan_keeps_stored_country(): void
    {
        // A stored country is never overwritten by the link query parameter.
        [$user, $entreprise] = $this->adminUser();
        config()->set('services.stripe.prices.solo', null);

        $this->actingAs($user)->get(route('subscription.choose', ['plan' => 'solo', 'country' => 'France']))
            ->assertRedirect(route('profile.upgrade'));

        $this->assertSame('Belgium', $entreprise->fresh()->country);
    }

    public function test_payment_success_validates_request(): void
    {
        [$user, $entreprise] = $this->adminUser();

        // Invalid plan.
        $this->actingAs($user)->get(route('subscription.success', ['plan' => 'gold']))
            ->assertRedirect(route('profile.upgrade'));

        // Missing checkout session.
        $this->actingAs($user)->get(route('subscription.success', ['plan' => 'solo']))
            ->assertRedirect(route('profile.upgrade'));

        // Already activated plan -> dashboard, no double processing.
        // NOTE: re-resolve the user: within one test the guard memoizes the
        // Auth user (with its stale entreprise relation), while real HTTP
        // requests always boot a fresh application.
        $entreprise->forceFill(['type_compt' => 'solo'])->save();
        $this->actingAs($user->fresh())->get(route('subscription.success', ['plan' => 'solo']))
            ->assertRedirect(route('dashboard'));
    }

    public function test_checkout_metadata_reads_stripe_object_values(): void
    {
        $controller = new \App\Http\Controllers\admin\SubscriptionController;
        $method = new \ReflectionMethod($controller, 'checkoutMetadataValue');

        // Exact shape returned by stripe-php: metadata is a StripeObject,
        // for which a plain (array) cast exposes SDK internals (always null).
        $session = \Stripe\Checkout\Session::constructFrom([
            'id' => 'cs_test_123',
            'payment_status' => 'paid',
            'metadata' => ['plan_name' => 'premium', 'entreprise_id' => '57'],
        ]);

        $this->assertSame('premium', $method->invoke($controller, $session, 'plan_name'));
        $this->assertSame('57', $method->invoke($controller, $session, 'entreprise_id'));

        // Documents the original bug mechanism: (array) cast loses the values.
        $cast = (array) $session->metadata;
        $this->assertNull($cast['plan_name'] ?? null);

        // Legitimate session passes the anti-fraud comparison.
        $metaPlan = $method->invoke($controller, $session, 'plan_name');
        $metaEntreprise = $method->invoke($controller, $session, 'entreprise_id');
        $this->assertTrue($metaPlan === 'premium' && (int) $metaEntreprise === 57);

        // Tampered plan is still detected.
        $this->assertFalse($metaPlan === 'solo' && (int) $metaEntreprise === 57);

        // Missing metadata -> nulls -> correctly rejected, no crash.
        $bare = \Stripe\Checkout\Session::constructFrom(['id' => 'cs_test_456']);
        $this->assertNull($method->invoke($controller, $bare, 'plan_name'));
        $this->assertNull($method->invoke($controller, $bare, 'entreprise_id'));

        // Defensive: plain-array metadata shape also works.
        $arraySession = (object) ['metadata' => ['plan_name' => 'pro']];
        $this->assertSame('pro', $method->invoke($controller, $arraySession, 'plan_name'));
        $this->assertNull($method->invoke($controller, $arraySession, 'missing_key'));
    }

    public function test_plan_for_stripe_price_mapping(): void
    {
        config()->set('services.stripe.prices', [
            'solo' => 'price_solo_x',
            'premium' => self::PREMIUM_TEST_PRICE,
            'pro' => null,
        ]);

        $this->assertSame('premium', Entreprise::planForStripePrice(self::PREMIUM_TEST_PRICE));
        $this->assertSame('solo', Entreprise::planForStripePrice('price_solo_x'));
        $this->assertNull(Entreprise::planForStripePrice('price_unknown'));
        $this->assertNull(Entreprise::planForStripePrice(null));
    }

    public function test_webhook_rejects_forged_signature(): void
    {
        $raw = json_encode($this->invoicePayload('cus_forged', null));

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => 't='.time().',v1=invalidsignature',
            'CONTENT_TYPE' => 'application/json',
        ], $raw)->assertStatus(403);
    }

    public function test_webhook_activates_plan_from_paid_invoice(): void
    {
        config()->set('services.stripe.prices.premium', self::PREMIUM_TEST_PRICE);

        [$user, $entreprise] = $this->adminUser();
        $entreprise->forceFill([
            'stripe_id' => 'cus_test_123',
            'type_compt' => null,
            'failed_payment_count' => 2,
            'account_status' => 'inactive',
        ])->save();

        // No 'subscription' key -> no Stripe API call; lines carry the price.
        $raw = json_encode($this->invoicePayload('cus_test_123', self::PREMIUM_TEST_PRICE));

        $this->call('POST', '/stripe/webhook', [], [], [], $this->stripeSignatureHeader($raw) + [], $raw)
            ->assertStatus(200);

        $entreprise->refresh();
        $this->assertSame('premium', $entreprise->type_compt);
        $this->assertSame('active', $entreprise->account_status);
        $this->assertSame(0, (int) $entreprise->failed_payment_count);
        $this->assertNull($entreprise->trial_ends_at);
    }

    public function test_webhook_unknown_customer_is_ignored_safely(): void
    {
        $raw = json_encode($this->invoicePayload('cus_unknown', null));

        $this->call('POST', '/stripe/webhook', [], [], [], $this->stripeSignatureHeader($raw), $raw)
            ->assertStatus(200);
    }

    public function test_webhook_unknown_price_keeps_existing_plan(): void
    {
        config()->set('services.stripe.prices.premium', self::PREMIUM_TEST_PRICE);

        [$user, $entreprise] = $this->adminUser();
        $entreprise->forceFill([
            'stripe_id' => 'cus_test_456',
            'type_compt' => 'solo',
        ])->save();

        $raw = json_encode($this->invoicePayload('cus_test_456', 'price_unknown_xyz'));

        $this->call('POST', '/stripe/webhook', [], [], [], $this->stripeSignatureHeader($raw), $raw)
            ->assertStatus(200);

        $this->assertSame('solo', $entreprise->refresh()->type_compt);
    }
}
