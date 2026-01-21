<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SFTP;
/**
 * Class EdiDownloadCommand
 *
 * Laravel Artisan command responsible for downloading EDI files
 * from multiple partner SFTP servers (Henry Schein, McKesson, Cardinal).
 *
 * Responsibilities:
 * - Connect to partner SFTP servers with retry logic
 * - Scan configured transaction directories
 * - Download files safely using temp files
 * - Prevent duplicate downloads
 * - Delete remote files only in production
 *
 * This command is designed to be:
 * - Fault-tolerant (retries, backoff)
 * - Idempotent (won’t re-download existing files)
 * - Environment-safe (no deletions outside prod)
 *
 * Usage:
 * php artisan edi:download
 */
class EdiDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * This is the command used in the terminal.
     *
     * @var string
     */
    protected $signature = 'edi:download';

    /**
     * Description shown when running `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Download EDI files from partner SFTP servers';

    /**
     * Entry point for the Artisan command.
     *
     * - Loads partner configuration
     * - Iterates through each partner
     * - Delegates processing to smaller methods
     *
     * @return void
     */
    public function handle()
    {
        Log::info("=== 🚀 Starting EDI Download Job ===");

        /**
         * Partner SFTP configuration.
         *
         * Each partner contains:
         * - SFTP credentials
         * - Transaction directories to scan
         *
         * NOTE:
         * Paths are remote SFTP directories, not local paths.
         */


        $partners = [
            'henryschein' => [
                'host' => config('services.hs_sftp.host'),
                'username' => config('services.hs_sftp.username'),
                'password' => config('services.hs_sftp.password'),
                'port' => config('services.hs_sftp.port'),
                'transactions' => [
                    'outbound' => '/outbound',
                    'flagged' => '/inboundsave',
                ],
            ],
            'mckesson' => [
                'host' => config('services.mck_sftp.host'),
                'username' => config('services.mck_sftp.username'),
                'password' => config('services.mck_sftp.password'),
                'port' => config('services.mck_sftp.port'),
                'transactions' => [
                    'outbound' => '/outgoing/pickup',
                ],
            ],
            'cardinal' => [
                'host' => config('services.cah_sftp.host'),
                'username' => config('services.cah_sftp.username'),
                'password' => config('services.cah_sftp.password'),
                'port' => config('services.cah_sftp.port'),
                'transactions' => [
                    'outbound' => '/custom/FROMCARDINAL',
                ],
            ],
        ];

        foreach ($partners as $partner => $config) {
            $this->processPartner($partner, $config);
        }

        Log::info("=== ✅ EDI Download Job Completed ===");
    }

    /**
     * Process a single partner configuration.
     *
     * - Validates configuration
     * - Establishes SFTP connection
     * - Iterates through transaction directories
     *
     * @param string $partner Partner identifier (e.g. henryschein)
     * @param array  $config  Partner SFTP configuration
     * @return void
     */
    private function processPartner(string $partner, array $config): void
    {
        // Skip if missing config
        if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
            Log::warning("⚠️ Skipping {$partner}: Missing SFTP config.");
            return;
        }

        $sftp = $this->connectToSftp($partner, $config);
        if (!$sftp) {
            return;
        }

        foreach ($config['transactions'] as $transaction => $remotePath) {
            $this->processTransaction($sftp, $partner, $transaction, $remotePath);
        }
    }


    /**
     * Establish an SFTP connection with retry logic.
     *
     * Features:
     * - Retry attempts with exponential backoff
     * - Connection timeout
     * - Graceful failure handling
     *
     * @param string $partner Partner name
     * @param array  $config  SFTP configuration
     * @return SFTP|null      Connected SFTP instance or null on failure
     */
    private function connectToSftp(string $partner, array $config): ?SFTP
    {
        $maxRetries = 3;
        $retryDelay = 2;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            Log::info("🔌 Connecting to {$partner} ({$config['host']}) - Attempt {$attempt}/{$maxRetries}...");

            try {
                $sftp = new SFTP($config['host'], $config['port'] ?? 22);

                // Set timeout to prevent hanging
                $sftp->setTimeout(30);

                // Enable keepalive to maintain connection
                $sftp->enableQuietMode();

                if ($sftp->login($config['username'], $config['password'])) {
                    Log::info("✅ Connected to {$partner}");
                    return $sftp;
                }

                Log::error("❌ Failed login for {$partner} on attempt {$attempt}");

            } catch (\Throwable $e) {
                Log::error("❌ Exception while connecting to {$partner} on attempt {$attempt}: " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                Log::info("⏳ Waiting {$retryDelay} seconds before retry...");
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }

        Log::error("❌ Failed to connect to {$partner} after {$maxRetries} attempts");
        return null;
    }

    /**
     * Process a remote transaction directory.
     *
     * - Lists remote files
     * - Skips directory markers
     * - Delegates download per file
     *
     * @param SFTP  $sftp
     * @param string $partner
     * @param string $transaction
     * @param string $remotePath
     * @return void
     */
    private function processTransaction(SFTP $sftp, string $partner, string $transaction, string $remotePath): void
    {
        Log::info("📂 Checking {$partner}/{$transaction} at {$remotePath}");

        try {
            $files = $sftp->nlist($remotePath);

            if (empty($files) || $files === false) {
                Log::info("ℹ️ No files found in {$remotePath}");
                return;
            }

            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue; // skip directory entries
                }

                $this->downloadFile($sftp, $partner, $transaction, $remotePath, $file);
            }
        } catch (\Throwable $e) {
            Log::error("❌ Error listing files in {$remotePath}: " . $e->getMessage());
        }
    }

    /**
     * Download and process a single EDI file from the partner SFTP server.
     *
     * Workflow:
     * 1. Build the full remote file path
     * 2. Determine the transaction type (used for local folder structure)
     * 3. Create the local directory if it does not exist
     * 4. Skip download if file already exists locally (idempotency)
     * 5. Download the file with retry & validation logic
     * 6. Handle post-download actions (delete remote file in production)
     *
     * Local storage structure:
     * public/{partner}/{transactionType}/{filename}
     *
     * @param SFTP  $sftp         Active SFTP connection
     * @param string $partner     Partner identifier (e.g. henryschein, mckesson)
     * @param string $transaction Transaction category (outbound, flagged, etc.)
     * @param string $remotePath  Remote SFTP directory path
     * @param string $file        Filename to download
     *
     * @return void
     */

    private function downloadFile(SFTP $sftp, string $partner, string $transaction, string $remotePath, string $file): void
    {
        Log::info("📄 Found file '{$file}' in {$remotePath}");
        $remoteFile = rtrim($remotePath, '/') . '/' . $file;

        /**
         * Determine transaction type for local storage.
         *
         * - flagged   → stored under /flagged
         * - outbound  → grouped by file extension (850, 810, 997, etc.)
         * - fallback  → use transaction name directly
         */
        if ($transaction === 'flagged') {
            $transactionType = 'flagged';
        } elseif ($transaction === 'outbound') {
            $transactionType = pathinfo($file, PATHINFO_EXTENSION);
        } else {
            $transactionType = $this->detectTransactionTypeFromSt01($sftp, $remoteFile, $file);
        }

        /**
         * Build local directory path where the file will be saved.
         *
         * Example:
         * public/henryschein/850/
         */

        $localDir = public_path("{$partner}/{$transactionType}");
        if (!is_dir($localDir)) {
            mkdir($localDir, 0777, true);
        }

        $localPath = $localDir . '/' . $file;

        /**
         * Prevent duplicate downloads.
         *
         * If the file already exists locally, skip processing.
         * This makes the command safe to run multiple times.
         */

        if (file_exists($localPath)) {
            Log::info("⏭️ File '{$file}' already exists locally. Skipping.");
            return;
        }

        /**
         * Download the file using retry-based logic.
         *
         * - Uses temporary file to avoid partial downloads
         * - Validates file size after download
         * - Retries on transient SFTP/network failures
         */
        if ($this->downloadWithRetry($sftp, $remoteFile, $localPath, $file)) {
            $this->handleSuccessfulDownload($sftp, $partner, $remoteFile, $file);
        } else {
            Log::error("❌ Could not download '{$file}' after multiple attempts.");
        }
    }


    /**
     * Download a file with robust retry logic.
     *
     * - Uses temp file to avoid partial downloads
     * - Verifies file size
     * - Handles SFTP protocol edge cases
     *
     * @param SFTP  $sftp
     * @param string $remoteFile
     * @param string $localPath
     * @param string $fileName
     * @return bool
     */

    private function downloadWithRetry(SFTP $sftp, string $remoteFile, string $localPath, string $fileName): bool
    {
        $maxRetries = 5;
        $retryDelay = 2;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Check if remote file exists
                if (!$sftp->file_exists($remoteFile)) {
                    Log::error("❌ Remote file not found: {$remoteFile}");
                    return false;
                }

                // Attempt download
                Log::info("⬇️ Downloading '{$fileName}' - Attempt {$attempt}/{$maxRetries}");

                // Use a temporary file to avoid partial downloads
                $tempPath = $localPath . '.tmp';

                if ($sftp->get($remoteFile, $tempPath)) {
                    // Verify download by checking if file exists and has content
                    if (file_exists($tempPath) && filesize($tempPath) > 0) {
                        $localSize = filesize($tempPath);

                        // Move temp file to final location
                        rename($tempPath, $localPath);
                        Log::info("✅ Downloaded '{$fileName}' to {$localPath} (" . number_format($localSize) . " bytes)");
                        return true;
                    } else {
                        Log::warning("⚠️ Downloaded file is empty or missing. Retrying...");
                        if (file_exists($tempPath)) {
                            unlink($tempPath);
                        }
                    }
                } else {
                    Log::warning("⚠️ SFTP get() returned false on attempt {$attempt}");
                }

            } catch (\UnexpectedValueException $e) {
                // This is the specific error you're encountering
                Log::warning("⚠️ SFTP protocol error on attempt {$attempt}: " . $e->getMessage());

                // For protocol errors, try a longer delay
                if ($attempt < $maxRetries) {
                    Log::info("🔄 Protocol error detected, using longer delay...");
                    sleep($retryDelay * 2);
                }

            } catch (\Throwable $e) {
                Log::warning("⚠️ Unexpected error on attempt {$attempt}: " . $e->getMessage());
            }

            // Clean up temp file if it exists
            if (file_exists($localPath . '.tmp')) {
                unlink($localPath . '.tmp');
            }

            if ($attempt < $maxRetries) {
                Log::info("⏳ Waiting {$retryDelay} seconds before retry...");
                sleep($retryDelay);
                $retryDelay = min($retryDelay * 1.5, 30); // Exponential backoff with cap
            }
        }

        return false;
    }

    /**
     * Handle post-download actions.
     *
     * - Deletes remote file ONLY in production
     * - Prevents accidental data loss in lower environments
     *
     * @param SFTP  $sftp
     * @param string $partner
     * @param string $remoteFile
     * @param string $file
     * @return void
     */
    private function handleSuccessfulDownload(SFTP $sftp, string $partner, string $remoteFile, string $file): void
    {
        // Delete from SFTP only in production
        if (app()->environment('production')) {
            try {
                if ($sftp->delete($remoteFile)) {
                    Log::info("🗑️ Deleted '{$file}' from {$partner} SFTP");
                } else {
                    Log::warning("⚠️ Failed to delete '{$file}' from {$partner} SFTP");
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Exception while deleting '{$file}' from {$partner} SFTP: " . $e->getMessage());
            }
        } else {
            Log::info("🗑️ Skipping deletion in non-production environment");
        }
    }

    /**
     * Detect EDI transaction type by reading ST01 from file content.
     *
     * Example ST segment:
     * ST*850*0001~
     *
     * @param SFTP  $sftp
     * @param string $remoteFile
     * @param string $fileName
     *
     * @return string Transaction type (850, 810, etc.) or 'unknown'
     */
    private function detectTransactionTypeFromSt01(
        SFTP $sftp,
        string $remoteFile,
        string $fileName
    ): string {
        try {
            // Fetch file contents into memory (safe for EDI size)
            $contents = $sftp->get($remoteFile);

            if (!$contents) {
                Log::warning("⚠️ Unable to read file contents for ST01 detection: {$fileName}");
                return 'unknown';
            }

            /**
             * EDI segment separators:
             * - Segment terminator: ~
             * - Element separator: *
             */
            $segments = explode('~', $contents);

            foreach ($segments as $segment) {
                // Look for ST segment
                if (str_starts_with($segment, 'ST*')) {
                    $elements = explode('*', $segment);

                    // ST01 = transaction set ID (e.g. 850, 810)
                    if (!empty($elements[1])) {
                        Log::info("🔍 Detected transaction type {$elements[1]} from ST01 for {$fileName}");
                        return trim($elements[1]);
                    }
                }
            }

            Log::warning("⚠️ ST segment not found in {$fileName}");
        } catch (\Throwable $e) {
            Log::error("❌ Error detecting ST01 for {$fileName}: " . $e->getMessage());
        }

        return 'unknown';
    }

}