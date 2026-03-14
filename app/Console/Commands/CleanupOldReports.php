<?php

namespace App\Console\Commands;

use App\Models\ReportDownload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup {--hours=8 : Number of hours after which to delete reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old report download records and files to save storage space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $cutoffTime = now()->subHours($hours);

        $this->info("Cleaning up report downloads older than {$hours} hours...");

        // Get old report downloads
        $oldReports = ReportDownload::where('created_at', '<', $cutoffTime)->get();

        $deletedFiles = 0;
        $deletedRecords = 0;

        foreach ($oldReports as $report) {
            // Delete the file if it exists
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
                $deletedFiles++;
                $this->line("Deleted file: {$report->file_path}");
            }

            // Delete the database record
            $report->delete();
            $deletedRecords++;
        }

        $this->info("Cleanup completed:");
        $this->line("- Deleted {$deletedFiles} files");
        $this->line("- Deleted {$deletedRecords} database records");
        $this->line("- Freed up storage space");

        return Command::SUCCESS;
    }
}
