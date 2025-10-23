<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
        }

        .content {
            padding: 25px;
        }

        .content h4 {
            margin: 0 0 15px;
            font-size: 18px;
            color: #333;
        }

        .content p {
            line-height: 1.6;
            font-size: 15px;
            color: #555;
        }

        .footer {
            text-align: center;
            padding: 15px;
            background: #f1f1f1;
            font-size: 13px;
            color: #888;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            margin-top: 15px;
            background-color: #007bff;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $subject }}</h2>
        </div>

        <div class="content">
            <h4>Hello,</h4>
            <p>{{ $mailmessage }}</p>

            {{-- Example: Optional CTA Button --}}
            {{-- <a href="{{ $actionUrl ?? '#' }}" class="btn">View Details</a> --}}
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} RSP System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
