<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\OnCriteriaJob;
use App\Models\rating_score;
use Illuminate\Http\JsonResponse;


class JobBatchesRspController extends Controller
{
    public function index() //  this function fetching the only didnit meet the end_date
    {
        // Only fetch jobs where end_post is today or later (still active)
        $today = Carbon::today();
        $activeJobs = JobBatchesRsp::whereDate('end_date', '>=', $today)
            ->orderBy('post_date', 'asc') // Optional: you can change this to 'created_at' if preferred
            ->get();

        return response()->json($activeJobs);
    }

    // public function job_post()
    // {
    //     $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date','Office', 'PositionID','ItemNo','status')
    //         ->withCount([
    //             'applicants as total_applicants',
    //             'applicants as qualified_count' => function ($query) {
    //                 $query->where('status', 'qualified');
    //             },
    //             'applicants as unqualified_count' => function ($query) {
    //                 $query->where('status', 'unqualified');
    //             },
    //             'applicants as pending_count' => function ($query) {
    //                 $query->where('status', 'pending');
    //             },
    //         ])
    //         ->get();

    //     return response()->json($jobPosts);
    // }


    public function job_post()
    {
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status')
            ->withCount([
                'applicants as total_applicants',
                'applicants as qualified_count' => function ($query) {
                    $query->where('status', 'qualified');
                },
                'applicants as unqualified_count' => function ($query) {
                    $query->where('status', 'unqualified');
                },
                'applicants as pending_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->get();

        // Loop and update status if needed
        foreach ($jobPosts as $job) {
            $originalStatus = $job->status;

            if ($job->qualified_count > 0 || $job->unqualified_count > 0) {
                if ($job->pending_count > 0) {
                    $newStatus = 'pending';
                } else {
                    $newStatus = 'assessed';
                }
            } else {
                $newStatus = 'not started';
            }

            // Save only if status changed
            if ($originalStatus !== $newStatus) {
                $job->status = $newStatus;
                $job->save();
            }
        }

        // Optionally, refresh the collection to get the updated statuses
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status')
            ->withCount([
                'applicants as total_applicants',
                'applicants as qualified_count' => function ($query) {
                    $query->where('status', 'qualified');
                },
                'applicants as unqualified_count' => function ($query) {
                    $query->where('status', 'unqualified');
                },
                'applicants as pending_count' => function ($query) {
                    $query->where('status', 'pending');
                },
            ])
            ->get();



        return response()->json($jobPosts);
    }
    public function job_list()
    {
        // Get all job posts with criteria and assigned raters
        $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position') // Include only user id and name
            ->get();

        // Add 'status' and 'assigned_raters' to each job
        $jobsWithDetails = $jobs->map(function ($job) {
            $job->status = $job->criteriaRatings->isNotEmpty() ? 'created' : 'no criteria';
            $job->assigned_raters = $job->users; // Include users as assigned raters
            unset($job->users); // Optionally remove the original 'users' relation if not needed directly
            return $job;
        });

        return response()->json($jobsWithDetails);
    }

    // public function job_list()
    // {
    //     // Get all job posts with criteria and assigned raters
    //     $jobs = JobBatchesRsp::with([
    //         'criteriaRatings:id,job_batches_rsp_id,status', // only fetch needed fields
    //         'users:id,name'
    //     ])
    //         ->select('id', 'office', 'isOpen', 'Position')
    //         ->get();

    //     $jobsWithDetails = $jobs->map(function ($job) {
    //         // Use the first criteria (if there), or set status to "no criteria"
    //         if ($job->criteriaRatings->isNotEmpty()) {
    //             // If you allow only one criteria per job, just use first
    //             $criteria = $job->criteriaRatings->first();
    //             $job->status = $criteria->status ?? 'created';
    //         } else {
    //             $job->status = 'no criteria';
    //         }
    //         $job->assigned_raters = $job->users; // keep assigned raters
    //         unset($job->users); // optional: remove users relation from output
    //         unset($job->criteriaRatings); // optional: hide raw criteriaRatings
    //         return $job;
    //     });

    //     return response()->json($jobsWithDetails);
    // }

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
    // public function show($PositionID, $ItemNo)
    // {
    //     // Ensure you have `use Illuminate\Support\Facades\DB;` at the top of your file.
    //     $jobBatches = \Illuminate\Support\Facades\DB::select('SELECT * FROM job_batches_rsp WHERE PositionID = ? AND ItemNo = ?', [$PositionID, $ItemNo]);

    //     if (empty($jobBatches)) {
    //         return response()->json(['error' => 'No matching record found'], 404);
    //     }

    //     // DB::select returns an array of objects, so we take the first one.
    //     return response()->json($jobBatches[0]);
    // }

    public function show($positionId, $itemNo): JsonResponse
    {
        $jobBatch = JobBatchesRsp::where('PositionID', $positionId)
            ->where('ItemNo', $itemNo)
            ->first();

        if (!$jobBatch) {
            return response()->json(['error' => 'No matching record found'], 404);
        }

        return response()->json($jobBatch);
    }

    // public function show($positionId, $itemNo): JsonResponse
    // {
    //     $jobBatch = JobBatchesRsp::where('PositionID', $positionId)
    //         ->where('ItemNo', $itemNo)
    //         ->first();

    //     if (!$jobBatch) {
    //         return response()->json(['error' => 'No matching job batch found'], 404);
    //     }

    //     $criteria = OnCriteriaJob::where('PositionID', $positionId)
    //         ->where('ItemNo', $itemNo)
    //         ->get(); // use `get()` if multiple criteria per job batch

    //     return response()->json([
    //         'job_batch' => $jobBatch,
    //         'criteria' => $criteria
    //     ]);
    // }

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


    // public function get_applicant($id)
    // {
    //     // Fetch applicants for the given job post with only needed fields
    //     $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
    //         ->with(['nPersonalInfo:id,firstname,lastname,name_extension']) // Eager load only firstname and lastname
    //         ->get(['id', 'job_batches_rsp_id', 'status', 'nPersonalInfo_id', 'created_at', 'ranking']); // Include rank and created_at if they exist in submissions table

    //     $applicants = $qualifiedApplicants->map(function ($submission) {
    //         return [
    //             'id' => $submission->nPersonalInfo->id ?? null,
    //             'firstname' => $submission->nPersonalInfo->firstname ?? null,
    //             'lastname' => $submission->nPersonalInfo->lastname ?? null,
    //             'name_extension' => $submission->nPersonalInfo->name_extension ?? null,
    //             'application_date' => $submission->created_at->toDateString(), // or ->format('Y-m-d H:i:s') if needed
    //             'status' => $submission->status,
    //             'ranking' => $submission->ranking,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'applicants' => $applicants,
    //     ]);
    // }
    // public function get_applicant($id)
    // {
    //     $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
    //         ->with([
    //             // 'nPersonalInfo.nPersonalInfo',
    //             'nPersonalInfo.education',
    //             'nPersonalInfo.work_experience',
    //             'nPersonalInfo.training',
    //             'nPersonalInfo.eligibity',
    //             'nPersonalInfo.family',
    //             'nPersonalInfo.children',
    //             'nPersonalInfo.personal_declarations',
    //             'nPersonalInfo.skills',
    //             'nPersonalInfo.voluntary_work',
    //             'nPersonalInfo.reference'
    //         ])
    //         ->get();

    //     // $applicants = $qualifiedApplicants->map(function ($submission) {
    //     //     $info = $submission->nPersonalInfo;

    //     $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
    //         $info = $submission->nPersonalInfo;
    //         // Fetch ranking from rating_score
    //         $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
    //             ->where('job_batches_rsp_id', $id)
    //             ->first();

    //         return [
    //             'id' => $submission->id,
    //             'nPersonalInfo_id' => $submission->nPersonalInfo_id,
    //             'job_batches_rsp_id' => $submission->job_batches_rsp_id,
    //             'status' => $submission->status,
    //             'education_remark' => $submission->education_remark,
    //             'experience_remark' => $submission->experience_remark,
    //             'training_remark' => $submission->training_remark,
    //             'eligibility_remark' => $submission->eligibility_remark,
    //             'controlno' => $info->controlno ?? null,
    //             'firstname' => $info->firstname ?? '',
    //             'lastname' => $info->lastname ?? '',
    //             'name_extension' => $info->name_extension ?? '',
    //             'image_path' => $info->image_path ?? null,
    //             'application_date' => $info->created_at ? $info->created_at->toDateString() : null,

    //             // Add all related applicant details
    //             'nPersonalInfo' => $info ?? [],
    //             'education' => $info->education ?? [],
    //             'work_experience' => $info->work_experience ?? [],
    //             'training' => $info->training ?? [],
    //             'eligibity' => $info->eligibity ?? [],
    //             'family' => $info->family ?? [],
    //             'children' => $info->children ?? [],
    //             'personal_declarations' => $info->personal_declarations ?? [],
    //             'reference' => $info->reference ?? [],
    //             'skills' => $info->skills ?? [],
    //             'voluntary_work' => $info->voluntary_work ?? [],
    //             // Add rank from rating_score
    //             'ranking' => $rating->ranking ?? null,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'applicants' => $applicants,
    //     ]);
    // }

    public function get_applicant($id)
{
    // All submissions for this job post
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
            'nPersonalInfo.reference'
        ])
        ->get();

    // Count all applicants for this job post
    $totalApplicants = $qualifiedApplicants->count();

    // Count applicants with qualified OR unqualified status
    $progressCount = $qualifiedApplicants->whereIn('status', ['qualified', 'unqualified'])->count();

    $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
        $info = $submission->nPersonalInfo;
        // Fetch ranking from rating_score
        $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
            ->where('job_batches_rsp_id', $id)
            ->first();

        return [
            'id' => $submission->id,
            'nPersonalInfo_id' => $submission->nPersonalInfo_id,
            'job_batches_rsp_id' => $submission->job_batches_rsp_id,
            'status' => $submission->status,
            'education_remark' => $submission->education_remark,
            'experience_remark' => $submission->experience_remark,
            'training_remark' => $submission->training_remark,
            'eligibility_remark' => $submission->eligibility_remark,
            'controlno' => $info->controlno ?? null,
            'firstname' => $info->firstname ?? '',
            'lastname' => $info->lastname ?? '',
            'name_extension' => $info->name_extension ?? '',
            'image_path' => $info->image_path ?? null,
            'application_date' => $info->created_at ? $info->created_at->toDateString() : null,
            // Add all related applicant details
            'nPersonalInfo' => $info ?? [],
            'education' => $info->education ?? [],
            'work_experience' => $info->work_experience ?? [],
            'training' => $info->training ?? [],
            'eligibity' => $info->eligibity ?? [],
            'family' => $info->family ?? [],
            'children' => $info->children ?? [],
            'personal_declarations' => $info->personal_declarations ?? [],
            'reference' => $info->reference ?? [],
            'skills' => $info->skills ?? [],
            'voluntary_work' => $info->voluntary_work ?? [],
            // Add rank from rating_score
            'ranking' => $rating->ranking ?? null,
        ];
    });

    return response()->json([
        'status' => true,
        'progress' => $progressCount . '/' . $totalApplicants, // e.g. 1/10
        'progress_count' => $progressCount, // just the number completed
        'total_applicants' => $totalApplicants, // just the total
        'applicants' => $applicants,
    ]);
}
}

