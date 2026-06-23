<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Request Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #4a00e0 0%, #8e2de2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 30px; }
        .appointment-box { background: #eef2f3; border-left: 5px solid #4a00e0; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .btn-action { display: block; width: 220px; margin: 25px auto; text-align: center; background: #4a00e0; color: white !important; text-decoration: none; padding: 12px 20px; border-radius: 25px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .link-text { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-family: monospace; word-break: break-all; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; background: #f8f9fa; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👋 Hello {{ $accountRequest->full_name }}</h1>
        </div>
        <div class="content">
            <p>We have successfully received your account creation request. Your request is currently under review by our administration team.</p>
            
            <div class="appointment-box">
                <h3>🏦 Please visit the bank at the following appointment:</h3>
                @if($accountRequest->appointment)
                    <p><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($accountRequest->appointment->appointment_time)->format('Y-m-d H:i') }}</p>
                @else
                    <p><strong>Date & Time:</strong> Will be specified shortly.</p>
                @endif
            </div>

            <p><strong>Your Unique Link:</strong></p>
            <p class="link-text">{{ $accountRequest->unique_link }}</p>

            <p style="text-align: center; margin-top: 25px;">
                If you would like to view your appointment or reschedule it to another time that suits you better, please click the button below:
            </p>

            <a href="http://localhost:5173/reschedule/{{ $accountRequest->unique_link }}" class="btn-action">
                View & Reschedule Appointment
            </a>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>