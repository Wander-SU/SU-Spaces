<?php

namespace App\Mail;

use App\Models\RoomFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoomIssueReported extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public RoomFeedback $feedback) {}

    public function envelope(): Envelope
    {
        $room = $this->feedback->selection?->room?->room_name ?? 'a room';

        return new Envelope(subject: "Room feedback: {$room}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.room-issue');
    }
}