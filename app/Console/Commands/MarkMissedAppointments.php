<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkMissedAppointments extends Command
{
    protected $signature = 'appointments:mark-missed';
    protected $description = 'Mark past appointments as missed if they are not completed';

    public function handle()
    {
        $affected = DB::table('appointments')
            ->where(function ($query) {
                $query->where('date', '<', now()->toDateString())
                      ->orWhere(function ($q) {
                          $q->where('date', now()->toDateString())
                            ->where('time', '<', now()->toTimeString());
                      });
            })
            ->whereNotIn('status', ['completed', 'missed', 'cancelled'])
            ->update(['status' => 'missed']);

        $this->info("Updated appointments: $affected");
    }
}
