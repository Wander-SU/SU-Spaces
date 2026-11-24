<?php

namespace App\Console\Commands;

use App\Mail\FeedbackRequested;
use App\Models\RoomSelection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PromptForFeedback extends Command
{
    protected $signature = 'feedback:prompt';
    protected $description = 'Flag selections whose slot has ended and notify the user';

    public function handle(): int
    {
        $flagged = 0;
        $notified = 0;

        $selections = RoomSelection::with(['user', 'room' => fn ($r) => $r->withTrashed()->with('building')])
            ->where('ends_at', '<=', now())
            ->whereNull('feedback_prompted_at')
            ->get();

        foreach ($selections as $s) {
            // Claim it first, so two overlapping runs can never notify twice.
            $claimed = RoomSelection::whereKey($s->id)
                ->whereNull('feedback_prompted_at')
                ->update(['feedback_prompted_at' => now()]);
            if (! $claimed) {
                continue;
            }
            $flagged++;

            // Older slots are flagged (so they stay quiet) but not notified.
            if ($s->ends_at->gte(now()->subHour()) && $this->notify($s)) {
                $notified++;
            }
        }

        $this->info("Flagged {$flagged}, notified {$notified}.");

        return self::SUCCESS;
    }

    /** The one place to swap in real push notifications later. */
    private function notify(RoomSelection $s): bool
    {
        if (! config('spaces.feedback.email_preview') || ! $s->user?->email) {
            return false;
        }

        try {
            Mail::to($s->user->email)->send(new FeedbackRequested($s));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}