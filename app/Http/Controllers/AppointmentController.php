<?php
namespace App\Http\Controllers;

use App\Services\AppointmentService;
use App\Models\AccountRequest;
use App\Http\Requests\RescheduleAppointmentRequest;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index($unique_link)
    {
        $accountRequest = AccountRequest::where('unique_link', $unique_link)->firstOrFail();
        
        return response()->json([
            'account_request_name' => $accountRequest->full_name,
            'my_appointments' => $this->appointmentService->getUserAppointments($accountRequest->id),
            'available_slots' => $this->appointmentService->getAvailableAppointments()
        ]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, $unique_link)
    {
        $accountRequest = AccountRequest::where('unique_link', $unique_link)->firstOrFail();
        $oldAppointmentId = $accountRequest->appointment ? $accountRequest->appointment->id : null;
        
        $appointment = $this->appointmentService->rescheduleAppointment(
            $accountRequest->id,
            $oldAppointmentId,
            $request->input('new_appointment_id')
        );

        return response()->json([
            'message' => 'Appointment rescheduled successfully.',
            'data' => $appointment
        ]);
    }
}