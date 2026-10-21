<?php

namespace Tests\Feature;

use App\Http\Controllers\admin\entrepriseController;
use App\Http\Middleware\CheckSubscription;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscriptionCountryRecoveryTest extends TestCase
{
    private function loginAsCompany(bool $expired = true, string $role = 'Admin'): void
    {
        $company = \Mockery::mock(Entreprise::class)->makePartial();
        $company->forceFill(['id' => 17, 'trial_ends_at' => null]);
        foreach (['isOnTrial' => false, 'isTrialExpired' => $expired, 'isBlockedByFailedPayments' => false,
            'hasCanceledSubscription' => false, 'isBlockingExempt' => false] as $method => $result) {
            $company->shouldReceive($method)->andReturn($result);
        }
        $user = \Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 1, 'entreprise_id' => 17]);
        $user->setRelation('entreprise', $company);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($candidate) => $candidate === $role);
        $user->shouldReceive('hasAnyRole')->andReturnUsing(fn ($roles) => in_array($role, $roles, true));
        $this->actingAs($user);
    }

    private function requestFor(string $name, string $method = 'GET', array $data = []): Request
    {
        $request = Request::create('/recovery-test', $method, $data);
        $route = new Route([$method], '/recovery-test', fn () => null);
        $route->name($name);
        $request->setRouteResolver(fn () => $route);
        return $request;
    }

    public function test_expired_company_can_save_country_from_upgrade_for_all_billing_roles(): void
    {
        foreach (['Admin' => 'admin', 'SuperAdmin' => 'SuperAdmin', 'Responsable de projet' => 'Responsable_de_projet',
            'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire'] as $role => $prefix) {
            $this->loginAsCompany(true, $role);
            $request = $this->requestFor($prefix.'.Enterprise_infos.updateInfos', 'POST', ['id' => 17, 'column' => 'country', 'content' => 'Belgique']);
            $response = app(CheckSubscription::class)->handle($request, fn () => response('allowed'));
            $this->assertSame('allowed', $response->getContent());
            $this->assertTrue($request->attributes->get('subscriptionCountryRecovery'));
            $response = app(CheckSubscription::class)->handle($this->requestFor($prefix.'.Enterprise_infos.index'), fn () => response('unexpected'));
            $this->assertSame(route('profile.upgrade'), $response->getTargetUrl());
        }
    }

    public function test_recovery_does_not_unlock_other_fields_tenants_or_application_routes(): void
    {
        $this->loginAsCompany();
        foreach ([['admin.Enterprise_infos.updateInfos', 'POST', ['id' => 17, 'column' => 'billit_api_key']],
            ['admin.Enterprise_infos.updateInfos', 'POST', ['id' => 18, 'column' => 'country']],
            ['admin.Enterprise_infos.index', 'GET', []], ['admin.Enterprise_infos.destroy', 'DELETE', []], ['admin.Enterprise_infos.edit', 'GET', []],
            ['admin.Enterprise_SMTPinfos.updateSMTPInfos', 'POST', []], ['admin.index', 'GET', []]] as [$name, $method, $data]) {
            $response = app(CheckSubscription::class)->handle($this->requestFor($name, $method, $data), fn () => response('unexpected'));
            $this->assertSame(route('profile.upgrade'), $response->getTargetUrl());
        }
    }

    public function test_employee_cannot_use_recovery_and_checkout_remains_accessible(): void
    {
        $this->loginAsCompany(true, 'Employee');
        $response = app(CheckSubscription::class)->handle($this->requestFor('employee.Enterprise_infos.index'), fn () => response('unexpected'));
        $this->assertSame(route('account.blocked'), $response->getTargetUrl());
        $this->loginAsCompany();
        $response = app(CheckSubscription::class)->handle($this->requestFor('subscription.choose'), fn () => response('checkout'));
        $this->assertSame('checkout', $response->getContent());
        $this->loginAsCompany(false);
        $request = $this->requestFor('admin.Enterprise_infos.index');
        app(CheckSubscription::class)->handle($request, fn () => response('allowed'));
        $this->assertNull($request->attributes->get('subscriptionCountryRecovery'));
    }

    public function test_country_update_persists_only_on_authenticated_company(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('entreprises', function ($table) {
            $table->id(); $table->string('country')->nullable(); $table->timestamps();
        });
        DB::table('entreprises')->insert([['id' => 17, 'country' => null], ['id' => 18, 'country' => 'France']]);
        $this->loginAsCompany();
        $request = $this->requestFor('admin.Enterprise_infos.updateInfos', 'POST', ['id' => 17, 'column' => 'country', 'content' => 'Belgique']);
        $response = app(CheckSubscription::class)->handle($request, fn ($request) => app(entrepriseController::class)->updateInfos($request));
        $this->assertTrue(isset($response->getData(true)['success']));
        $this->assertSame('Belgique', DB::table('entreprises')->where('id', 17)->value('country'));
        $this->assertSame('France', DB::table('entreprises')->where('id', 18)->value('country'));
    }

    public function test_recovery_rejects_invalid_country(): void
    {
        $this->loginAsCompany();
        $request = $this->requestFor('admin.Enterprise_infos.updateInfos', 'POST', ['id' => 17, 'column' => 'country', 'content' => 'invalid-country']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(CheckSubscription::class)->handle($request, fn ($request) => app(entrepriseController::class)->updateInfos($request));
    }

}
