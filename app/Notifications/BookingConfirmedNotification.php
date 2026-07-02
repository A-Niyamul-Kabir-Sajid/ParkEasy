<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Booking $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $lot = $this->booking->parkingLot;

        return (new MailMessage)
            ->subject('Booking confirmed — '.($lot?->name ?? 'ParkEasy'))
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your parking spot is reserved.')
            ->line('Lot: '.($lot?->name ?? 'Unknown'))
            ->line('Start: '.$this->booking->start_time->format('M j, Y g:i A'))
            ->line('End: '.$this->booking->end_time->format('M j, Y g:i A'))
            ->line('Total: ৳'.number_format($this->booking->totalCost(), 2))
            ->action(
                'View booking',
                route('driver.bookings.show', $this->booking),
            )
            ->line('Thank you for using ParkEasy.');
    }
}
