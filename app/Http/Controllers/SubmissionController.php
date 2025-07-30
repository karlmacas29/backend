<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{



    // updating the status of the applicant if this applicant are qualified to rate
    public function update_status(Request $request, $applicant_id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        // Correct field: nPersonalInfo_id
        $submission = Submission::where('nPersonalInfo_id', $applicant_id)->first();

        if (!$submission) {
            return response()->json([
                'message' => 'Submission not found.',
            ], 404);
        }
        $submission->status = $validated['status'];
        $submission->save();

        return response()->json([
            'message' => 'Status successfully updated.',
            'data' => $submission
        ]);
    }

    // fetching the all data on submission table
    public function index()
    {

        $data = Submission::all();
        return response()->json([
            'data' => $data
        ]);
    }

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
