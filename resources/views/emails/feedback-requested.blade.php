<!DOCTYPE html>
<html>
<head><title>How was your room?</title></head>
<body style="margin:0; padding:40px 20px; background-color:#fbf7f0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width:540px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #f3f4f6;">
        <div style="background-color:#941c1c; padding:24px;">
            <h2 style="color:#ffffff; margin:0; font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">SU Spaces</h2>
        </div>
        <div style="padding:32px;">
            <h2 style="margin:0 0 12px; color:#1d2d54; font-size:22px; font-weight:800;">
                How was {{ $selection->room?->room_name ?? 'your room' }}?
            </h2>
            <p style="margin:0 0 16px; color:#4a5568; font-size:14px; line-height:1.6;">
                We saw that you used this room
                @if($selection->room?->building) in {{ $selection->room->building->building_name }}@endif
                on {{ $selection->starts_at->format('D j M, g:i A') }}. How did you find it?
            </p>
            <p style="margin:0; color:#4a5568; font-size:14px; line-height:1.6;">
                Open SU Spaces, go to <strong>My activity &rarr; Past</strong> and tap <strong>How was it?</strong>.
                It takes about ten seconds.
            </p>
        </div>
    </div>
</body>
</html>