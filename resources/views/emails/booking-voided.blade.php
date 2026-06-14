<!DOCTYPE html>
<html>
<head>
    <title>Booking Voided</title>
</head>
<body>
    <h2>Hello, {{ $booking->user->name }}</h2>

    <p>We're writing to let you know that your booking has been voided.</p>

    <table border="1" cellpadding="8">
        <tr>
            <th>Room</th>
            <td>{{ $booking->room->room_name }}</td>
        </tr>
        <tr>
            <th>Date</th>
            <td>{{ $booking->booking_date }}</td>
        </tr>
        <tr>
            <th>Time</th>
            <td>{{ $booking->startTimeSlot->start_time }} – {{ $booking->endTimeSlot->end_time }}</td>
        </tr>
        <tr>
            <th>Reason</th>
            <td>{{ $booking->void_reason ?? 'Not specified' }}</td>
        </tr>
    </table>

    <p>If you have questions, please contact the admin.</p>
</body>
</html>