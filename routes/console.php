<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('edi:download')->hourly();
Schedule::command('edi:process')->hourly();

Schedule::command('edi:send-mckesson-orders')->dailyAt('14:00');
Schedule::command('edi:send-mckesson-orders')->dailyAt('20:00');

Schedule::command('edi:send-cardinal-orders')->dailyAt('14:00');
Schedule::command('edi:send-cardinal-orders')->dailyAt('20:00');

Schedule::command('edi:send-staples-orders')->dailyAt('14:00');
Schedule::command('edi:send-staples-orders')->dailyAt('20:00');

Schedule::command('po:send-emails')->dailyAt('14:00');
Schedule::command('po:send-emails')->dailyAt('20:00');

Schedule::command('edi:send-henryschein-orders')->dailyAt('14:00');
Schedule::command('edi:send-henryschein-orders')->dailyAt('20:00');

// Schedule::command('EDI:email_alerts')->dailyAt('08:00');

Schedule::command('app:send-weekly-open-tickets')->weeklyOn(1, '8:00');

Schedule::command('app:pending-orders')->dailyAt('08:00');

Schedule::command('app:pending-orders-for-practice')->weeklyOn(1, '8:00');

Schedule::command('app:send-medline-orders')->dailyAt('08:00');


// Schedule::command('billing:send-monthly-invoices')
//     ->monthlyOn(1, '09:00')
//     ->withoutOverlapping();

// Schedule::command('billing:send-monthly-invoices');
