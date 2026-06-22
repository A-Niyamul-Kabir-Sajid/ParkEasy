<?php

namespace App\Http\Requests\Review;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parking_lot_id' => ['required', 'integer', 'exists:parking_lots,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $user = $this->user();
            $lotId = $this->integer('parking_lot_id');

            if ($user === null || $lotId === 0) {
                return;
            }

            $hasCompletedBooking = Booking::query()
                ->forDriver($user)
                ->where('parking_lot_id', $lotId)
                ->where('status', BookingStatus::Completed->value)
                ->exists();

            if (! $hasCompletedBooking) {
                $validator->errors()->add('parking_lot_id', 'You can only review a lot after completing a booking there.');
            }

            $alreadyReviewed = Review::query()
                ->where('driver_id', $user->id)
                ->where('parking_lot_id', $lotId)
                ->exists();

            if ($alreadyReviewed) {
                $validator->errors()->add('parking_lot_id', 'You have already reviewed this parking lot.');
            }
        });
    }
}
