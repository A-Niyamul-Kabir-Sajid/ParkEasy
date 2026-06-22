<?php

namespace App\Http\Requests\Booking;

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDriver() ?? false;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'parking_lot_id' => ['required', 'integer', 'exists:parking_lots,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $lot = ParkingLot::query()->find($this->input('parking_lot_id'));

            if ($lot === null) {
                return;
            }

            if ($lot->verification_status !== ParkingLotVerificationStatus::Verified) {
                $validator->errors()->add('parking_lot_id', 'This parking lot is not available for booking.');
            }

            if ((int) $lot->available_spots < 1) {
                $validator->errors()->add('parking_lot_id', 'No spots are currently available.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_time.after' => 'The start time must be in the future.',
            'duration_hours.max' => 'A booking cannot be longer than 24 hours.',
        ];
    }
}
