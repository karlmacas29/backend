<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{



    // updating the status of the applicant if this applicant are qualified to rate
    //submit evaluation
    public function evaluation(Request $request, $id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        // Find the submission record by ID
        $submission = Submission::findOrFail($id); 

        // Update the status
        $submission->status = $validated['status'];
        $submission->save();

        return response()->json([
            'message' => 'Evaluation successfully sent.',
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
