<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TaskStopTranslationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.api_key' => 'test-key']);
        Http::preventStrayRequests();
        $user = new User;
        $user->forceFill(['id' => 123456, 'locale' => 'fr']);
        $user->setRelation('entreprise', null);
        $user->setRelation('roles', collect());
        $this->actingAs($user);
    }

    public function test_confirmation_preserves_input_language_unless_translation_was_requested(): void
    {
        foreach ([[], ['translate' => false], ['translate' => '0']] as $options) {
            Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'Worked on the plans.']]]])]);
            $this->postJson('/tasks/prepare-stop-comment', $options + ['comment' => 'Worked on plans.', 'language' => 'fr'])
                ->assertOk()->assertExactJson(['corrected' => 'Worked on the plans.']);
            Http::assertSent(fn ($request) => str_contains($request['messages'][0]['content'], 'Keep the same language as the input.')
                && str_contains($request['messages'][0]['content'], 'Do not translate.')
                && $request['messages'][1]['content'] === 'Worked on plans.');
            Http::assertSentCount(1);
        }
    }

    public function test_translation_uses_the_selected_language_instead_of_dashboard_language(): void
    {
        foreach (['fr' => 'French', 'en' => 'English', 'nl' => 'Dutch', 'es' => 'Spanish'] as $locale => $language) {
            Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'Translated description']]]])]);
            $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Worked on plans.', 'translate' => true, 'language' => $locale])
                ->assertOk()->assertExactJson(['corrected' => 'Translated description']);
            Http::assertSent(fn ($request) => str_contains($request['messages'][0]['content'], "Translate the task description into {$language}"));
        }
    }

    public function test_translation_requires_one_of_the_four_supported_languages(): void
    {
        foreach ([null, 'de', 'French', ''] as $language) {
            Http::fake();
            $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Worked on plans.', 'translate' => true, 'language' => $language])
                ->assertUnprocessable();
            Http::assertNothingSent();
        }
    }

    public function test_invalid_translate_flag_is_rejected_without_calling_provider(): void
    {
        Http::fake();
        $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Worked on plans.', 'translate' => 'unexpected'])
            ->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_stop_comment_preserves_whitespace_without_changing_other_input_trimming(): void
    {
        $original = "  Worked on plans.\nSecond line.  ";
        $request = \Illuminate\Http\Request::create('/tasks/7/stop', 'POST', ['stop_comment' => $original, 'other' => ' trim me ']);
        (new \App\Http\Middleware\TrimStrings)->handle($request, fn () => response()->noContent());
        $this->assertSame($original, $request->input('stop_comment'));
        $this->assertSame('trim me', $request->input('other'));
    }
}
