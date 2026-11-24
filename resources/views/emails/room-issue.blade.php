@php
    $sel = $feedback->selection;
    $room = $sel?->room;
@endphp
<!DOCTYPE html>
<html>
<head><title>Room feedback</title></head>
<body style="margin:0; padding:40px 20px; background-color:#fbf7f0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width:540px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #f3f4f6;">
        <div style="background-color:#941c1c; padding:24px;">
            <h2 style="color:#ffffff; margin:0; font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">Room feedback</h2>
        </div>
        <div style="padding:32px; color:#1d2d54; font-size:14px; line-height:1.7;">
            <p style="margin:0 0 4px;"><strong>Room:</strong> {{ $room?->room_name ?? 'Removed room' }}</p>
            <p style="margin:0 0 4px;"><strong>Building:</strong> {{ $room?->building?->building_name ?? '-' }}</p>
            <p style="margin:0 0 4px;"><strong>Used:</strong> {{ $sel?->starts_at->format('D j M, g:i A') }} &ndash; {{ $sel?->ends_at->format('g:i A') }}</p>
            <p style="margin:0 0 4px;"><strong>Rating:</strong> {{ $feedback->rating }} / 5</p>
            <p style="margin:0 0 16px;"><strong>Marked as having a problem:</strong> {{ $feedback->has_issue ? 'Yes' : 'No' }}</p>
            @if($feedback->comment)
                <div style="background:#f9fafb; border:1px solid #f3f4f6; border-radius:12px; padding:16px;">
                    {{ $feedback->comment }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>