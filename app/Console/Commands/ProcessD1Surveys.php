<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Services\Surveys\D1ActionService;
use App\Services\Surveys\D1Lifecycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessD1Surveys extends Command
{
    protected $signature = 'surveys:process-d1';

    protected $description = 'Process D1 eligibility, delivery, cancellation, reminder and expiry.';

    public function handle(D1Lifecycle $lifecycle, D1ActionService $actions): int
    {
        $results = [];
        // Use explicit queries: Entreprise has no Questionnaire relations to alter.
        Entreprise::where(function ($companies) {
            $companies->whereIn('id', function ($query) {
                $query->select('entreprise_id')->from('users')->whereNotNull('email_verified_at')
                    ->where('email_verified_at', '<=', now('UTC')->subDay());
            })->orWhereIn('id', function ($query) {
                $query->select('entreprise_id')->from('survey_responses')->where('survey_type', \App\Enums\SurveyType::D1->value);
            });
        })->select('id')->chunkById(100, function ($companies) use ($lifecycle, $actions, &$results) {
            foreach ($companies as $company) {
                try {
                    $result = $lifecycle->process($company->id);
                    if ($actions->retryCompany($company->id) > 0) {
                        $result = 'error';
                    }
                } catch (Throwable $exception) {
                    Log::error('D1 processing failed.', ['company_id' => $company->id, 'exception_class' => $exception::class]);
                    $result = 'error';
                }
                $results[$result] = ($results[$result] ?? 0) + 1;
            }
        });
        $this->line(json_encode($results, JSON_THROW_ON_ERROR));

        return isset($results['error']) || isset($results['unconfirmed']) ? self::FAILURE : self::SUCCESS;
    }
}
