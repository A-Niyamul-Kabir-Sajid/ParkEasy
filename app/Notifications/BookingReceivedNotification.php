<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReceivedNotification extends Notification implements ShouldQueue
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
        $driver = $this->booking->driver;

        return (new MailMessage)
            ->subject('New booking — '.($lot?->name ?? 'ParkEasy'))
            ->greeting('Hi '.$notifiable->name.',')
            ->line('A driver has booked a spot at your lot.')
            ->line('Driver: '.($driver?->name ?? 'Unknown'))
            ->line('Lot: '.($lot?->name ?? 'Unknown'))
            ->line('Start: '.$this->booking->start_time->format('M j, Y g:i A'))
            ->line('End: '.$this->booking->end_time->format('M j, Y g:i A'))
            ->line('Total: ৳'.number_format($this->booking->totalCost(), 2))
            ->action(
                'View bookings',
                route('owner.parking-lots.bookings', $lot),
            )
            ->line('Thank you for hosting with ParkEasy.');
    }
}
