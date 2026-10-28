<form method="POST" enctype="multipart/form-data" action="{{ route('manager.prospects.import.store') }}" class="crm-upload-form space-y-4">
        @csrf <input type="hidden" name="_prospect_modal" value="import">
        <label for="import-file" class="block text-sm font-semibold text-[#0B2638]">Formats acceptés : XLSX, CSV<input id="import-file" type="file" name="file" required accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="crm-import-file mt-2 block w-full text-sm" aria-describedby="import-help"></label>
        <p id="import-help" class="text-xs text-slate-500">Première feuille pour XLSX · CSV séparé par virgules ou points-virgules · 1 000 lignes maximum · 5 Mo maximum. La première ligne doit contenir les en-têtes.</p>
        <div>
            <label for="import-source" class="mb-1.5 block text-sm font-semibold text-[#0B2638]">Source d’acquisition</label>
            <select id="import-source" name="import_source" aria-describedby="import-source-help" @if($errors->has('import_source')) aria-invalid="true" @endif class="block h-9 w-full rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                <option value="">Utiliser la source du fichier</option>
                @foreach(['Google', 'Google Ads', 'LinkedIn', 'Facebook', 'Instagram', 'Email', 'Campagne', 'Salon / Événement', 'Recommandation', 'Site web', 'Autre'] as $importSource)
                    <option value="{{ $importSource }}" @selected(old('import_source', '') === $importSource)>{{ $importSource }}</option>
                @endforeach
            </select>
            <p id="import-source-help" class="mt-1.5 text-xs text-slate-500">La source sélectionnée s’applique à tous les nouveaux prospects importés et remplace la colonne source du fichier. Sans sélection, les valeurs du fichier sont conservées.</p>
            @error('import_source')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
        </div>
        <div class="crm-modal-actions"><button type="button" class="crm-modal-cancel" data-close-prospect-modal>Annuler</button><button type="submit" class="crm-modal-submit">@include('manager.prospects.partials.icon', ['icon' => 'check']) Valider et importer</button></div>
    </form>
    <details class="crm-import-guide" @if(!$result) open @endif><summary class="cursor-pointer font-semibold text-[#0B2638]">Format attendu et règles d’import</summary><div class="mt-3 space-y-3 text-sm text-slate-600">
        <p><strong>Nom obligatoire :</strong> colonne <code>name</code> (nom complet), ou <code>first_name</code> et <code>last_name</code>.</p>
        <p>Colonnes facultatives : <code>email</code>, <code>phone</code>, <code>company_name</code> (ou <code>company</code>), <code>country</code>, <code>source</code>, <code>utm_source</code>, <code>utm_medium</code>, <code>utm_campaign</code>, <code>utm_content</code>, <code>utm_term</code>, <code>status</code>.</p>
        <p>Statuts : @foreach(\App\Enums\ProspectStatus::cases() as $status)<code>{{ $status->value }}</code>{{ $loop->last ? '.' : ', ' }}@endforeach Un statut absent ou non reconnu devient <strong>Nouveau</strong>.</p>
        <p>Les emails sont comparés sans distinction de casse ni espaces aux extrémités. Les doublons du fichier ou du CRM, même archivés, sont ignorés. Sans email, ce contrôle n’est pas possible.</p>
        <p>Enregistrez les téléphones comme texte dans Excel pour préserver les zéros et le signe +. Remplacez les formules par leurs valeurs avant l’envoi. Les colonnes non reconnues sont ignorées.</p>
        <p>L’import ajoute des contacts sans créer de compte DalyTrac ni modifier un compte existant.</p>
    </div></details>
    @if($result)
        <section class="space-y-4" aria-labelledby="import-result" role="status"><div><h2 id="import-result" class="text-lg font-semibold text-[#0B2638]">Résultat du dernier import</h2><p class="text-xs text-slate-500">{{ \Carbon\CarbonImmutable::parse($result['completed_at'])->utc()->format('d/m/Y H:i') }} UTC · {{ $result['total'] }} lignes traitées · rapport disponible 30 minutes.</p></div>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">@foreach(['created' => 'Créés', 'duplicated' => 'Doublons ignorés', 'invalid' => 'Invalides', 'skipped' => 'Lignes vides ignorées'] as $key => $label)<div class="dt-card p-4"><p class="text-xs text-slate-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold text-[#0B2638]">{{ $result[$key] }}</p></div>@endforeach</div>
            <div class="dt-card overflow-hidden"><div class="max-h-96 overflow-auto"><table class="dt-table w-full text-sm"><thead><tr><th>Ligne</th><th>Prospect</th><th>Résultat</th><th>Détails</th></tr></thead><tbody>@foreach($result['rows'] as $row)<tr><td>{{ $row['line'] }}</td><td class="break-words">{{ $row['name'] ?: '—' }}<p class="text-xs text-slate-500">{{ $row['email'] ?: 'Sans email' }}</p></td><td class="font-medium {{ $row['outcome'] === 'invalid' ? 'text-rose-700' : 'text-[#2D7080]' }}">{{ ['created' => 'Créé', 'duplicated' => 'Doublon', 'invalid' => 'Invalide', 'skipped' => 'Ignoré'][$row['outcome']] }}</td><td class="break-words">{{ $row['reason'] }}@foreach($row['messages'] as $message)<p class="mt-1 text-xs text-slate-500">{{ $message }}</p>@endforeach</td></tr>@endforeach</tbody></table></div></div>
        </section>
    @endif
