<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    //


    public function store_score(Request $request)
    {
        $validated = $request->validate([
            'nPersonalInfo_id' => 'required|exists:nPersonalInfo,id',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
            'education_score' => 'required|integer',
            'experience_score' => 'required|integer',
            'training_score' => 'required|integer',
            'performance_score' => 'required|integer',
            'behavioral_score' => 'required|integer',
            'total_qs' => 'required|integer',
            'grand_total' => 'required|integer',
            'ranking' => 'required|integer',
            'status' => 'required',
        ]);

        $submission = Submission::updateOrCreate(
            [
                'nPersonalInfo_id' => $validated['nPersonalInfo_id'],
            ],
            [
                'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
                'education_score' => $validated['education_score'],
                'experience_score' => $validated['experience_score'],
                'training_score' => $validated['training_score'],
                'performance_score' => $validated['performance_score'],
                'behavioral_score' => $validated['behavioral_score'],
                'total_qs' => $validated['total_qs'],
                'grand_total' => $validated['grand_total'],
                'ranking' => $validated['ranking'],
                'status' => $validated['status'],
            ]
        );

        return response()->json([
            'message' => 'Successfully created or updated',
            'data' => $submission
        ]);
    }


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



    public function index(){

        $data = Submission::all();
        return response()->json([
            'data'=> $data
        ]);
     }


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
