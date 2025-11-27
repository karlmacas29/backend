<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background-color: #1d8d07; color: white; text-align: center; padding: 20px; }
        .header h2 { margin: 0; font-size: 22px; }
        .content { padding: 25px; }
        .content p { line-height: 1.6; font-size: 15px; color: #555; }
        .footer { text-align: center; padding: 15px; background: #f1f1f1; font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $mailSubject }}</h2>
        </div>

        <div class="content">
            <p>
                Dear <strong>{{ $firstname }} {{ $lastname }},</strong><br><br>

                @if($isUpdate)
                    Your application for <strong>{{ $jobPosition }}</strong> under <strong>{{ $jobOffice }}</strong>
                    has been successfully <strong>updated</strong>.<br><br>
                    We have received your updated documents and our HR team is reviewing them.
                @else
                    Thank you for submitting your application for <strong>{{ $jobPosition }}</strong> under
                    <strong>{{ $jobOffice }}</strong>.<br><br>
                    Your application is now under review by our HR team.
                @endif
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Recruitment, Selection and Placement. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
