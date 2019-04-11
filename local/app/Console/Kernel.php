<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\JurnalPenyusutan::class,
        Commands\AlertResiStock::class,
        Commands\StnkKir::class,
        Commands\UpdateDashboard::class,
        Commands\CreateCustomerCoa::class,
        Commands\CreateInvoiceResi::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:jurnal-penyusutan')
                    ->daily()
                    ->appendOutputTo(storage_path('logs/jurnal-penyusutan.log'));

        $schedule->command('cron:alert-resi-stock')
                    ->daily()
                    ->appendOutputTo(storage_path('logs/alert-resi-stock.log'));

        $schedule->command('cron:alert-stnk-kir')
                    ->daily()
                    ->appendOutputTo(storage_path('logs/alert-stnk-kir.log'));

        $schedule->command('cron:update-data-dashboard')
                    ->dailyAt('06:00')
                    ->appendOutputTo(storage_path('logs/update-data-dashboard.log'));

        $schedule->command('cron:update-data-dashboard')
                    ->dailyAt('09:00')
                    ->appendOutputTo(storage_path('logs/update-data-dashboard.log'));


        $schedule->command('cron:update-data-dashboard')
                    ->dailyAt('12:00')
                    ->appendOutputTo(storage_path('logs/update-data-dashboard.log'));

        $schedule->command('cron:update-data-dashboard')
                    ->dailyAt('15:00')
                    ->appendOutputTo(storage_path('logs/update-data-dashboard.log'));
    }
}
