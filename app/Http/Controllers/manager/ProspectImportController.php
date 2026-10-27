<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Services\ProspectExcelImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProspectImportController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('manager.prospects.index', ['modal' => 'import']);
    }

    public function store(Request $request, ProspectExcelImport $importer)
    {
        $request->session()->forget('prospect_import_report');
        $request->session()->flash('prospect_modal', 'import');
        try {
            $request->validate(['file' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:5120']]);
            $result = $importer->import($request->file('file'), $request->user('manager'));
        } catch (ValidationException $error) {
            throw $error->redirectTo(route('manager.prospects.index'));
        } catch (Throwable $error) {
            Log::error('CRM Excel import failed; all prospect writes were rolled back.', ['manager_id' => $request->user('manager')->id, 'exception' => $error]);
            throw ValidationException::withMessages(['file' => 'L’import a échoué. Aucun prospect de ce fichier n’a été créé. Réessayez ou contactez le support.'])->redirectTo(route('manager.prospects.index'));
        }
        $id = (string) Str::uuid();
        Cache::put('crm:prospect-import:'.$request->user('manager')->id.':'.$id, $result, now()->addMinutes(30));
        $request->session()->put('prospect_import_report', $id);

        return redirect()->route('manager.prospects.index', ['modal' => 'import']);
    }
}
