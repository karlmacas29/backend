<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\EmailApi;
use App\Models\Submission;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\excel\nPersonal_info;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{


    // // updating the status of the applicant if this applicant are qualified to rate
    // public function evaluation(Request $request, $id)
    // {
    //     // ✅ Validate input
    //     $validated = $request->validate([
    //         'status' => 'required|string',
    //         'education_remark' => 'nullable|string',
    //         'experience_remark' => 'nullable|string',
    //         'training_remark' => 'nullable|string',
    //         'eligibility_remark' => 'nullable|string',
    //     ]);

    //     // ✅ Find submission
    //     $submission = Submission::findOrFail($id);

    //     // ✅ Update submission
    //     $submission->update([
    //         'status' => $validated['status'],
    //         'education_remark' => $validated['education_remark'] ?? 'N/A',
    //         'experience_remark' => $validated['experience_remark'] ?? 'N/A',
    //         'training_remark' => $validated['training_remark'] ?? 'N/A',
    //         'eligibility_remark' => $validated['eligibility_remark'] ?? 'N/A',
    //     ]);

    //     // ✅ Find applicant (internal or external)
    //     $applicant = nPersonal_info::where('id', $submission->nPersonalInfo_id)->first();
    //     $applicantExternal = DB::table('xPersonalAddt')->where('ControlNo', $submission->ControlNo)->first();
    //     $applicantName = DB::table('xPersonal')->where('ControlNo', $submission->ControlNo)->first();
    //     $activeApplicant = $applicant ?? $applicantExternal;

    //     if (!$activeApplicant) {
    //         Log::warning("⚠️ No applicant found for submission ID: {$id}");
    //         return response()->json([
    //             'message' => 'Submission updated, but applicant not found for email notification.',
    //             'data' => $submission
    //         ]);
    //     }

    //     // ✅ Log applicant info
    //     Log::info('Applicant record:', [$activeApplicant]);

    //     // ✅ Detect email field
    //     $email = $activeApplicant->email_address
    //         ?? $activeApplicant->emailAdd
    //         ?? $activeApplicant->EmailAdd
    //         ?? null;

    //     // ✅ Detect applicant full name
    //     $fullname = '';
    //     if ($applicant) {
    //         $fullname = trim("{$applicant->firstname} {$applicant->lastname}");
    //     } elseif ($applicantName) {
    //         $fullname = trim("{$applicantName->Firstname} {$applicantName->Surname}");
    //     }

    //     // ✅ Fetch job details
    //     $job = \App\Models\JobBatchesRsp::find($submission->job_batches_rsp_id);
    //     $position = $job->Position ?? 'the applied position';
    //     $office = $job->Office ?? 'the corresponding office';

    //     Log::info("📧 Email info: {$email}, Name: {$fullname}, Job: {$position}, Office: {$office}");

    //     if (!empty($email)) {
    //         $subject = "Application Status Update ";

    //         // ✅ Build remarks section
    //         $remarks = "
    //         <br><br><strong>Evaluation Remarks:</strong><br>
    //         Education: {$submission->education_remark}<br>
    //         Experience: {$submission->experience_remark}<br>
    //         Training: {$submission->training_remark}<br>
    //         Eligibility: {$submission->eligibility_remark}<br>
    //     ";

    //         // ✅ Message content based on status
    //         switch (strtolower($submission->status)) {
    //             case 'qualified':
    //                 $message = "
    //                 Dear {$fullname},<br><br>
    //                 Congratulations! You have been qualified for the next stage of evaluation
    //                 for the position of <strong>{$position}</strong> under <strong>{$office}</strong>.<br>
    //                 Please stay tuned for further instructions.
    //                 {$remarks}
    //             ";
    //                 break;

    //             case 'unqualified':
    //                 $message = "
    //                 Dear {$fullname},<br><br>
    //                 We appreciate your effort in applying for the position of <strong>{$position}</strong>
    //                 under <strong>{$office}</strong>. However, after evaluation, your application
    //                 did not meet the required qualifications.
    //                 {$remarks}
    //             ";
    //                 break;

    //             default:
    //                 $message = "
    //                 Dear {$fullname},<br><br>
    //                 Your application status for the position of <strong>{$position}</strong>
    //                 under <strong>{$office}</strong> has been updated to: <strong>{$submission->status}</strong>.
    //                 {$remarks}
    //             ";
    //                 break;
    //         }

    //         // ✅ Send email
    //         Mail::to($email)->queue(new EmailApi($message, $subject));
    //         Log::info("✅ Email queued for: {$email}");
    //     } else {
    //         $identifier = $activeApplicant->ControlNo ?? $activeApplicant->id ?? 'Unknown';
    //         Log::warning("⚠️ Applicant has no email address. Identifier: {$identifier}");
    //     }

    //     return response()->json([
    //         'message' => 'Evaluation successfully saved and email notification processed.',
    //         'data' => $submission
    //     ]);
    // }


    // deleting applicant on the job_post he/she applicant
    public function delete($id)
    {
        $submission = Submission::find($id);

        if (!$submission) {
            return response()->json([
                'status' => false,
                'message' => 'Submission not found.'
            ], 404);
        }

        $submission->delete();
        return response()->json([
            'status' => true,
            'message' => 'Submission deleted successfully.'
        ]);
    }

    public function evaluation(Request $request, $id)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'status' => 'required|string',
            'education_remark' => 'nullable|string',
            'experience_remark' => 'nullable|string',
            'training_remark' => 'nullable|string',
            'eligibility_remark' => 'nullable|string',
            'education_qualification' => 'nullable|string',
            'experience_qualification' => 'nullable|string',
            'training_qualification' => 'nullable|string',
            'eligibility_qualification' => 'nullable|string',
        ]);

        // ✅ Update submission in one call
        $submission = Submission::findOrFail($id);
        $submission->update([
            'status' => $validated['status'],
            'education_remark' => $validated['education_remark'] ?? null,
            'experience_remark' => $validated['experience_remark'] ?? null,
            'training_remark' => $validated['training_remark'] ?? null,
            'eligibility_remark' => $validated['eligibility_remark'] ?? null,

            'education_qualification' => $validated['education_qualification'] ?? null,
            'experience_qualification' => $validated['experience_qualification'] ?? null,
            'training_qualification' => $validated['training_qualification'] ?? null,
            'eligibility_qualification' => $validated['eligibility_qualification'] ?? null,
        ]);

        // ✅ Fetch applicant and job in one shot
        $applicant = nPersonal_info::find($submission->nPersonalInfo_id);

        $externalApplicant = DB::table('xPersonalAddt')
            ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
            ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
            ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
            ->first();

        $activeApplicant = $applicant ?? $externalApplicant;

        if (!$activeApplicant) {
            Log::warning("⚠️ No applicant found for submission ID: {$id}");
            return response()->json([
                'message' => 'Submission updated, but applicant not found for email notification.',
                'data' => $submission
            ]);
        }

        // ✅ Determine email and full name
        $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;
        $fullname = $applicant
            ? trim("{$applicant->firstname} {$applicant->lastname}")
            : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

        // ✅ Fetch job details
        $job = \App\Models\JobBatchesRsp::find($submission->job_batches_rsp_id);
        $position = $job->Position ?? 'the applied position';
        $office = $job->Office ?? 'the corresponding office';

        if (!empty($email)) {
            $subject = "Application Status Update";

            $remarks = "
            <br><br><strong>Evaluation Remarks:</strong><br>
            Education: {$submission->education_remark}<br>
            Experience: {$submission->experience_remark}<br>
            Training: {$submission->training_remark}<br>
            Eligibility: {$submission->eligibility_remark}<br>
        ";

            $statusLower = strtolower($submission->status);
            $message = match ($statusLower) {
                'qualified' => "
                Dear {$fullname},<br><br>
                Congratulations! You have been qualified for the next stage of evaluation
                for the position of <strong>{$position}</strong> under <strong>{$office}</strong>.<br>
                Please stay tuned for further instructions.
                {$remarks}
            ",
                'unqualified' => "
                Dear {$fullname},<br><br>
                We appreciate your effort in applying for the position of <strong>{$position}</strong>
                under <strong>{$office}</strong>. However, after evaluation, your application
                did not meet the required qualifications.
                {$remarks}
            ",
                default => "
                Dear {$fullname},<br><br>
                Your application status for the position of <strong>{$position}</strong>
                under <strong>{$office}</strong> has been updated to: <strong>{$submission->status}</strong>.
                {$remarks}
            ",
            };

            Mail::to($email)->queue(new EmailApi($message, $subject));
        }

        return response()->json([
            'message' => 'Evaluation successfully saved and email notification processed.',
            'data' => $submission
        ]);
    }




}
