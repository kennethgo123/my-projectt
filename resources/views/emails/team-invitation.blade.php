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
            <h1>Team Invitation</h1>
        </div>
        
        <div class="content">
            <p>{{ __('You have been invited to join the :team team!', ['team' => $invitation->team->name]) }}</p>
            
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
                <p>{{ __('If you do not have an account, you may create one by clicking the button below. After creating an account, you may click the invitation acceptance button in this email to accept the team invitation:') }}</p>
                
                <div class="button-container">
                    <a href="{{ route('register') }}" class="button">{{ __('Create Account') }}</a>
                </div>
                
                <p>{{ __('If you already have an account, you may accept this invitation by clicking the button below:') }}</p>
            @else
                <p>{{ __('You may accept this invitation by clicking the button below:') }}</p>
            @endif
            
            <div class="button-container">
                <a href="{{ $acceptUrl }}" class="button">{{ __('Accept Invitation') }}</a>
            </div>
            
            <p>{{ __('If you did not expect to receive an invitation to this team, you may discard this email.') }}</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
