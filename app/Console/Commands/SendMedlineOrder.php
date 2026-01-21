<?php

namespace App\Console\Commands;



use phpseclib3\Net\SFTP;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMedlineOrder extends Command
{
    protected $signature = 'app:send-medline-orders';
    protected $description = 'Test Medline SFTP Login';

    public function handle()
    {
        $this->info('Connecting to Medline SFTP...');

        try {
            $sftp = new SFTP(
                config('services.med_sftp.host'),
                config('services.med_sftp.port', 22)
            );

            if (! $sftp->login(
                config('services.med_sftp.username'),
                config('services.med_sftp.password')
            )) {
                throw new \Exception('SFTP login failed');
            }

            $this->info('✅ SFTP login successful');
            Log::info('Medline SFTP login successful');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ SFTP login failed: ' . $e->getMessage());
            Log::error('Medline SFTP login failed', [
                'error' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
