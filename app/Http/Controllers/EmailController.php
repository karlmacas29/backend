<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\EmailApi;
use App\Models\Schedule;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    //

    // public function sendEmail(){

    //     $toEmail = "clifordmillan2025@gmail.com";
    //     $message = "testng rako aprt";
    //     $subject = 'tagum city';

    //    $response =  Mail::to($toEmail)->send(new EmailApi($message, $subject));

    //    dd($response);
    // }


    public function sendEmailInterview(Request $request)
    {
        $validated = $request->validate([
            'applicants' => 'required|array', // array of submission IDs
            'applicants.*' => 'exists:submission,id',
            'date_interview' => 'required|date',
            'time_interview' => 'required|string',
            'venue_interview' => 'required|string',
            'batch_name' => 'required|string',
            'job_batches_rsp' => 'required|exists:job_batches_rsp,id'

        ]);

        $date = Carbon::parse($validated['date_interview'])->format('F d, Y');
        $time = $validated['time_interview'];
        $venue = $validated['venue_interview'];
        $batchName = $validated['batch_name'] ?? null;

        $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp']);

        if (!$job) {
            return response()->json(['error' => 'Job batch not found'], 404);
        }
        // // Get job details
         $position = $job->Position ?? 'the applied position';
        // $office = $job->Office ?? 'the corresponding office';
        $SalaryGrade = $job->SalaryGrade ?? 'the corresponding SG';

        $count = 0;

        foreach ($validated['applicants'] as $submissionId) {
            $submission = Submission::with('nPersonalInfo')->find($submissionId);

            if (!$submission) {
                Log::warning("⚠️ Submission ID {$submissionId} not found.");
                continue;
            }

            $applicant = $submission->nPersonalInfo;

            // Check external applicant if internal info missing
            if (!$applicant && $submission->ControlNo) {
                $externalApplicant = DB::table('xPersonalAddt')
                    ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
                    ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
                    ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
                    ->first();
                $applicant = $externalApplicant;
            }

            $email = $applicant->email_address ?? $applicant->EmailAdd ?? null;
            $fullname = $applicant->firstname ?? $applicant->Firstname ?? '';
            $lastname = $applicant->lastname ?? $applicant->Surname ?? '';
            $fullname = trim("{$fullname} {$lastname}");

            if (!$email) {
                Log::warning("⚠️ Applicant {$fullname} has no email address.");
                continue;
            }

            try {
                Mail::to($email)->queue(
                    new EmailApi(
                        "Interview Invitation",       // subject
                        'mail-template.interview',    // Blade view
                        [
                            'mailSubject' => "Interview Invitation",
                            'fullname' => $fullname,

                            'date' => $date,
                            'time' => $time,
                            'venue' => $venue,
                            'position' => $position,
                            'SalaryGrade' => $SalaryGrade,
                        ]
                    )
                );
                Log::info("📝 Preparing email for {$fullname} ({$email}) with data: " . json_encode([
                    'mailSubject' => "Interview Invitation",
                    'fullname' => $fullname,
                    'date' => $date,
                    'time' => $time,
                    'venue' => $venue,
                ]));


                // Save schedule in DB
                Schedule::create([
                    'submission_id' => $submission->id,
                    'batch_name' => $batchName,
                    'full_name' => $fullname,
                    'date_interview' => $validated['date_interview'],
                    'time_interview' => $time,
                    'venue_interview' => $venue,
                ]);

                Log::info("📧 Queued INTERVIEW email for {$fullname} ({$email})");
                $count++;
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email to {$fullname} ({$email}): {$e->getMessage()}");
            }
        }

        return response()->json([
            'success'=> true,
            'message' => "Interview invitations successfully sent to {$count} applicant(s).",
        ]);
    }



    // public function sendEmailApplicantBatch(Request $request) // send an email to all applicants of a specific job post for Qualified and Unqualified status
    // {
    //     $validated = $request->validate([
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     $jobId = $validated['job_batches_rsp_id'];

    //     // ✅ Get all applicants for that job with Qualified or Unqualified status
    //     $submissions = Submission::where('job_batches_rsp_id', $jobId)
    //         ->with('nPersonalInfo') // eager load relation
    //         ->whereIn('status', ['Qualified', 'Unqualified'])
    //         ->get();

    //     if ($submissions->isEmpty()) {
    //         return response()->json([
    //             'message' => 'No applicants found with Qualified or Unqualified status for this job post.'
    //         ], 404);
    //     }

    //     // ✅ Get job details for context
    //     $job = \App\Models\JobBatchesRsp::find($jobId);
    //     $position = $job->Position ?? 'the applied position';
    //     $office = $job->Office ?? 'the corresponding office';

    //     $count = 0;

    //     foreach ($submissions as $submission) {
    //         $applicant = $submission->nPersonalInfo;

    //         // Check for internal or external applicant
    //         $externalApplicant = DB::table('xPersonalAddt')
    //             ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
    //             ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
    //             ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
    //             ->first();

    //         $activeApplicant = $applicant ?? $externalApplicant;

    //         if (!$activeApplicant) {
    //             Log::warning("⚠️ No applicant found for submission ID: {$submission->id}");
    //             continue;
    //         }

    //         $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;
    //         $fullname = $applicant
    //             ? trim("{$applicant->firstname} {$applicant->lastname}")
    //             : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

    //         if (empty($email)) {
    //             Log::warning("⚠️ Applicant {$fullname} has no email address.");
    //             continue;
    //         }

    //         $remarks = "
    //         <br><br><strong>Evaluation Remarks:</strong><br>
    //         Education: {$submission->education_remark}<br>
    //         Experience: {$submission->experience_remark}<br>
    //         Training: {$submission->training_remark}<br>
    //         Eligibility: {$submission->eligibility_remark}<br>

    //     ";

    //         // $statusLower = strtolower($submission->status);

    //         // ✅ Email content based on status

    //         try {

    //             Mail::to($email)->queue(
    //                 new EmailApi(
    //                     "Applicant Status",       // subject
    //                     'mail-template.unqlified',    // Blade view
    //                     [
    //                         'mailSubject' => "Applicant Status",
    //                         'fullname' => $fullname,
    //                         'postion' => $position,
    //                         'office' => $office,
    //                         'Education' => $submission->education_remark,
    //                          'Experience' => $submission->experience_remark,
    //                         'Training' =>$submission->training_remark,
    //                          'Eligibility' => $submission->eligibility_remark,

    //                         'education_qualification' => $submission->education_qualification,
    //                         'experience_qualification' => $submission->experience_qualification,
    //                         'training_qualification' => $submission->training_qualification,
    //                         'eligibility_qualification' => $submission->eligibility_qualification,

    //                     ]
    //                 )
    //             );
    //             // Mail::to($email)->queue(new EmailApi($message, $subject));

    //             Log::info("📧 Queued email for {$fullname} ({$email}) with status: {$submission->status}");
    //             $count++;
    //         } catch (\Exception $e) {
    //             Log::error("❌ Failed to queue email for {$fullname} ({$email}): {$e->getMessage()}");
    //         }
    //     }

    //     return response()->json([
    //         'message' => "Email notifications sent to {$count} applicant(s) for this job post.",
    //     ]);
    // }

    // public function sendEmailInterview(Request $request)
    // {
    //     $validated = $request->validate([
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //         'date_interview' => 'required|date',
    //         'time_interview' => 'required|string',
    //         'venue_interview' => 'required|string',

    //     ]);

    //     $jobId = $validated['job_batches_rsp_id'];

    //     // ✅ Get qualified applicants only
    //     $submissions = Submission::where('job_batches_rsp_id', $jobId)
    //         ->with('nPersonalInfo')
    //         ->where('status', 'Qualified')
    //         ->get();

    //     if ($submissions->isEmpty()) {
    //         return response()->json([
    //             'message' => 'No qualified applicants found for this job post.',
    //         ], 404);
    //     }

    //     // ✅ Get job details
    //     $job = \App\Models\JobBatchesRsp::find($jobId);
    //     $position = $job->Position ?? 'the applied position';
    //     $office = $job->Office ?? 'the corresponding office';
    //     $date = Carbon::parse($validated['date_interview'])->format('F d, Y');
    //     $time = $validated['time_interview'];
    //     $venue = $validated['venue_interview'];

    //     $count = 0;

    //     foreach ($submissions as $submission) {
    //         $applicant = $submission->nPersonalInfo;

    //         // ✅ Check if internal or external
    //         $externalApplicant = DB::table('xPersonalAddt')
    //             ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
    //             ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
    //             ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
    //             ->first();

    //         $activeApplicant = $applicant ?? $externalApplicant;

    //         if (!$activeApplicant) {
    //             Log::warning("⚠️ No applicant found for submission ID: {$submission->id}");
    //             continue;
    //         }

    //         $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;
    //         $fullname = $applicant
    //             ? trim("{$applicant->firstname} {$applicant->lastname}")
    //             : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

    //         if (empty($email)) {
    //             Log::warning("⚠️ Applicant {$fullname} has no email address.");
    //             continue;
    //         }

    //         // ✅ Construct interview message
    //         $subject = "Interview Invitation for {$position}";
    //         $message = "
    //         Dear {$fullname},<br><br>
    //         Congratulations! You have been shortlisted for an <strong>interview</strong> for the position of
    //         <strong>{$position}</strong> under <strong>{$office}</strong>.<br><br>
    //         Please see the interview details below:<br><br>
    //         <strong>Date:</strong> {$date}<br>
    //         <strong>Time:</strong> {$time}<br>
    //         <strong>Venue:</strong> {$venue}<br><br>
    //         Kindly confirm your attendance by replying to this email.<br><br>
    //         We look forward to meeting you.<br><br>
    //         Best regards,<br>
    //         <strong>Recruitment, Selection, and Placement Unit</strong>
    //     ";

    //         try {
    //             Mail::to($email)->queue(new EmailApi($message, $subject));
    //             Log::info("📧 Queued INTERVIEW email for {$fullname} ({$email}) — {$position}");
    //             $count++;
    //         } catch (\Exception $e) {
    //             Log::error("❌ Failed to queue interview email for {$fullname} ({$email}): {$e->getMessage()}");
    //         }
    //     }

    //     Log::info("✅ Total interview emails queued: {$count} for JobPost ID {$jobId}");

    //     return response()->json([
    //         'message' => "Interview invitations successfully sent to {$count} qualified applicant(s).",
    //     ]);
    // }

    public function sendEmailApplicantBatch(Request $request)
    {
        $validated = $request->validate([
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $jobId = $validated['job_batches_rsp_id'];

        // Get ONLY Unqualified applicants
        $submissions = Submission::where('job_batches_rsp_id', $jobId)
            ->with('nPersonalInfo')
            ->where('status', 'Unqualified')
            ->get();

        if ($submissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Unqualified applicants found for this job post.'
            ], 404);
        }

        // Get job details
        $job = \App\Models\JobBatchesRsp::with('criteria:id,job_batches_rsp_id,Education,Eligibility,Training,Experience')
        ->find($jobId);

        $position = $job->position ?? 'the applied position';
        $office = $job->office ?? 'the corresponding office';

        //qs of the job post
        $education_qs = $job->criteria->Education ?? null;
        $eligibility_qs  = $job->criteria->Eligibility ?? null;
        $training_qs  = $job->criteria->Training ?? null;
        $experience_qs  = $job->criteria->Experience ?? null;

        $count = 0;

        foreach ($submissions as $submission) {
            $applicant = $submission->nPersonalInfo;

            // Check internal/external records
            $externalApplicant = DB::table('xPersonalAddt')
                ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
                ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
                ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
                ->first();

            $activeApplicant = $applicant ?? $externalApplicant;

            if (!$activeApplicant) {
                Log::warning("⚠️ No applicant record found for submission ID: {$submission->id}");
                continue;
            }

            // Email
            $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;

            // Fullname
            $fullname = $applicant
                ? trim("{$applicant->firstname} {$applicant->lastname}")
                : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

            if (empty($email)) {
                Log::warning("⚠️ Applicant {$fullname} has no email address.");
                continue;
            }

            // Fixed: ONLY Unqualified template
            $template = 'mail-template.unqualified';

            try {
                Mail::to($email)->queue(
                    new EmailApi(
                        "Applicant Status - Unqualified",
                        $template,
                        [
                            'fullname' => $fullname,
                            'lastname' => $applicant->lastname ?? $externalApplicant->Surname,
                            'position' => $position,
                            'office' => $office,

                            // QS Qualifications of applicant
                            'education_qualification' => $submission->education_qualification,
                            'experience_qualification' => $submission->experience_qualification,
                            'training_qualification' => $submission->training_qualification,
                            'eligibility_qualification' => $submission->eligibility_qualification,

                            // Remarks
                            'education_remark' => $submission->education_remark,
                            'experience_remark' => $submission->experience_remark,
                            'training_remark' => $submission->training_remark,
                            'eligibility_remark' => $submission->eligibility_remark,


                            // qs of job post
                             'education_qs'=>   $education_qs,
                            'eligibility_qs' =>    $eligibility_qs,
                            'training_qs' =>    $training_qs,
                            'experience_qs' =>   $experience_qs,

                        ]
                    )
                );

                Log::info("📧 Queued UNQUALIFIED email for {$fullname} ({$email}).");

                $count++;
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email for {$fullname}: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Unqualified email notifications sent to {$count} applicant(s)."
        ]);
    }
}
