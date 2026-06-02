<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0A0F1E;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #E2E8F0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #0A0F1E;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #111827;
            border: 1px solid #1E2D4A;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #1E2D4A, #111827);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #1E2D4A;
        }
        .logo {
            height: 40px;
            width: auto;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .intro-text {
            font-size: 15px;
            line-height: 1.6;
            color: #94A3B8;
            margin-bottom: 30px;
        }
        .details-card {
            background-color: #1A2235;
            border: 1px solid #1E2D4A;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .detail-row {
            margin-bottom: 16px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 14px;
            color: #E2E8F0;
            font-weight: 500;
        }
        .employee-badge {
            display: inline-flex;
            align-items: center;
            background-color: rgba(79, 126, 255, 0.1);
            color: #4F7EFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .status-approved {
            background-color: rgba(16, 217, 122, 0.1);
            color: #10D97A;
        }
        .status-rejected {
            background-color: rgba(255, 79, 106, 0.1);
            color: #FF4F6A;
        }
        .description-box {
            background-color: #111827;
            border-left: 3px solid #4F7EFF;
            padding: 12px 16px;
            font-style: italic;
            color: #94A3B8;
            margin-top: 6px;
            border-radius: 0 8px 8px 0;
        }
        .btn-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #4F7EFF;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(79, 126, 255, 0.3);
        }
        .footer {
            padding: 24px 30px;
            text-align: center;
            font-size: 12px;
            color: #475569;
            border-top: 1px solid #1E2D4A;
            background-color: #0d1321;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ asset('LoopsWhite.png') }}" alt="Loops Logo" class="logo">
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Loops WFH') }}. All rights reserved.</p>
                <p>This is an automated notification. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
