<?php

namespace App\Console\Commands;

use App\Models\LegalCase;
use App\Services\DocumentConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConvertContractsToPdf extends Command
{
    protected $signature = 'contracts:convert-to-pdf {--force : Force conversion even for PDF files}';
    protected $description = 'Convert all contract documents to PDF format';

    private DocumentConversionService $conversionService;

    public function __construct(DocumentConversionService $conversionService)
    {
        parent::__construct();
        $this->conversionService = $conversionService;
    }

    public function handle()
    {
        $this->info('Starting contract conversion process...');

        $cases = LegalCase::whereNotNull('contract_path')->get();
        $total = $cases->count();
        $converted = 0;
        $failed = 0;

        $this->output->progressStart($total);

        foreach ($cases as $case) {
            try {
                // Skip if already PDF and not forcing
                $extension = strtolower(pathinfo($case->contract_path, PATHINFO_EXTENSION));
                if ($extension === 'pdf' && !$this->option('force')) {
                    $this->output->progressAdvance();
                    continue;
                }

                DB::beginTransaction();

                $newPath = $this->conversionService->convertContractToPdf($case->contract_path);

                if ($newPath) {
                    $case->contract_path = $newPath;
                    $case->save();
                    DB::commit();
                    $converted++;
                } else {
                    DB::rollBack();
                    $failed++;
                    Log::error('Failed to convert contract', [
                        'case_id' => $case->id,
                        'contract_path' => $case->contract_path
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                Log::error('Error during contract conversion', [
                    'case_id' => $case->id,
                    'error' => $e->getMessage()
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("\nConversion completed:");
        $this->info("Total contracts processed: $total");
        $this->info("Successfully converted: $converted");
        $this->info("Failed conversions: $failed");

        if ($failed > 0) {
            $this->warn('Some conversions failed. Check the logs for details.');
            return 1;
        }

        return 0;
    }
} 