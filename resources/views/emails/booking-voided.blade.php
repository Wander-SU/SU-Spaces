<!DOCTYPE html>
<html>
<head>
    <title>Booking Voided</title>
</head>
<body style="margin:0; background-color:#f8fafc; padding:40px 20px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="max-width:580px; margin:0 auto; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); border:1px solid #e2e8f0;">
        <div style="background-color:#1d2d54; padding:24px; text-align:center; border-bottom:4px solid #c99d3b;">
            <img src="{{ asset('images/strathmore_logo.png') }}" alt="SU-Spaces" style="display:block; margin:0 auto; max-width:180px; height:auto;">
        </div>

        <div style="padding:28px 24px;">
            <h2 style="margin:0 0 12px; color:#1d2d54; font-size:22px; font-weight:800; line-height:1.2;">Hello, {{ $booking->user->name }}</h2>

            <p style="margin:0 0 16px; color:#4a5568; font-size:14px; line-height:1.6;">We're writing to let you know that your booking has been voided.</p>

            <div style="background-color:#f8fafc; border:1px solid #edf2f7; border-radius:12px; padding:20px; margin:24px 0;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding-bottom:12px; margin-bottom:12px; border-bottom:1px solid #edf2f7;">
                    <span style="color:#718096; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; font-weight:700;">Room</span>
                    <span style="color:#1d2d54; font-size:14px; font-weight:600; text-align:right;">{{ $booking->room->room_name }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding-bottom:12px; margin-bottom:12px; border-bottom:1px solid #edf2f7;">
                    <span style="color:#718096; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; font-weight:700;">Date</span>
                    <span style="color:#1d2d54; font-size:14px; font-weight:600; text-align:right;">{{ $booking->booking_date }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding-bottom:12px; margin-bottom:12px; border-bottom:1px solid #edf2f7;">
                    <span style="color:#718096; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; font-weight:700;">Time</span>
                    <span style="color:#1d2d54; font-size:14px; font-weight:600; text-align:right;">{{ $booking->startTimeSlot->start_time }} – {{ $booking->endTimeSlot->end_time }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                    <span style="color:#718096; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; font-weight:700;">Reason</span>
                    <span style="color:#1d2d54; font-size:14px; font-weight:600; text-align:right;">{{ $reason }} scheduled to take place during the time of your booking</span>
                </div>
            </div>

            <p style="margin:0; color:#4a5568; font-size:14px; line-height:1.6;">If you have questions, please contact the admin.</p>
        </div>
    </div>
</body>
</html>