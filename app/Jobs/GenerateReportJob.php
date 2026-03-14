<?php

namespace App\Jobs;

use App\Models\ReportDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportDownload;
    protected $reportClass;
    protected $reportMethod;
    protected $parameters;

    /**
     * Create a new job instance.
     */
    public function __construct(ReportDownload $reportDownload, string $reportClass, string $reportMethod, array $parameters = [])
    {
        $this->reportDownload = $reportDownload;
        $this->reportClass = $reportClass;
        $this->reportMethod = $reportMethod;
        $this->parameters = $parameters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $this->reportDownload->update([
                'status' => 'processing'
            ]);

            // Generate unique filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = Str::slug($this->reportDownload->report_name) . '_' . $timestamp . '.xlsx';
            $filePath = 'reports/' . $filename;

            // Instantiate the report class and call the method
            $reportInstance = app($this->reportClass);
            
            // Call the report generation method with parameters
            $reportData = call_user_func_array([$reportInstance, $this->reportMethod], $this->parameters);

            // If the method returns a file path (for direct file generation)
            if (is_string($reportData) && file_exists($reportData)) {
                // Move the generated file to storage
                Storage::put($filePath, file_get_contents($reportData));
                unlink($reportData); // Clean up temporary file
            }
            // If the method returns binary data or a response
            elseif ($reportData instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                // Handle BinaryFileResponse
                $tempPath = $reportData->getFile()->getPathname();
                Storage::put($filePath, file_get_contents($tempPath));
            }
            // If the method returns raw data that needs to be processed
            else {
                // This would need custom handling based on your report structure
                // For now, we'll assume the method handles file creation internally
                Storage::put($filePath, $reportData);
            }

            // Update the report download record with success
            $this->reportDownload->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'file_name' => $filename,
                'completed_at' => now()
            ]);

        } catch (Exception $e) {
            // Update status to failed with error message
            $this->reportDownload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);

            // Log the error for debugging
            \Log::error('Report generation failed', [
                'report_download_id' => $this->reportDownload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        $this->reportDownload->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'completed_at' => now()
        ]);
    }
}
