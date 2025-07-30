<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;

class JobBatchesRspController extends Controller
{
    // List all
    public function index()
    {
        // Only fetch jobs where end_post is today or later (still active)
        $today = Carbon::today();
        $activeJobs = JobBatchesRsp::whereDate('end_date', '>=', $today)
            ->orderBy('post_date', 'asc') // Optional: you can change this to 'created_at' if preferred
            ->get();

        return response()->json($activeJobs);
    }

    public function job_list()
    {
        // Only fetch jobs where end_post is today or later (still active)

        $activeJobs = JobBatchesRsp::all();
        return response()->json($activeJobs);
    }

    // public function job_list()
    // {
    //     // Get all job posts with criteria and assigned raters
    //     $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position') // Include only user id and name
    //         ->get();

    //     // Add 'status' and 'assigned_raters' to each job
    //     $jobsWithDetails = $jobs->map(function ($job) {
    //         $job->status = $job->criteriaRatings->isNotEmpty() ? 'created' : 'no criteria';
    //         $job->assigned_raters = $job->users; // Include users as assigned raters
    //         unset($job->users); // Optionally remove the original 'users' relation if not needed directly
    //         return $job;
    //     });

    //     return response()->json($jobsWithDetails);
    // }

    public function office()
    {
        // Only fetch jobs where end_post is today or later (still active)
       $data = JobBatchesRsp::select('Office','Position','SalaryGrade','ItemNo','id')->get();
       return response()->json($data);
    }

    // Create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Office' => 'nullable|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'PageNo' => 'nullable|string',
            'ItemNo' => 'nullable|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string', // Changed to string
        ]);

        // No default for post_date or end_date; must be set explicitly if required
        $jobBatch = JobBatchesRsp::create($validated);

        return response()->json($jobBatch, 201);
    }

    // Read single by PositionID and ItemNo
    public function show($PositionID, $ItemNo)
    {
        // Ensure you have `use Illuminate\Support\Facades\DB;` at the top of your file.
        $jobBatches = \Illuminate\Support\Facades\DB::select('SELECT * FROM job_batches_rsp WHERE PositionID = ? AND ItemNo = ?', [$PositionID, $ItemNo]);

        if (empty($jobBatches)) {
            return response()->json(['error' => 'No matching record found'], 404);
        }

        // DB::select returns an array of objects, so we take the first one.
        return response()->json($jobBatches[0]);
    }

    // Update
    // Update only post_date and end_date
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'post_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $jobPost = JobBatchesRsp::findOrFail($id);
        $jobPost->update([
            'post_date' => $validated['post_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json([
            'message' => 'Dates updated successfully.',
            'data' => $jobPost,
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $jobBatch = JobBatchesRsp::findOrFail($id);

    //     $validated = $request->validate([
    //         'Office' => 'nullable|string',
    //         'Office2' => 'nullable|string',
    //         'Group' => 'nullable|string',
    //         'Division' => 'nullable|string',
    //         'Section' => 'nullable|string',
    //         'Unit' => 'nullable|string',
    //         'Position' => 'sometimes|required|string',
    //         'PositionID' => 'nullable|integer',
    //         'isOpen' => 'boolean',
    //         'post_date' => 'nullable|date',
    //         'end_date' => 'nullable|date',
    //         'PageNo' => 'nullable|string',
    //         'ItemNo' => 'nullable|string',
    //         'SalaryGrade' => 'nullable|string',
    //         'salaryMin' => 'nullable|string',
    //         'salaryMax' => 'nullable|string',
    //         'level' => 'nullable|string', // Changed to string
    //     ]);

    //     $jobBatch->update($validated);

    //     return response()->json($jobBatch);
    // }

    // Delete
    public function destroy($id)
    {
        $jobBatch = JobBatchesRsp::findOrFail($id);
        $jobBatch->delete();

        return response()->json([
            'message' => 'deleted successfully',
            'jobBatch' => $jobBatch,
        ]);
    }

    public function get_applicant($id)
    {
        // Fetch applicants for the given job post
        $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
            ->with([
            'nPersonalInfo.education',
            'nPersonalInfo.work_experience',
            'nPersonalInfo.training',
            'nPersonalInfo.eligibity',
            'nPersonalInfo.family',
            'nPersonalInfo.children',
            'nPersonalInfo.personal_declarations',
            'nPersonalInfo.skills',
            'nPersonalInfo.voluntary_work',
            ])
            ->get();

        $applicants = $qualifiedApplicants->map(function ($submission) {
            return [
                'id' => $submission->id,
                'job_batches_rsp_id' => $submission->job_batches_rsp_id,
                'status' => $submission->status,
                'n_personal_info' => $submission->nPersonalInfo,
            ];
        });

        return response()->json([
            'status' => true,
            'applicants' => $applicants,
        ]);
    }
}

