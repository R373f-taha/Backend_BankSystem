<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\AccountRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueLink = $this->route('unique_link');
        $accountRequest = AccountRequest::where('unique_link', $uniqueLink)->first();

        return [
            'old_appointment_id' => [
                'nullable', 
                'integer', 
                Rule::exists('appointments', 'id')->where(function ($query) use ($accountRequest) {
                    $query->where('account_request_id', $accountRequest?->id);
                })
            ],
            'new_appointment_id' => [
                'required', 
                'integer', 
                Rule::exists('appointments', 'id')->where(function ($query) {
                    $query->where('status', 'available');
                })
            ],
        ];
    }
}