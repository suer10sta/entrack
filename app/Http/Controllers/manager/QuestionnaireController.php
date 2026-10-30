<?php

namespace App\Http\Controllers\manager;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Http\Controllers\Controller;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Services\Surveys\ManagerSurveyReader;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function __construct(private ManagerSurveyReader $reader) {}

    private function key(Request $request, string $type): string
    {
        return 'manager_surveys.'.$request->user('manager')->id.'.'.$type;
    }

    public function index(Request $request)
    {
        $filters = $this->reader->filters($request);
        $type = SurveyType::from($filters['type']);
        $key = $this->key($request, $type->value);
        $seen = $request->session()->get($key.'.seen');
        $new = SurveyResponse::where('survey_type', $type)->whereNotNull('completed_at');
        if ($seen) {
            $new->where('completed_at', '>', $seen);
        }
        $newCount = (clone $new)->count();
        $request->session()->put($key.'.offered', (clone $new)->max('completed_at'));
        $query = $this->reader->query($filters, $seen);
        $sent = (clone $query)->whereNotNull('sent_at')->count();
        $answered = (clone $query)->whereNotNull('completed_at')->count();
        $rate = $sent ? round((clone $query)->whereNotNull('sent_at')->whereNotNull('completed_at')->count() * 100 / $sent, 1) : 0;
        $contacts = (clone $query)->whereHas('actions', fn ($q) => $q->where('action_type', 'TICKET'))->count();
        $subscriptionsAfterSend = null;
        if ($type === SurveyType::D4) {
            $subscriptionsAfterSend = (clone $query)->whereNotNull('sent_at')
                ->whereHas('entreprise', fn ($company) => $company
                    ->whereNotNull('first_paid_at')->whereNotNull('first_paid_invoice_id')
                    ->whereColumn('entreprises.first_paid_at', '>', 'survey_responses.sent_at')
                    ->where('first_paid_at', '<=', now('UTC')))
                ->distinct()->count('entreprise_id');
        }
        $definition = $this->reader->definition($type);
        $summary = collect();
        foreach ($definition['questions'] as $question) {
            if ($question['type'] !== 'single_choice') {
                continue;
            }
            $eligible = clone $query;
            $parent = $question;
            while ($condition = $parent['condition'] ?? null) {
                $eligible->whereHas('answers', function ($q) use ($condition) {
                    $q->where('question_id', $condition['question_id']);
                    if ($condition['operator'] === 'in') {
                        $q->whereIn('value', $condition['values']);
                    } else {
                        $q->whereNotIn('value', $condition['values']);
                    }
                });
                $parent = collect($definition['questions'])->firstWhere('id', $condition['question_id']);
            }
            $counts = SurveyAnswer::whereIn('survey_id', $eligible->select('survey_responses.id'))->where('question_id', $question['id'])
                ->whereIn('value', array_column($question['choices'], 'value'))->selectRaw('value, COUNT(*) AS total')->groupBy('value')->orderByDesc('total')->get();
            if ($counts->isNotEmpty()) {
                $summary->put($question['id'], $counts);
            }
        }
        $surveys = $this->reader->load($query)->orderByDesc('created_at')->orderByDesc('id')->paginate($filters['per_page'] ?? 25)->withQueryString();

        return view('manager.questionnaires.index', compact('type', 'definition', 'filters', 'newCount', 'sent', 'answered', 'rate', 'contacts', 'subscriptionsAfterSend', 'summary', 'surveys') + ['reader' => $this->reader, 'types' => SurveyType::cases(), 'statuses' => SurveyStatus::cases()]);
    }

    public function seen(Request $request)
    {
        $request->validate(['type' => ['required', \Illuminate\Validation\Rule::enum(SurveyType::class)]]);
        $key = $this->key($request, $request->input('type'));
        $offered = $request->session()->get($key.'.offered');
        if ($offered && $offered > $request->session()->get($key.'.seen', '')) {
            $request->session()->put($key.'.seen', $offered);
        }

        return redirect()->route('manager.questionnaires.index', ['type' => $request->input('type')]);
    }

    public function show(string $survey)
    {
        $survey = $this->reader->load(SurveyResponse::whereKey($survey))->firstOrFail();
        $events = $survey->actions()->orderByDesc('triggered_at')->orderByDesc('id')->paginate(25);

        $emails = [];
        if ($survey->sent_at) {
            $emails[] = $this->reader->emailPreview($survey) + ['sent_at' => $survey->sent_at, 'reminder' => false];
        }
        if ($survey->reminder_sent_at) {
            $emails[] = $this->reader->emailPreview($survey, true) + ['sent_at' => $survey->reminder_sent_at, 'reminder' => true];
        }

        return view('manager.questionnaires.show', ['survey' => $survey, 'events' => $events, 'emails' => $emails, 'responses' => $this->reader->responses($survey), 'reader' => $this->reader]);
    }

    public function export(Request $request)
    {
        $filters = $this->reader->filters($request);
        $query = $this->reader->query($filters, $request->session()->get($this->key($request, $filters['type']).'.seen'));
        $reader = $this->reader;

        return response()->streamDownload(function () use ($query, $reader) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $cell = fn ($value) => preg_match('/^[\s]*[=+@-]/u', (string) $value) ? "'".$value : (string) $value;
            fputcsv($out, ['Questionnaire', 'Entreprise', 'Email', 'Envoi', 'Réponse', 'Statut', 'Contact', 'Rappel prévu', 'Questions / réponses'], ';', '"', '');
            foreach ($reader->load($query)->reorder()->lazyById(200, 'survey_responses.id', 'id') as $survey) {
                $responses = collect($reader->responses($survey))->map(fn ($answer) => $answer['label'].' : '.($answer['value'] ?? '—'))->implode("\n");
                fputcsv($out, array_map($cell, [$reader->name($survey->survey_type), $survey->entreprise?->name, $survey->email, $survey->sent_at?->toIso8601String(), $survey->completed_at?->toIso8601String(), __('manager-surveys.statuses.'.$survey->status->value), $survey->contact?->ticket_num, $survey->recall_due_at?->toIso8601String(), $responses]), ';', '"', '');
            }
            fclose($out);
        }, 'questionnaires-'.$filters['type'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
