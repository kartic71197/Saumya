<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\Edi\Edi810Service;
use App\Services\Edi\Edi832Service;
use App\Services\Edi\Edi855Service;
use App\Services\Edi\Edi856Service;
use App\Services\Edi\EdiFlaggedService;
use Illuminate\Support\Facades\Log;

class EdiProcessCommand extends Command
{
    protected $signature = 'edi:process';
    protected $description = 'Process already-downloaded EDI files for all suppliers from local storage';

    protected $services;

    public function __construct(
        Edi810Service $edi810,
        Edi832Service $edi832,
        Edi855Service $edi855,
        Edi856Service $edi856,
        EdiFlaggedService $ediFlagged
    ) {
        parent::__construct();

        $this->services = [
            '810' => $edi810,
            '832' => $edi832,
            '855' => $edi855,
            '856' => $edi856,
            'flagged' => $ediFlagged,
        ];
    }

    public function handle()
    {
        Log::info("=== Starting Local EDI Processing Job ===");

        $partners = [
            'henryschein' => ['810', '832', '855', '856', 'flagged'],
            'mckesson' => ['810', '832', '855', '856', 'flagged'],
            'cardinal' => ['810', '832', '855', '856', 'flagged'],
        ];

        foreach ($partners as $partner => $transactions) {
            logger("--- Processing EDI files for partner: {$partner} ---");
            foreach ($transactions as $transaction) {
                logger("--- Processing transaction type: {$transaction} ---");
                $dir = public_path("{$partner}/{$transaction}");

                if (!is_dir($dir)) {
                    Log::warning("Directory $dir does not exist. Skipping.");
                    continue;
                }

                $files = array_diff(scandir($dir), ['.', '..']);
                if (empty($files)) {
                    Log::info("No local files found in $dir");
                    continue;
                }

                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;

                    if (!isset($this->services[$transaction])) {
                        Log::warning("No service defined for {$transaction}, skipping $filePath");
                        continue;
                    }

                    $success = $this->services[$transaction]->process($filePath);
                    // Move to archive
                    if(!$success) {
                        Log::error("❌ Processing failed for {$filePath}, skipping archiving.");
                        continue;
                    }
                    $archiveDir = public_path("{$partner}/archive/{$transaction}");
                    if (!is_dir($archiveDir)) {
                        mkdir($archiveDir, 0777, true);
                    }

                    $archivePath = $archiveDir . '/' . $file;

                    if (rename($filePath, $archivePath)) {
                        Log::info("📦 Archived {$filePath} -> {$archivePath}");
                    } else {
                        Log::error("❌ Failed to archive {$filePath}");
                    }
                }
            }
        }

        Log::info("=== Local EDI Processing Job Completed ===");
    }
}
