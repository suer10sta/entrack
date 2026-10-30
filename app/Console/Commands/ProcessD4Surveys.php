<?php

namespace App\Console\Commands;

use App\Enums\SurveyType;
use App\Models\Entreprise;
use App\Services\Surveys\D4ActionService;
use App\Services\Surveys\D4Lifecycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessD4Surveys extends Command
{
    protected $signature = 'surveys:process-d4';

    protected $description = 'Process D4 end-of-trial surveys and retry their completed business actions.';

    public function handle(D4Lifecycle $lifecycle, D4ActionService $actions): int
    {
        $results = [];
        Entreprise::where(function ($companies) {
            $companies->whereNotNull('trial_ends_at')->orWhereIn('id', function ($surveys) {
                $surveys->select('entreprise_id')->from('survey_responses')->where('survey_type', SurveyType::D4->value);
            });
        })->select('id')->chunkById(100, function ($companies) use ($lifecycle, $actions, &$results) {
            foreach ($companies as $company) {
                try {
                    $result = $lifecycle->process($company->id);
                    if ($actions->retryCompany($company->id) > 0) {
                        $result = 'error';
                    }
                } catch (Throwable $exception) {
                    Log::error('D4 processing failed.', ['company_id' => $company->id, 'exception_class' => $exception::class]);
                    $result = 'error';
                }
                $results[$result] = ($results[$result] ?? 0) + 1;
            }
        });
        $this->line(json_encode($results, JSON_THROW_ON_ERROR));

        return isset($results['error']) || isset($results['unconfirmed']) ? self::FAILURE : self::SUCCESS;
    }
}
