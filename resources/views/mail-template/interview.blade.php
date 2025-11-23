<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>{{ $mailSubject }}</title> --}}

    <style>


        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 30px 0;
            color: #333;
        }

        /* Bond Paper Style Container */
        .container {
            max-width: 1500px;
            /* Wider like bond paper */
            background: #ffffff;
            margin: 0 auto;
            border: 1px solid #dcdcdc;
            /* Light paper border */
            padding: 50px;
            /* More spacing like a printed document */
            box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.07);
        }

        .header {
            background-color: #1d8d07;
            color: #fff;
            text-align: center;
            padding: 25px;
            border-radius: 5px;
            margin-bottom: 40px;
        }

        .content p,
        .content li {
            font-size: 16px;
            line-height: 1.7;
            color: #333;
        }

        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
            color: #1d8d07;
        }

        ul {
            padding-left: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 15px;
            background: #f1f1f1;
            font-size: 13px;
            color: #666;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="90" valign="middle">
            <img src="https://phshirt.com/wp-content/uploads/2021/11/City-of-Tagum-Logo.png"
                 width="80" style="display: block;">
        </td>
        <td valign="middle" style="color: #00703c; padding-left: 20px;">
            <div style="font-size: 8pt; font-weight: 500; line-height: 1;">REPUBLIC OF THE PHILIPPINES</div>
            <div style="font-size: 8pt; font-weight: 500; line-height: 1;">PROVINCE OF DAVAO DEL NORTE</div>
            <div style="font-size: 13pt; font-weight: bold; line-height: 1; padding-top: 2px; text-transform: uppercase;">CITY OF TAGUM</div>
        </td>
    </tr>
</table>
     <div style="background-color: #008000; color: white; padding: 10px 20px; margin-top: 5px; margin-bottom: 30px;">
    <div style="font-weight: bold; text-transform: uppercase; font-size: 13pt; text-align: center;">
        HUMAN RESOURCE MERIT PROMOTION AND SELECTION
    </div>
</div>

        <div class="content">
            <p>Dear <strong>{{ $fullname }}</strong>,</p>

            <p>
                This pertains to your application for the vacant plantilla position of
                <strong>{{ $position }}, SG {{ $SalaryGrade }}</strong>
                assigned to the City Human Resource Management Office.
            </p>

            <p>
                Please be informed that you are scheduled for a <strong>face-to-face interview</strong>
                with the members of the <strong> Human Resource Merit Promotion and Selection Board (HRMPSB).</strong>
                Kindly refer to the details below:
            </p>

            <p>

            <strong>Interview details:</strong><br>
            <strong>Date:</strong> {{ $date }}<br>
            <strong>Time:</strong> {{ $time }}<br>
            <strong>Venue:</strong> {{ $venue }}<br>

            </p>

            <h3>Reminders for the Interview:</h3>
            <ul>
                <li>Be on time (at least <strong>30 minutes</strong> before your scheduled interview).</li>
                <li>Bring a <strong>valid ID</strong> for identification purposes.</li>
                <li>Observe the <strong>dress code</strong> in accordance with Civil Service Commission standards.</li>
                <li>Inform us in advance if you require special assistance (e.g., wheelchair or mobility support).</li>
                <li>Some HR personnel may take <strong>random photos</strong> for documentation purposes only. All
                    photos will be handled according to data privacy principles.</li>
            </ul>

            <p>
                <strong>Kindly reply to this email to confirm your attendance for the interview.</strong>
            </p>

            <p>If you have any clarifications, you may reach us from <strong>Monday to Friday, 8:00 AM – 5:00
                    PM</strong> through the following:</p>

            <ul>
                <li><strong>Email:</strong> <a
                        href="mailto:lgutagumhrmo.recruitment@gmail.com">lgutagumhrmo.recruitment@gmail.com</a></li>
                <li><strong>Telephone:</strong> (084) 645-3300 local 252</li>
            </ul>

            <p>Thank you.</p>

        </div>

        <div class="footer">
            This is an automated notification. Please confirm your attendance by replying to this email.
        </div>

    </div>
</body>

</html>
