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
            width: 80px;
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
    </style>
</head>

<body>
    <div class="container">

        <!-- Letterhead -->
        <div class="letterhead">
            <img src="https://phshirt.com/wp-content/uploads/2021/11/City-of-Tagum-Logo.png" alt="City of Tagum Logo">
            <div class="letterhead-text">
                <div>REPUBLIC OF THE PHILIPPINES</div>
                <div>PROVINCE OF DAVAO DEL NORTE</div>
                <div>CITY OF TAGUM</div>
            </div>
        </div>

        <!-- Date -->
        <div class="date">
            December 27, 2024
        </div>

        <!-- Recipient -->
        <div class="recipient">
             <strong>Mr/Ms {{ $fullname }}</strong>
            48 WPU Residential Complex, Sta. Monica<br>
            Puerto Princesa, Palawan
        </div>

        <div class="recipient">
            Dear Mr/Ms {{ $fullname }},
        </div>

        <!-- Content -->
        <div class="content">
            <p>
                This refers to your application for the <strong>{{ $position }}</strong> position in
                the {{ $office }} of the City Government of Tagum.
            </p>

            <p>
                We are pleased to inform you that based on the evaluation of your qualifications vis-à-vis the prescribed
                qualification standards (QS) for the position, you were found to have <strong>met all the required qualifications</strong>.
                Details of the comparison are shown below:
            </p>

            <!-- Qualification Comparison Table -->
            <table class="qualification-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Prescribed QS</th>
                        <th style="width: 40%;">Position Requirements</th>
                        <th style="width: 40%;">Mr/Ms {{ $lastname }}Qualification</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Education</strong></td>
                        <td>
                            Bachelor's Degree in Tourism, Business, Law, Economics, Marketing,
                            Public Administration or related fields
                        </td>
                        <td>
                           {{$education_qualification  }}<br>

                        </td>
                    </tr>
                    <tr>
                        <td><strong>Training</strong></td>
                        <td>
                            Department of Tourism specific and mandatory training such as:<br>
                            • Tourism Awareness and Hospitality Building Seminar for LGUs<br>
                            • DRRM Seminar<br>
                            • Basic Tourism Statistics Training<br>
                            • Local Tourism Guidebook Orientation<br>
                            • Gender and Development Orientation
                        </td>
                        <td>
                              {{$training_qualification }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Experience</strong></td>
                        <td>5 Years of Relevant Experience</td>
                        <td> {{$experience_qualification  }}</td>
                    </tr>
                    <tr>
                        <td><strong>Eligibility</strong></td>
                        <td>Career Service - Professional Second Level Eligibility</td>
                        <td>{{$eligibility_qualification  }}</td>
                    </tr>
                </tbody>
            </table>

            <p>
                Your application will now proceed to the next stage of evaluation and selection. You will be notified of
                the succeeding steps or schedule of interview, if applicable.
            </p>

            <p>
                We wish you the best and thank you for your interest in joining the Local Government of Tagum.
            </p>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            Very truly yours,<br><br><br>

            <div class="signature-name">EDGAR C. DE GUZMAN</div>
            <div class="signature-title">City Administrator</div>
            <div class="signature-auth">
                Authorized Representative of the City Mayor<br>
                Chairperson
            </div>
        </div>

    </div>
</body>

</html>
