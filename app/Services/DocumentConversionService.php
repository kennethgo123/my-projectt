<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class DocumentConversionService
{
    /**
     * Convert a document to PDF format
     *
     * @param string $sourcePath Full path to source file
     * @param string $outputDir Directory where the PDF should be saved
     * @return string|null Path to the converted PDF file or null if conversion failed
     */
    public function convertToPdf(string $sourcePath, string $outputDir): ?string
    {
        try {
            // Create output directory if it doesn't exist
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Prepare the command
            $command = [
                'soffice',
                '--headless',
                '--convert-to',
                'pdf',
                '--outdir',
                $outputDir,
                $sourcePath
            ];

            // Execute the conversion
            $process = new Process($command);
            $process->setTimeout(60); // 1 minute timeout
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('Document conversion failed', [
                    'error' => $process->getErrorOutput(),
                    'source' => $sourcePath
                ]);
                return null;
            }

            // Get the output PDF filename
            $sourceFilename = pathinfo($sourcePath, PATHINFO_FILENAME);
            $pdfPath = $outputDir . '/' . $sourceFilename . '.pdf';

            if (!file_exists($pdfPath)) {
                Log::error('PDF file not found after conversion', [
                    'source' => $sourcePath,
                    'expected_pdf' => $pdfPath
                ]);
                return null;
            }

            return $pdfPath;
        } catch (\Exception $e) {
            Log::error('Error converting document to PDF', [
                'error' => $e->getMessage(),
                'source' => $sourcePath
            ]);
            return null;
        }
    }

    /**
     * Convert a contract file to PDF and store it in the contracts directory
     *
     * @param string $contractPath Path relative to storage/app/public
     * @return string|null New contract path (relative to storage/app/public) or null if conversion failed
     */
    public function convertContractToPdf(string $contractPath): ?string
    {
        try {
            // Get full paths
            $storagePath = Storage::disk('public')->path('');
            $fullSourcePath = $storagePath . $contractPath;
            $tempDir = storage_path('app/temp/conversions');

            // Only convert if not already a PDF
            if (strtolower(pathinfo($contractPath, PATHINFO_EXTENSION)) === 'pdf') {
                return $contractPath;
            }

            // Convert the file
            $pdfPath = $this->convertToPdf($fullSourcePath, $tempDir);
            
            if (!$pdfPath) {
                return null;
            }

            // Generate new filename for the PDF
            $newFilename = Str::random(40) . '.pdf';
            $newContractPath = 'contracts/' . $newFilename;

            // Move the converted PDF to the contracts directory
            Storage::disk('public')->put(
                $newContractPath,
                file_get_contents($pdfPath)
            );

            // Clean up temp file
            @unlink($pdfPath);
            
            // Delete the original file if it exists
            if (Storage::disk('public')->exists($contractPath)) {
                Storage::disk('public')->delete($contractPath);
            }

            return $newContractPath;
        } catch (\Exception $e) {
            Log::error('Error in contract conversion process', [
                'error' => $e->getMessage(),
                'contract_path' => $contractPath
            ]);
            return null;
        }
    }
} 