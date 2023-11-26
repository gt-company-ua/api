<?php

namespace App\Console;

use App\Services\CarService;
use App\Services\CitiesUpdateService;
use App\Services\DraftOrderService;
use App\Services\GreenCardService;
use App\Services\OrderService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            (new CitiesUpdateService())->ingo();
        })
            ->name('cities:update')
            ->dailyAt("03:00")
            ->withoutOverlapping();

        $schedule->call(function () {
            (new GreenCardService())->sendGreenCardDraft();
        })
            ->name('orders:greencard:draft')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->call(function () {
            (new OrderService(null))->sentPolicyToClients();
        })
            ->name('orders:greencard:files')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->call(function () {
            (new CarService())->updateCars();
        })
            ->name('cars:update')
            ->dailyAt("04:00")
            ->withoutOverlapping();

        $schedule->call(function () {
            (new DraftOrderService())->sendDraftOrders();
        })
            ->name('draft:sent')
            ->everyThreeMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
