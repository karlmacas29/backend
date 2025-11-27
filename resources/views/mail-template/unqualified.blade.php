<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 30px 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            background: #ffffff;
            margin: 0 auto;
            border: 1px solid #dcdcdc;
            padding: 60px;
            box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.07);
        }

        .letterhead {
            text-align: center;
            margin-bottom: 30px;
        }

        .letterhead img {
            max-width: 200px;
            display: block;
            margin: 0 auto 10px;
        }

        .letterhead-text {
            color: #00703c;
            line-height: 1.3;
        }

        .letterhead-text div:nth-child(1),
        .letterhead-text div:nth-child(2) {
            font-size: 9pt;
            font-weight: 500;
        }

        .letterhead-text div:nth-child(3) {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .date {
            font-size: 11pt;
            margin: 30px 0 40px 0;
        }

        .recipient {
            font-size: 11pt;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .recipient strong {
            display: block;
            margin-bottom: 5px;
        }

        .content p {
            font-size: 11pt;
            line-height: 1.7;
            text-align: justify;
            margin: 15px 0;
        }

        .qualification-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }

        .qualification-table th,
        .qualification-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .qualification-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 50px;
            font-size: 11pt;
        }

        .signature-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 50px;
        }

        .signature-title {
            font-style: italic;
        }

        .signature-auth {
            font-size: 10pt;
        }

        .signatureImage {
            max-width: 300px;
            height: auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Letterhead -->
        <div class="letterhead">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo">
            <div class="letterhead-text">
                <div>REPUBLIC OF THE PHILIPPINES</div>
                <div>PROVINCE OF DAVAO DEL NORTE</div>
                <div>CITY OF TAGUM</div>
            </div>
        </div>

        <!-- Date -->
        <div class="date">
            {{ $date }}
        </div>

        <!-- Recipient -->
        <div class="recipient">
            <p>
                <strong>Mr/Ms. {{ $fullname }}</strong>
                {{ ucfirst(strtolower($street)) }},
                {{ ucwords(strtolower($barangay)) }}<br>
                {{ ucwords(strtolower($city)) }},
                {{ ucwords(strtolower($province)) }}
            </p>
        </div>

        <div class="recipient">
            Dear Mr/Ms. {{ $lastname }},
        </div>

        <!-- Content -->
        <div class="content">
            <p>
                This refers to your application for the <strong>{{ $position }}</strong> position in
                the <strong>{{ $office }}</strong> of the City Government of Tagum.
            </p>

            <p>
                We regret to inform you that based on the evaluation of your qualifications vis-à-vis the
                qualification standards (QS) for the position, as shown below:
            </p>

            <!-- Qualification Comparison Table -->
            <table class="qualification-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Prescribed QS</th>
                        <th style="width: 35%;">Position Requirements</th>
                        <th style="width: 35%;">Mr/Ms. {{ $lastname }}'s Qualification</th>
                        <th style="width: 15%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Education</strong></td>
                        <td>{!! nl2br(e($education_qs)) !!}</td>
                        <td>{!! $education_qualification !!}</td>
                        <td>{{ $education_remark }}</td>
                    </tr>
                    <tr>
                        <td><strong>Training</strong></td>
                        <td>{!! nl2br(e($training_qs)) !!}</td>
                        <td>{!! $training_qualification !!}</td>
                        <td>{{ $training_remark }}</td>
                    </tr>
                    <tr>
                        <td><strong>Experience</strong></td>
                        <td>{!! nl2br(e($experience_qs)) !!}</td>
                        <td>{!! $experience_qualification !!}</td>
                        <td>{{ $experience_remark }}</td>
                    </tr>
                    <tr>
                        <td><strong>Eligibility</strong></td>
                        <td>{!! nl2br(e($eligibility_qs)) !!}</td>
                        <td>{!! $eligibility_qualification !!}</td>
                        <td>{{ $eligibility_remark }}</td>
                    </tr>
                </tbody>
            </table>

            <p>
                With this, we highly encourage you to still take part in our future employment opportunities
                for position/s that will be deemed fit to your qualifications.
            </p>

            <p>Thank you.</p>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <img src="{{ $message->embed(public_path('images/signature1.png')) }}" alt="Signature" class="signatureImage">
        </div>

    </div>
</body>

</html>
