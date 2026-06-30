<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingLotRequest extends FormRequest
{
    public function authorize(): bool
    {
        $parkingLot = $this->route('parking_lot');

        return $this->user() !== null
            && $parkingLot !== null
            && $this->user()->isOwner()
            && $this->user()->id === $parkingLot->owner_id;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'total_capacity' => ['required', 'integer', 'min:1', 'max:10000'],
            'available_spots' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  string|int|null  $key
     * @param  mixed  $default
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();

        if (is_array($validated) && array_key_exists('available_spots', $validated)) {
            $validated['available_spots'] = min(
                (int) $validated['available_spots'],
                (int) ($validated['total_capacity'] ?? $this->input('total_capacity', PHP_INT_MAX)),
            );
        }

        if ($key === null) {
            return $validated;
        }

        return data_get($validated, $key, $default);
    }
}
