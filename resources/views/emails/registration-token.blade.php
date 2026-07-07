<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SU-Spaces Verification</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fbf7f0; background-color: rgba(247, 235, 217, 0.3); padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border: 1px solid rgba(29, 45, 84, 0.08);">
        <div style="background-color: #941c1c; padding: 24px 32px; text-align: left;">
            <h2 style="color: #ffffff; margin: 0; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;">SU-Spaces Verification</h2>
        </div>

        <div style="padding: 32px;">
            <p style="color: #1d2d54; font-size: 15px; font-weight: 500; margin: 0 0 24px 0;">Hello! Thank you for registering an account on SU-Spaces. Use the secure authentication token below to complete your setup profile:</p>

            <div style="background-color: #02338D; border-radius: 12px; padding: 18px; text-align: center; margin: 24px 0; border-bottom: 4px solid #c99d3b;">
                <span style="color: #ffffff; font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 800; letter-spacing: 0.25em;">{{ $token }}</span>
            </div>

            <p style="color: #718096; font-size: 12px; font-weight: 500; text-align: center; margin: 16px 0 0 0; font-style: italic;">⚠️ This single-use validation token will expire in exactly 15 minutes for your account security.</p>
        </div>
    </div>
</body>
</html>
