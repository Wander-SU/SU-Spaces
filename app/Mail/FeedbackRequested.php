<?php

namespace App\Mail;

use App\Models\RoomSelection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackRequested extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public RoomSelection $selection) {}

    public function envelope(): Envelope
    {
        $room = $this->selection->room?->room_name ?? 'your room';

        return new Envelope(subject: "How was {$room}?");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.feedback-requested');
    }
}