<?php

namespace App\Http\Controllers\manager;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Services\ProspectStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::enum(ProspectStatus::class)],
            'source' => ['nullable', 'string', 'max:255'], 'sort' => ['nullable', Rule::in(['newest', 'oldest', 'activity'])],
            'per_page' => ['nullable', Rule::in([15, 25, 50, 100])],
        ]);
        $query = Prospect::query()->withMax('activities', 'occurred_at')->withMax('statusHistories', 'occurred_at');
        $search = $filters['q'] ?? null;
        if ($search !== null && $search !== '') {
            // Bind parameters and escape wildcard characters so a search remains literal.
            $pattern = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($search)).'%';
            $query->where(function ($query) use ($pattern) {
                foreach (['name', 'email', 'phone', 'company_name'] as $column) {
                    $query->orWhereRaw('LOWER('.$column.') LIKE ?', [$pattern]);
                }
            });
        }
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['source']) && $filters['source'] !== '') {
            $query->where('source', $filters['source']);
        }
        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'activity') {
            $query->orderByRaw('GREATEST(COALESCE((SELECT MAX(occurred_at) FROM prospect_activities WHERE prospect_id = prospects.id), prospects.created_at), COALESCE((SELECT MAX(occurred_at) FROM prospect_status_history WHERE prospect_id = prospects.id), prospects.created_at), prospects.created_at) DESC');
        } else {
            $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');
        }
        $prospects = $query->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')->paginate($filters['per_page'] ?? 15)->appends($request->except(['page', 'modal']));
        $counts = Prospect::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $sources = Prospect::query()->whereNotNull('source')->distinct()->orderBy('source')->pluck('source');

        $report = $request->session()->get('prospect_import_report');
        $result = $report ? Cache::get('crm:prospect-import:'.$request->user('manager')->id.':'.$report) : null;
        $activeModal = $request->session()->get('prospect_modal', $request->query('modal'));
        $activeModal = in_array($activeModal, ['create', 'import'], true) ? $activeModal : '';

        return view('manager.prospects.index', compact('prospects', 'counts', 'sources', 'filters', 'result', 'activeModal'));
    }

    public function create()
    {
        return redirect()->route('manager.prospects.index', ['modal' => 'create']);
    }

    public function store(Request $request, ProspectStatusService $service)
    {
        $fromModal = $request->input('_prospect_modal') === 'create';
        try {
            $data = $this->validated($request, true);
        } catch (ValidationException $error) {
            if ($fromModal) {
                $request->session()->flash('prospect_modal', 'create');
                $error->redirectTo(route('manager.prospects.index'));
            }
            throw $error;
        }
        $prospect = DB::transaction(function () use ($data, $request, $service) {
            $prospect = Prospect::create(Arr::except($data, ['status', 'initial_note']) + [
                'owner_manager_id' => $request->user('manager')->id, 'source_captured_at' => now('UTC'),
            ]);
            $service->transition($prospect, ProspectStatus::from($data['status'] ?? 'nouveau'), manager: $request->user('manager'));
            if ($data['initial_note'] ?? null) {
                $prospect->activities()->create(['type' => ProspectActivityType::Note, 'body' => $data['initial_note'],
                    'manager_id' => $request->user('manager')->id, 'occurred_at' => now('UTC')]);
            }

            return $prospect;
        });

        return redirect()->route('manager.prospects.show', $prospect)->with('status', 'Prospect créé.');
    }

    public function show(Prospect $prospect)
    {
        $prospect->load('ownerManager');
        // Paginate the combined timeline rather than loading an unbounded history.
        $activities = DB::table('prospect_activities')->where('prospect_id', $prospect->id)
            ->selectRaw("id, 'activity' AS kind, type, body, occurred_at, manager_id, NULL AS from_status, NULL AS to_status, due_at, subject, NULL AS source");
        $history = DB::table('prospect_status_history')->where('prospect_id', $prospect->id)
            ->selectRaw("id, 'status' AS kind, NULL AS type, note AS body, occurred_at, manager_id, from_status, to_status, NULL AS due_at, NULL AS subject, source");
        $timeline = DB::query()->fromSub($activities->unionAll($history), 'timeline')->orderByDesc('occurred_at')->orderBy('kind')->orderByDesc('id')->paginate(20)->withQueryString();
        $authors = \App\Models\Manager::whereIn('id', $timeline->pluck('manager_id')->filter())->pluck('name', 'id');

        return view('manager.prospects.show', compact('prospect', 'timeline', 'authors'));
    }

    public function edit(Prospect $prospect)
    {
        return redirect()->route('manager.prospects.show', ['prospect' => $prospect, 'modal' => 'edit']);
    }

    public function update(Request $request, Prospect $prospect)
    {
        try {
            $data = $this->validated($request, false);
        } catch (ValidationException $error) {
            if ($request->input('_prospect_modal') === 'edit') {
                $request->session()->flash('prospect_edit', true);
                $error->redirectTo(route('manager.prospects.show', $prospect));
            }
            throw $error;
        }
        $prospect->update($data);

        return redirect()->route('manager.prospects.show', $prospect)->with('status', 'Informations mises à jour.');
    }

    public function status(Request $request, Prospect $prospect, ProspectStatusService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::enum(ProspectStatus::class)], 'note' => ['nullable', 'string', 'max:5000']]);
        $service->transition($prospect, ProspectStatus::from($data['status']), manager: $request->user('manager'), note: $data['note'] ?? null);

        return redirect()->route('manager.prospects.show', $prospect)->with('status', 'Statut mis à jour.');
    }

    public function activity(Request $request, Prospect $prospect)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['note', 'manual_contact', 'call', 'email', 'demo', 'follow_up'])],
            'body' => ['required', 'string', 'max:10000'], 'subject' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'required_if:type,follow_up'],
        ]);
        DB::transaction(function () use ($prospect, $data, $request) {
            $locked = Prospect::whereKey($prospect->id)->lockForUpdate()->firstOrFail();
            $due = empty($data['due_at']) ? null : \Carbon\CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data['due_at'], 'UTC');
            $locked->activities()->create(Arr::except($data, ['due_at']) + ['due_at' => $due,
                'manager_id' => $request->user('manager')->id, 'occurred_at' => now('UTC')]);
            if ($data['type'] === 'follow_up') {
                $locked->update(['next_follow_up_at' => $locked->activities()->where('type', 'follow_up')->whereNull('completed_at')->min('due_at')]);
            }
        });

        return redirect()->route('manager.prospects.show', $prospect)->with('status', 'Activité enregistrée. Aucun email ni rappel automatique envoyé.');
    }

    public function archive(Prospect $prospect)
    {
        $prospect->delete();

        return redirect()->route('manager.prospects.index')->with('status', 'Prospect archivé. Son historique et ses liens sont conservés.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $rules = ['name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'], 'company_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100']];
        foreach (['source', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        if ($creating) {
            $rules['status'] = ['sometimes', 'required', Rule::enum(ProspectStatus::class)];
            $rules['initial_note'] = ['nullable', 'string', 'max:5000'];
        }

        return $request->validate($rules);
    }
}
