<!DOCTYPE html>
<html>
<head>
    <title>Booking Voided</title>
</head>
<body style="margin:0; padding:40px 20px; background-color:#fbf7f0; background-color:rgba(247, 235, 217, 0.3); font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width:540px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); border:1px solid #f3f4f6;">
        <div style="background-color:#941c1c; padding:24px; text-align:left;">
            <h2 style="color:#ffffff; margin:0; font-size:18px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">Booking Cancellation</h2>
        </div>

        <div style="padding:32px;">
            <h2 style="margin:0 0 12px; color:#1d2d54; font-size:22px; font-weight:800; line-height:1.2;">Hello, {{ $booking->user->name }}</h2>

            <p style="margin:0 0 16px; color:#4a5568; font-size:14px; line-height:1.6;">We're writing to let you know that your booking has been voided.</p>

            <div style="background-color:rgba(249, 250, 251, 0.7); border:1px solid #f3f4f6; border-radius:12px; padding:20px; margin:24px 0;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding-bottom:8px; margin-bottom:14px; border-bottom:1px solid rgba(229, 231, 235, 0.5);">
                    <span style="font-size:10px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:0.09em; color:#9ca3af;">Room</span>
                    <span style="font-size:14px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:600; color:#1d2d54; text-align:right;">{{ $booking->room->room_name }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding-bottom:8px; margin-bottom:14px; border-bottom:1px solid rgba(229, 231, 235, 0.5);">
                    <span style="font-size:10px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:0.09em; color:#9ca3af;">Date</span>
                    <span style="font-size:14px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:600; color:#1d2d54; text-align:right;">{{ $booking->booking_date }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding-bottom:8px; margin-bottom:14px; border-bottom:1px solid rgba(229, 231, 235, 0.5);">
                    <span style="font-size:10px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:0.09em; color:#9ca3af;">Time</span>
                    <span style="font-size:14px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:600; color:#1d2d54; text-align:right;">{{ $booking->startTimeSlot->start_time }} – {{ $booking->endTimeSlot->end_time }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; padding-bottom:0; margin-bottom:0; border-bottom:0;">
                    <span style="font-size:10px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:0.09em; color:#9ca3af;">Reason</span>
                    <span style="font-size:14px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight:600; color:#1d2d54; text-align:right;">{{ $reason }} scheduled to take place during the time of your booking</span>
                </div>
            </div>

            <p style="color:#9ca3af; font-size:12px; text-align:center; font-weight:500; margin:24px 0 0 0;">If you have any questions or require alternative scheduling, please reach out to the department administrator.</p>
        </div>
    </div>
</body>
</html>