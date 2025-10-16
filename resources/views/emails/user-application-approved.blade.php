<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 20px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background-color: #4f46e5;
            color: white !important;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 5px;
            display: inline-block;
        }
        .footer {
            padding: 20px 0;
            border-top: 1px solid #eee;
            text-align: center;
            color: #888;
            font-size: 0.9em;
        }
        h1 {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application Approved</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $name }},</p>
            
            <p>Congratulations! Your application to join LexCav as a {{ $role }} has been approved. You can now access all the features of our platform.</p>
            
            @if($approvalNote ?? false)
            <p><strong>Note from the administrator:</strong><br>
            {{ $approvalNote }}</p>
            @endif
            
            <div class="button-container">
                <a href="{{ route('login') }}" class="button">Login to Your Account</a>
            </div>
            
            <p>Thank you for choosing LexCav. We look forward to having you as part of our community.</p>
            
            <p>Best regards,<br>
            {{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html> 