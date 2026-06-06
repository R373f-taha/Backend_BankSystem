{{-- resources/views/emails/transfer-received.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Received</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .panel {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        .panel h3 {
            margin-top: 0;
            color: #4CAF50;
        }
        .detail-row {
            padding: 8px 0;
            font-size: 16px;
        }
        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            padding: 20px;
        }
        .button {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 14px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #888;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Money Received!</h1>
        </div>

        <div class="content">
            <p style="font-size: 18px;">Hello {{ $receiverName }},</p>

            <p>Great news! You've received a new transfer! 💵</p>

            <div class="panel">
                <h3>Transfer Details:</h3>
                <div class="detail-row"><strong>Amount:</strong> ${{ number_format($amount, 2) }}</div>
                <div class="detail-row"><strong>From:</strong> {{ $senderName }}</div>
                <div class="detail-row"><strong>Date:</strong> {{ $date }}</div>
            </div>

            <p>The amount has been added to your balance and is ready to use.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/dashboard" class="button">Go to Dashboard</a>
            </div>

            <p style="margin-top: 20px;">Thank you for your trust.</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
