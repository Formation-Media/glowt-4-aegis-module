<?php

namespace Modules\AEGIS\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\AEGIS\Console\Commands\ExportData;
use Modules\AEGIS\Console\Commands\ImportData;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('competencies:expire')->daily();
        });
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            ExportData::class,
            ImportData::class,
        ]);
    }
}
