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

    public function test_symbols_and_numbers_bypass_correction_even_without_an_api_key(): void
    {
        config(['services.openai.api_key' => null]);
        Http::fake();
        foreach (['-', '*', '..', '-- ** ..', '123', '🙂'] as $comment) {
            $this->postJson('/tasks/prepare-stop-comment', ['comment' => $comment])
                ->assertOk()->assertExactJson(['corrected' => $comment]);
        }
        Http::assertNothingSent();
    }

    public function test_correction_failures_return_the_original_comment(): void
    {
        foreach ([
            Http::response(['error' => ['message' => 'Unavailable']], 503),
            Http::response(['choices' => []]),
            Http::response(['choices' => [['message' => ['content' => '']]]]),
            Http::response(['choices' => [['message' => ['content' => str_repeat('x', 501)]]]]),
            Http::response(['choices' => [['message' => ['content' => ['invalid']]]]]),
        ] as $response) {
            Http::fake(['api.openai.com/*' => $response]);
            $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Original comment.'])
                ->assertOk()->assertExactJson(['corrected' => 'Original comment.']);
        }
    }

    public function test_connection_failure_returns_the_original_comment(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Timed out'));
        $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Original comment.'])
            ->assertOk()->assertExactJson(['corrected' => 'Original comment.']);
    }

    public function test_missing_api_key_does_not_block_correction_for_stop(): void
    {
        config(['services.openai.api_key' => null]);
        Http::fake();
        $this->postJson('/tasks/prepare-stop-comment', ['comment' => 'Original comment.'])
            ->assertOk()->assertExactJson(['corrected' => 'Original comment.']);
        Http::assertNothingSent();
    }
}
