<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(30);

        while ($startDate->lte($endDate)) {
            if (!$startDate->isFriday() && !$startDate->isSaturday()) {
                
                $workStart = $startDate->copy()->setHour(9)->setMinute(0);
                $workEnd = $startDate->copy()->setHour(15)->setMinute(0);

                while ($workStart->lt($workEnd)) {
                    Appointment::create([
                        'appointment_time' => $workStart->toDateTimeString(),
                        'status' => 'available',
                    ]);

                    $workStart->addMinutes(30);
                }
            }
            $startDate->addDay();
        }
    }
}