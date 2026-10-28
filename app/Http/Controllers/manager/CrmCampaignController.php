<?php

namespace App\Http\Controllers\manager;

use App\Enums\ProspectStatus;
use App\Http\Controllers\Controller;
use App\Models\CrmCampaign;
use App\Models\Prospect;
use App\Services\CrmCampaignAssets;
use App\Services\CrmCampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CrmCampaignController extends Controller
{
    public function index()
    {
        $campaigns = CrmCampaign::withCount(['recipients as opened_count' => fn ($q) => $q->where('status', 'sent')->whereNotNull('first_opened_at'),
            'recipients as clicked_count' => fn ($q) => $q->where('status', 'sent')->whereNotNull('first_clicked_at'), 'recipients', 'recipients as sent_count' => fn ($q) => $q->where('status', 'sent'),
            'recipients as failed_count' => fn ($q) => $q->where('status', 'failed'),
            'recipients as pending_count' => fn ($q) => $q->whereIn('status', ['selected', 'pending', 'sending']),
            'recipients as uncertain_count' => fn ($q) => $q->where('status', 'uncertain'),
            'recipients as skipped_count' => fn ($q) => $q->where('status', 'skipped')])->latest('id')->paginate(20);

        $totals = \App\Models\CrmCampaignRecipient::where('status', 'sent')->whereNotNull('sent_at')
            ->whereHas('campaign', fn ($query) => $query->where('status', '!=', 'draft'))
            ->selectRaw('COUNT(*) AS sent, COUNT(first_opened_at) AS opened, COUNT(first_clicked_at) AS clicked')->first();
        $sent = (int) $totals->sent;
        $globalRates = [
            'Taux d’ouverture' => number_format($sent ? (int) $totals->opened / $sent * 100 : 0, 1, ',', ' ').' %',
            'Taux de clic' => number_format($sent ? (int) $totals->clicked / $sent * 100 : 0, 1, ',', ' ').' %',
        ];

        return view('manager.campaigns.index', compact('campaigns', 'globalRates'));
    }

    public function create()
    {
        return view('manager.campaigns.wizard', ['campaign' => null, 'step' => 1, 'selected' => collect()]);
    }

    public function store(Request $request)
    {
        $data = $request->boolean('_wizard') ? $this->information($request) + ['content' => ''] : $this->content($request);
        $campaign = CrmCampaign::create($data + ['manager_id' => $request->user('manager')->id]);

        return redirect()->route('manager.campaigns.show', ['campaign' => $campaign] + ($request->boolean('_wizard') ? ['step' => 2] : []))
            ->with('status', 'Brouillon créé. Sélectionnez maintenant vos destinataires.');
    }

    public function setup(Request $request, CrmCampaign $campaign)
    {
        $request->validate(['step' => ['required', Rule::in([1, 3])], 'revision' => ['required', 'integer', 'min:0']]);
        $step = (int) $request->input('step');
        $back = $step === 3 && $request->boolean('back_to_audience');
        $data = $step === 1 ? $this->information($request) : $this->composition($request, $back);
        app(CrmCampaignAssets::class)->transaction(function ($assets) use ($campaign, $request, $data, $step) {
            $locked = CrmCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'draft', 409);
            $this->checkRevision($locked, $request);
            $locked->update(($step === 3 ? $assets->apply($request, $locked, $data) : $data) + ['revision' => $locked->revision + 1]);
        });

        return redirect()->route('manager.campaigns.show', ['campaign' => $campaign, 'step' => $step === 1 || $back ? 2 : 3])->with('status', 'Brouillon enregistré.');
    }

    public function preview(CrmCampaign $campaign)
    {
        $mail = new \App\Mail\ProspectCampaignMail($campaign->subject, $campaign->content, $campaign->preheader, $campaign->cta_text, $campaign->ctaLink(), $campaign->media['image'] ?? null, $campaign->media['attachment'] ?? null);

        return response($mail->render())->header('Cache-Control', 'no-store, private')
            ->header('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src data:; base-uri 'none'; form-action 'none'; frame-ancestors 'self'");
    }

    public function show(Request $request, CrmCampaign $campaign)
    {
        $filters = $this->filters($request);
        $query = Prospect::query();
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['source']) && $filters['source'] !== '') {
            $query->where('source', $filters['source']);
        }
        $prospects = $query->orderBy('name')->orderBy('id')->paginate(25, ['*'], 'prospects_page')->withQueryString();
        $sources = Prospect::whereNotNull('source')->distinct()->orderBy('source')->pluck('source');
        $selected = $campaign->recipients()->pluck('email_normalized', 'prospect_id');
        $counts = $campaign->recipients()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $campaign->loadCount(['recipients as sent_count' => fn ($q) => $q->where('status', 'sent'),
            'recipients as opened_count' => fn ($q) => $q->where('status', 'sent')->whereNotNull('first_opened_at'),
            'recipients as clicked_count' => fn ($q) => $q->where('status', 'sent')->whereNotNull('first_clicked_at')]);
        $results = $campaign->recipients()->with(['links' => fn ($q) => $q->where('click_count', '>', 0)->orderBy('first_clicked_at')])->orderBy('id')->paginate(25, ['*'], 'results_page')->withQueryString();

        $request->validate(['step' => ['nullable', 'integer', Rule::in([1, 2, 3])]]);
        $step = (int) $request->query('step', 2);

        return view($campaign->status === 'draft' ? 'manager.campaigns.wizard' : 'manager.campaigns.show', compact('campaign', 'step', 'filters', 'prospects', 'sources', 'selected', 'counts', 'results'));
    }

    public function update(Request $request, CrmCampaign $campaign)
    {
        $data = $this->content($request);
        DB::transaction(function () use ($campaign, $data) {
            $locked = CrmCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'draft', 409);
            $locked->update($data + ['revision' => $locked->revision + 1]);
        });

        return redirect()->route('manager.campaigns.show', $campaign)->with('status', 'Campagne enregistrée.');
    }

    public function recipients(Request $request, CrmCampaign $campaign, CrmCampaignService $service)
    {
        $data = $request->validate(['mode' => ['required', Rule::in(['page', 'all', 'clear'])],
            'visible_ids' => ['sometimes', 'array', 'max:25'], 'visible_ids.*' => ['integer', 'distinct'],
            'selected_ids' => ['sometimes', 'array', 'max:25'], 'selected_ids.*' => ['integer', 'distinct']]) + $this->filters($request);
        $result = $service->select($campaign, $data);

        return redirect()->route('manager.campaigns.show', ['campaign' => $campaign] + ($request->boolean('_wizard') ? ['step' => in_array((string) $request->input('wizard_next'), ['1', '3'], true) ? (int) $request->input('wizard_next') : 2] : []) + array_filter(['filter_status' => $data['status'], 'source' => $data['source']], fn ($v) => $v !== null && $v !== ''))
            ->with('status', 'Sélection enregistrée. '.$result['added'].' ajout(s), '.$result['ignored'].' adresse(s) invalide(s) ou en double ignorée(s).');
    }

    public function send(Request $request, CrmCampaign $campaign, CrmCampaignService $service)
    {
        $request->validate(['confirm' => ['accepted'], 'revision' => ['required', 'integer', 'min:0']]);
        if ($request->boolean('wizard_final')) {
            $data = $this->composition($request);
            app(CrmCampaignAssets::class)->transaction(function ($assets) use ($campaign, $request, $data, $service) {
                $locked = CrmCampaign::whereKey($campaign->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'draft') {
                    return;
                }
                $this->checkRevision($locked, $request);
                $locked->update($assets->apply($request, $locked, $data) + ['revision' => $locked->revision + 1]);
                $service->enqueue($locked, (int) $locked->revision);
            });
        } else {
            $service->enqueue($campaign, (int) $request->input('revision'));
        }

        return redirect()->route('manager.campaigns.show', $campaign)->with('status', 'Campagne mise en attente. L’envoi sera effectué par lots en arrière-plan.');
    }

    private function checkRevision(CrmCampaign $campaign, Request $request): void
    {
        if ((int) $campaign->revision !== (int) $request->input('revision')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['campaign' => 'Ce brouillon a changé. Rechargez la page avant de continuer.']);
        }
    }

    private function information(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'preheader' => ['nullable', 'string', 'max:255'], 'utm_campaign' => ['nullable', 'string', 'max:255']]);
    }

    private function composition(Request $request, bool $draft = false): array
    {
        $data = $request->validate(['content' => [$draft ? 'nullable' : 'required', 'string', 'max:50000'],
            'image_file' => ['nullable', 'file', 'image', 'mimes:'.CrmCampaignAssets::IMAGES, 'max:2048'],
            'attachment_file' => ['nullable', 'file', 'mimes:'.CrmCampaignAssets::DOCUMENTS, 'max:10240'],
            'image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'attachment_url' => ['nullable', 'url:http,https', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'], 'remove_attachment' => ['sometimes', 'boolean'],
            'cta_text' => ['nullable', ...($draft ? [] : ['required_with:cta_url']), 'string', 'max:120'],
            'cta_url' => ['nullable', ...($draft ? [] : ['required_with:cta_text']), 'url:http,https', 'max:2048', function ($attribute, $value, $fail) {
                $parts = is_string($value) ? parse_url($value) : false;
                if (! $parts || isset($parts['user']) || isset($parts['pass'])) {
                    $fail('Utilisez une URL HTTP(S) sans identifiants.');
                }
            }]]);
        $data['content'] = $data['content'] ?? '';

        return $data;
    }

    private function content(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'], 'content' => ['required', 'string', 'max:50000']]);
    }

    private function filters(Request $request): array
    {
        // filter_status avoids colliding with campaign status/form feedback.
        $filters = $request->validate(['filter_status' => ['nullable', Rule::enum(ProspectStatus::class)], 'source' => ['nullable', 'string', 'max:255']]);

        return ['status' => $filters['filter_status'] ?? null, 'source' => $filters['source'] ?? null];
    }
}
