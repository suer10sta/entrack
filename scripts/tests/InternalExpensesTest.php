<?php

namespace Tests\Feature;

use App\Http\Controllers\admin\entrepriseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InternalExpensesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        Schema::create('entreprises', function ($table) { $table->id(); });
        Schema::create('smtp_config', function ($table) { $table->id(); $table->integer('entreprise_id'); });
        Schema::create('billings_budget', function ($table) {
            $table->id(); $table->integer('entreprise_id'); $table->integer('sold_out')->default(0);
            $table->double('budget'); $table->double('budget_remaining'); $table->timestamps();
        });
        Schema::create('entreprise_charges', function ($table) {
            $table->id(); $table->integer('entreprise_id'); $table->integer('Billings_budget_id');
            $table->string('charge_name'); $table->double('price'); $table->integer('validated')->default(0); $table->timestamps();
        });
        DB::table('entreprises')->insert(['id' => 9]);
        DB::table('billings_budget')->insert([
            ['id' => 1, 'entreprise_id' => 9, 'sold_out' => 1, 'budget' => 100, 'budget_remaining' => 20, 'created_at' => '2026-01-01'],
            ['id' => 2, 'entreprise_id' => 9, 'sold_out' => 0, 'budget' => 200, 'budget_remaining' => 200, 'created_at' => '2026-02-01'],
            ['id' => 3, 'entreprise_id' => 10, 'sold_out' => 0, 'budget' => 300, 'budget_remaining' => 300, 'created_at' => '2026-03-01'],
        ]);
        $user = new User;
        $user->forceFill(['id' => 1, 'entreprise_id' => 9]);
        $this->actingAs($user);
    }

    private function store(array $data)
    {
        return app(entrepriseController::class)->store(Request::create('/expenses', 'POST', $data + ['name' => ['Transport'], 'price' => ['12.50']]));
    }

    public function test_expense_is_attached_to_selected_budget_without_changing_its_balance(): void
    {
        $before = DB::table('billings_budget')->where('id', 1)->first();
        $response = $this->store(['budget_id' => 1]);
        $this->assertSame(302, $response->getStatusCode());
        $charge = DB::table('entreprise_charges')->first();
        $this->assertEquals(1, $charge->Billings_budget_id);
        $this->assertEquals(9, $charge->entreprise_id);
        $this->assertEquals(0, $charge->validated);
        $this->assertEquals($before, DB::table('billings_budget')->where('id', 1)->first());
    }

    public function test_requests_without_selected_budget_keep_the_existing_default(): void
    {
        $this->store([]);
        $this->assertEquals(2, DB::table('entreprise_charges')->value('Billings_budget_id'));
    }

    public function test_selected_budget_must_belong_to_the_current_enterprise(): void
    {
        try {
            $this->store(['budget_id' => 3]);
            $this->fail('Foreign budget accepted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertSame(0, DB::table('entreprise_charges')->count());
        }
    }

    public function test_multiple_lines_still_use_the_selected_budget(): void
    {
        $this->store(['budget_id' => 1, 'name' => ['Transport', 'Repas'], 'price' => ['12.50', '20']]);
        $this->assertSame(2, DB::table('entreprise_charges')->where('Billings_budget_id', 1)->count());
        $this->assertEquals(32.5, DB::table('entreprise_charges')->sum('price'));
    }
}
