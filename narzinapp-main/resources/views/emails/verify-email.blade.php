<!-- resources/views/emails/verify-email.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* Reset styles */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.4;
            -webkit-text-size-adjust: none;
        }

        /* Main container */
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .email-header {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .logo {
            max-width: 150px;
            height: auto;
        }

        /* Content */
        .email-content {
            padding: 30px;
            color: #374151;
        }

        .welcome-text {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
            text-align: center;
        }

        .message-text {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        /* Button */
        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .verify-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.2s;
        }

        .verify-button:hover {
            background-color: #1d4ed8;
        }

        /* Footer */
        .email-footer {
            padding: 20px 30px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .footer-text {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin: 0;
        }

        .url-text {
            word-break: break-all;
            color: #2563eb;
            font-size: 13px;
            margin-top: 15px;
        }

        .expiry-text {
            font-size: 14px;
            color: #6b7280;
            margin-top: 20px;
            text-align: center;
            font-style: italic;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }

            .email-content {
                padding: 20px;
            }

            .verify-button {
                display: block;
                text-align: center;
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <!-- Add your logo here -->
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="logo">
            </div>

            <div class="email-content">
                <h1 class="welcome-text">
                    Welcome to {{ config('app.name') }}!
                </h1>

                <p class="message-text">
                    Hi {{ $user->name }},
                </p>

                <p class="message-text">
                    Thanks for signing up! To get started, please verify your email address by clicking the button below.
                </p>

                <div class="button-container">
                    <a href="{{ $url }}" class="verify-button" target="_blank">
                        Verify Email Address
                    </a>
                </div>

                <p class="message-text">
                    This verification link will expire {{ $expiresIn }}.
                </p>

                <p class="message-text">
                    If you did not create an account with us, you can safely ignore this email.
                </p>
            </div>

            <div class="email-footer">
                <p class="footer-text">
                    If you're having trouble clicking the "Verify Email Address" button,
                    copy and paste the URL below into your web browser:
                </p>
                <p class="url-text">
                    {{ $url }}
                </p>
                <p class="expiry-text">
                    For security reasons, this link will expire in 60 minutes.
                </p>
            </div>
        </div>
    </div>
</body>
</html>