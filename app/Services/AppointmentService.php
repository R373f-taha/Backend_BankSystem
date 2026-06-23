<?php
namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentService
{
    public function getAvailableAppointments()
    {
        return Appointment::where('status', 'available')
            ->where('appointment_time', '>', Carbon::now())
            ->orderBy('appointment_time', 'asc')
            ->get();
    }

    public function getUserAppointments($accountRequestId)
    {
        return Appointment::where('account_request_id', $accountRequestId)
            ->orderBy('appointment_time', 'desc')
            ->get();
    }

    public function rescheduleAppointment($accountRequestId, $oldAppointmentId, $newAppointmentId)
    {
        if ($oldAppointmentId) {
            Appointment::where('id', $oldAppointmentId)
                ->where('account_request_id', $accountRequestId)
                ->update([
                    'account_request_id' => null,
                    'status' => 'available'
                ]);
        }

        $newAppointment = Appointment::where('id', $newAppointmentId)
            ->where('status', 'available')
            ->firstOrFail();

        $newAppointment->update([
            'account_request_id' => $accountRequestId,
            'status' => 'reserved'
        ]);

        return $newAppointment;
    }

    public function generateStaticSlotsForDay(Carbon $date)
    {
        if ($date->isFriday()) {
            return 0;
        }

        $start = $date->copy()->setTime(10, 0, 0);
        $end = $date->copy()->setTime(14, 0, 0);
        
        $slots = [];

        while ($start->lt($end)) {
            $exists = Appointment::where('appointment_time', $start->toDateTimeString())->exists();
            
            if (!$exists) {
                $slots[] = [
                    'appointment_time' => $start->toDateTimeString(),
                    'status' => 'available',
                    'account_request_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $start->addMinutes(15);
        }

        if (!empty($slots)) {
            Appointment::insert($slots);
        }

        return count($slots);
    }

}