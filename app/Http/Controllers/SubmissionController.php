<?php

namespace App\Http\Controllers;

use App\Mail\EmailApi;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\excel\nPersonal_info;
use Illuminate\Support\Facades\Mail;

class SubmissionController extends Controller
{




    // updating the status of the applicant if this applicant are qualified to rate
    // this function for applicant that qualified to rate or not
    // public function evaluation(Request $request, $id)
    // {
    //     // Validate the incoming request
    //     $validated = $request->validate([
    //         'status' => 'required|string',
    //         'education_remark' => 'nullable|string',
    //         'experience_remark' => 'nullable|string',
    //         'training_remark' => 'nullable|string',
    //         'eligibility_remark' => 'nullable|string',
    //     ]);

    //     // Find the submission record by ID
    //     $submission = Submission::findOrFail($id);

    //     // Update the submission fields
    //     $submission->status = $validated['status'];
    //     $submission->education_remark = $validated['education_remark'] ?? 'N/A';
    //     $submission->experience_remark = $validated['experience_remark'] ??  'N/A';
    //     $submission->training_remark = $validated['training_remark'] ??  'N/A';
    //     $submission->eligibility_remark = $validated['eligibility_remark'] ??  'N/A';
    //     $submission->save();

    //     return response()->json([
    //         'message' => 'Evaluation successfully sent.',
    //         'data' => $submission
    //     ]);
    // }


    public function evaluation(Request $request, $id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'status' => 'required|string',
            'education_remark' => 'nullable|string',
            'experience_remark' => 'nullable|string',
            'training_remark' => 'nullable|string',
            'eligibility_remark' => 'nullable|string',
        ]);

        // Find the submission record by ID
        $submission = Submission::findOrFail($id);

        // Update the submission fields
        $submission->status = $validated['status'];
        $submission->education_remark = $validated['education_remark'] ?? 'N/A';
        $submission->experience_remark = $validated['experience_remark'] ??  'N/A';
        $submission->training_remark = $validated['training_remark'] ??  'N/A';
        $submission->eligibility_remark = $validated['eligibility_remark'] ??  'N/A';
        $submission->save();

        // --- Find the applicant’s info ---
        $applicant = nPersonal_info::where('id', $id)->first();
        // ⚠️ adjust the key if your foreign key is different (e.g., applicant_control_no)
        Log::info('Applicant ID:', [$id]);
        Log::info('Applicant record:', [$applicant]);
        Log::info('Applicant email:', [$applicant->email_address ?? 'no email']);

        if ($applicant && $applicant->email_address) {
            // Determine email subject and message
            $subject = "Application Status Update - RSP System";

            if (strtolower($submission->status) === 'qualified') {
                $message = "Congratulations! You have been qualified for the next stage of evaluation. Please stay tuned for further instructions.";
            } elseif (strtolower($submission->status) === 'unqualified') {
                $message = "We appreciate your effort in applying. However, after evaluation, your application did not meet the required qualifications.";
            } else {
                $message = "Your application status has been updated to: {$submission->status}.";
            }

            // Send email
            Mail::to($applicant->email_address)->queue(new EmailApi($message, $subject));
        }

        return response()->json([
            'message' => 'Evaluation successfully sent and email notification delivered.',
            'data' => $submission
        ]);
    }

    // fetching the all data on submission table
    // public function index()
    // {

    //     $data = Submission::all();
    //     return response()->json([
    //         'data' => $data
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

}
