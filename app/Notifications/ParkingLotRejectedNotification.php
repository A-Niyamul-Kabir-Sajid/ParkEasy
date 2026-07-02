<?php

namespace App\Notifications;

use App\Models\ParkingLot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParkingLotRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ParkingLot $parkingLot,
        public string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your parking lot was not approved')
            ->greeting('Hello '.$this->parkingLot->owner?->name.',')
            ->line('Unfortunately, your parking lot "'.$this->parkingLot->name.'" was not approved.')
            ->line('Reason from the reviewer:')
            ->line($this->reason)
            ->line('You can update the lot and resubmit it for verification from your dashboard.')
            ->action('Open my parking lots', route('owner.parking-lots.index'))
            ->line('Thank you for using ParkEasy.');
    }
}
