<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\IcoController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function(){
        //     IcoController::save_single_page_data_in_db();
        // })->everyMinute();
        //run scheduler for updating details database table
         $schedule->call(function(){
             $status = 'Upcoming';
             IcoController::save_icos_in_db( $status,1, 1000 );
         })->hourly();
         $schedule->call(function(){
                  $status = 'Ongoing';
                  IcoController::save_icos_in_db( $status,1, 1000 );
         })->cron("* */2 * * *");
         $schedule->call(function(){
            
            IcoController::save_particular_id_in_db();
        })->cron("* */3 * * *");
        //run scheduler change status
         $schedule->call(function(){
              IcoController::refresh_database();                
         })->cron("0 0 * * *");
        //run scheduler for updating details database table
        // $schedule->call(function(){
        //     $status = 'Ongoing';
        //     IcoController::save_icos_in_db( $status,1, 1000 );
        // })->cron("* */2 * * *");
        //run scheduler for removing all records older than 2 days
        //  $schedule->call(function(){
        //     $status = 'Ended';
        //     IcoController::save_icos_in_db( $status,1, 1000 );
        // })->cron("0 0 * * *");
    }
}
