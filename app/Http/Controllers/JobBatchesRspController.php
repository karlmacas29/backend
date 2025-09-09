<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use App\Models\rating_score;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\OnCriteriaJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;


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
    //     // All submissions for this job post
    //     $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
    //         ->with([
    //             'nPersonalInfo.education',
    //             'nPersonalInfo.work_experience',
    //             'nPersonalInfo.training',
    //             'nPersonalInfo.eligibity',
    //             'nPersonalInfo.family',
    //             'nPersonalInfo.children',
    //             'nPersonalInfo.personal_declarations',
    //             'nPersonalInfo.skills',
    //             'nPersonalInfo.voluntary_work',
    //             'nPersonalInfo.reference',
    //         ])
    //         ->get();

    //     // Count all applicants for this job post
    //     $totalApplicants = $qualifiedApplicants->count();

    //     // Count applicants with qualified OR unqualified status
    //     $progressCount = $qualifiedApplicants->whereIn('status', ['qualified', 'unqualified'])->count();

    //     $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
    //         $info = $submission->nPersonalInfo;

    //         // ✅ If no nPersonalInfo_id, fetch from Employee DB (via controlno)
    //         if (!$info && $submission->ControlNo) {
    //             $xPDS = new \App\Http\Controllers\xPDSController();
    //             $employeeData = $xPDS->getPersonalDataSheet(new \Illuminate\Http\Request([
    //                 'controlno' => $submission->ControlNo
    //             ]));

    //             $employeeJson = $employeeData->getData(true); // decode JSON response
    //             $info = [
    //                 'controlno' => $submission->ControlNo,
    //                 'firstname' => $employeeJson['User'][0]['Firstname'] ?? '',
    //                 'lastname' => $employeeJson['User'][0]['Surname'] ?? '',
    //                 'middlename' => $employeeJson['User'][0]['MIddlename'] ?? '',
    //                 'name_extension' => $employeeJson['User'][0]['NameExtension'] ?? '',
    //                 'nPersonalInfo' => $employeeJson['User'] ?? [],
    //                 'children' => $employeeJson['User'][0]['children'] ?? [],
    //                 'education' => $employeeJson['Education'] ?? [],
    //                 'eligibity' => $employeeJson['Eligibility'] ?? [],
    //                 'work_experience' => $employeeJson['Experience'] ?? [],
    //                 'training' => $employeeJson['Training'] ?? [],
    //                 'voluntary_work' => $employeeJson['Voluntary'] ?? [],
    //                 'skills' => $employeeJson['Skills'] ?? [],
    //                 'reference' => $employeeJson['Reference'] ?? [],
    //                 'personal_declarations' => [],
    //                 'family' => [],
    //                 'image_path' => null,
    //                 'created_at' => null,
    //             ];
    //         }

    //         // 🔄 Standardize Education Data here
    //         $educationData = collect($info['education'] ?? [])->map(function ($edu) {
    //             if (isset($edu['Education'])) {
    //                 $dates = explode('-', $edu['DateAttend'] ?? '');
    //                 $from = isset($dates[0]) ? trim($dates[0]) : null;
    //                 $to   = isset($dates[1]) ? trim($dates[1]) : null;

    //                 return [
    //                     'level'           => $edu['Education'] ?? null,
    //                     'school_name'     => $edu['School'] ?? null,
    //                     'degree'          => $edu['Degree'] ?? null,
    //                     'attendance_from' => $from,
    //                     'attendance_to'   => $to,
    //                     'year_graduated'  => $to,
    //                     'highest_units'    => $edu['NumUnits'] ?? 0,
    //                     'degree'          => $edu['Degree'] ?? null,
    //                 ];
    //             }

    //             return [
    //                 'level'           => $edu['level'] ?? null,
    //                 'school_name'     => $edu['school_name'] ?? null,
    //                 'degree'          => $edu['degree'] ?? null,
    //                 'highest_units'        => $edu['highest_units'] ?? null,
    //                 'attendance_from' => $edu['attendance_from'] ?? null,
    //                 'attendance_to'   => $edu['attendance_to'] ?? null,
    //                 'year_graduated'  => $edu['year_graduated'] ?? null,
    //             ];
    //         });


    //         // 🔄 Standardize Education Data here
    //         $experienceData = collect($info['experience'] ?? [])->map(function ($exp) {
    //             if (isset($edu['Experience'])) {
    //                 $dates = explode('-', $edu['DateAttend'] ?? '');
    //                 $from = isset($dates[0]) ? trim($dates[0]) : null;
    //                 $to   = isset($dates[1]) ? trim($dates[1]) : null;

    //                 return [
    //                     'level'           => $edu['Education'] ?? null,
    //                     'school_name'     => $edu['School'] ?? null,
    //                     'degree'          => $edu['Degree'] ?? null,
    //                     'attendance_from' => $from,
    //                     'attendance_to'   => $to,
    //                     'year_graduated'  => $to,
    //                     'highest_units'    => $edu['NumUnits'] ?? 0,
    //                     'degree'          => $edu['Degree'] ?? null,
    //                 ];
    //             }

    //             return [
    //                 'level'           => $edu['level'] ?? null,
    //                 'school_name'     => $edu['school_name'] ?? null,
    //                 'degree'          => $edu['degree'] ?? null,
    //                 'highest_units'        => $edu['highest_units'] ?? null,
    //                 'attendance_from' => $edu['attendance_from'] ?? null,
    //                 'attendance_to'   => $edu['attendance_to'] ?? null,
    //                 'year_graduated'  => $edu['year_graduated'] ?? null,
    //             ];
    //         });

    //         // Fetch ranking from rating_score
    //         $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
    //             ->where('job_batches_rsp_id', $id)
    //             ->first();

    //         // Generate image URL
    //         $imageUrl = null;
    //         if ($info && isset($info['image_path']) && $info['image_path']) {
    //             if (Storage::disk('public')->exists($info['image_path'])) {
    //                 $baseUrl = config('app.url');
    //                 $imageUrl = $baseUrl . '/storage/' . $info['image_path'];
    //             }
    //         }

    //         return [
    //             'id' => $submission->id,
    //             'nPersonalInfo_id' => $submission->nPersonalInfo_id,
    //             'ControlNo' => $submission->ControlNo,
    //             'job_batches_rsp_id' => $submission->job_batches_rsp_id,
    //             'status' => $submission->status,
    //             'education_remark' => $submission->education_remark,
    //             'experience_remark' => $submission->experience_remark,
    //             'training_remark' => $submission->training_remark,
    //             'eligibility_remark' => $submission->eligibility_remark,
    //             'controlno' => $info['controlno'] ?? null,
    //             'firstname' => $info['firstname'] ?? '',
    //             'lastname' => $info['lastname'] ?? '',
    //             'name_extension' => $info['name_extension'] ?? '',
    //             'image_path' => $info['image_path'] ?? null,
    //             'image_url' => $imageUrl,
    //             'application_date' => $info['application_date']
    //                 ?? ($info instanceof \App\Models\excel\nPersonal_info
    //                     ? optional($info->created_at)->toDateString()
    //                     : (!empty($info['created_at'])
    //                         ? \Carbon\Carbon::parse($info['created_at'])->toDateString()
    //                         : ($submission->created_at
    //                             ? $submission->created_at->toDateString()
    //                             : null))),
    //             'nPersonalInfo' => $info ?? [],
    //             'education' => $educationData,
    //             'work_experience' => $info['work_experience'] ?? [],
    //             'training' => $info['training'] ?? [],
    //             'eligibity' => $info['eligibity'] ?? [],
    //             'family' => $info['family'] ?? [],
    //             'children' => $info['children'] ?? [],
    //             'personal_declarations' => $info['personal_declarations'] ?? [],
    //             'reference' => $info['reference'] ?? [],
    //             'skills' => $info['skills'] ?? [],
    //             'voluntary_work' => $info['voluntary_work'] ?? [],
    //             'ranking' => $rating->ranking ?? null,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'progress' => $progressCount . '/' . $totalApplicants,
    //         'progress_count' => $progressCount,
    //         'total_applicants' => $totalApplicants,
    //         'applicants' => $applicants,
    //     ]);
    // }


    // public function get_applicant($id)
    // {
    //     // All submissions for this job post
    //     $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
    //         ->with([
    //             'nPersonalInfo.education',
    //             'nPersonalInfo.work_experience',
    //             'nPersonalInfo.training',
    //           'nPersonalInfo.eligibity',
    //             'nPersonalInfo.family',
    //             'nPersonalInfo.children',
    //             'nPersonalInfo.personal_declarations',
    //             'nPersonalInfo.skills',
    //             'nPersonalInfo.voluntary_work',
    //             'nPersonalInfo.reference',

    //         ])
    //         ->get();

    //     // Count all applicants for this job post
    //     $totalApplicants = $qualifiedApplicants->count();

    //     // Count applicants with qualified OR unqualified status
    //     $progressCount = $qualifiedApplicants->whereIn('status', ['qualified', 'unqualified'])->count();

    //     $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
    //         $info = $submission->nPersonalInfo;

    //         // Fetch ranking from rating_score
    //         $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
    //             ->where('job_batches_rsp_id', $id)
    //             ->first();

    //         // Generate image URL
    //         // Generate image URL with correct domain
    //         $imageUrl = null;
    //         if ($info && $info->image_path) {
    //             // Check if image exists in storage
    //             if (Storage::disk('public')->exists($info->image_path)) {
    //                 $baseUrl = config('app.url'); // Get APP_URL from .env
    //                 $imageUrl = $baseUrl . '/storage/' . $info->image_path;
    //             }
    //         }

    //         return [
    //             'id' => $submission->id,
    //             'nPersonalInfo_id' => $submission->nPersonalInfo_id,
    //             'ControlNo' => $submission->ControlNo,
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
    //             'image_path' => $info->image_path ?? null, // Raw path stored in database
    //             'image_path' => $info->image_path ?? null, // Raw path stored in database
    //             'image_url' => $imageUrl, // Fixed URL with correct domain
    //             'application_date' => $info && $info->created_at
    //                 ? $info->created_at->toDateString()
    //                 : null,
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
    //         'progress' => $progressCount . '/' . $totalApplicants, // e.g. 1/10
    //         'progress_count' => $progressCount, // just the number completed
    //         'total_applicants' => $totalApplicants, // just the total
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
                'nPersonalInfo.reference',
            ])
            ->get();

        // Count all applicants for this job post
        $totalApplicants = $qualifiedApplicants->count();

        // Count applicants with qualified OR unqualified status
        $progressCount = $qualifiedApplicants->whereIn('status', ['qualified', 'unqualified'])->count();

        $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
            $info = $submission->nPersonalInfo;

            // ✅ If no nPersonalInfo_id, fetch from Employee DB (via controlno)
            if (!$info && $submission->ControlNo) {
                $xPDS = new \App\Http\Controllers\xPDSController();
                $employeeData = $xPDS->getPersonalDataSheet(new \Illuminate\Http\Request([
                    'controlno' => $submission->ControlNo
                ]));

                $employeeJson = $employeeData->getData(true); // decode JSON response
                $info = [
                    'controlno' => $submission->ControlNo,
                    'firstname' => $employeeJson['User'][0]['Firstname'] ?? '',
                    'lastname' => $employeeJson['User'][0]['Surname'] ?? '',
                    'middlename' => $employeeJson['User'][0]['MIddlename'] ?? '',
                    'name_extension' => $employeeJson['User'][0]['NameExtension'] ?? '',
                    'nPersonalInfo' => $employeeJson['User'] ?? [],
                    'image_path' => null,
                    'created_at' => null,
                ];
            }


            // Fetch ranking from rating_score
            $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
                ->where('job_batches_rsp_id', $id)
                ->first();

            // Generate image URL
            $imageUrl = null;
            if ($info && isset($info['image_path']) && $info['image_path']) {
                if (Storage::disk('public')->exists($info['image_path'])) {
                    $baseUrl = config('app.url');
                    $imageUrl = $baseUrl . '/storage/' . $info['image_path'];
                }
            }

            return [
                'id' => $submission->id,
                'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                'ControlNo' => $submission->ControlNo,
                'job_batches_rsp_id' => $submission->job_batches_rsp_id,
                'status' => $submission->status,
                'education_remark' => $submission->education_remark,
                'experience_remark' => $submission->experience_remark,
                'training_remark' => $submission->training_remark,
                'eligibility_remark' => $submission->eligibility_remark,
                'controlno' => $info['controlno'] ?? null,
                'firstname' => $info['firstname'] ?? '',
                'lastname' => $info['lastname'] ?? '',
                'name_extension' => $info['name_extension'] ?? '',
                'image_path' => $info['image_path'] ?? null,
                'image_url' => $imageUrl,
                'application_date' => $info['application_date']
                    ?? ($info instanceof \App\Models\excel\nPersonal_info
                        ? optional($info->created_at)->toDateString()
                        : (!empty($info['created_at'])
                            ? \Carbon\Carbon::parse($info['created_at'])->toDateString()
                            : ($submission->created_at
                                ? $submission->created_at->toDateString()
                                : null))),
                'nPersonalInfo' => $info ?? [],
                'education' => $info['education'] ?? [],
                'work_experience' => $info['work_experience'] ?? [],
                'training' => $info['training'] ?? [],
                'eligibity' => $info['eligibity'] ?? [],
                'family' => $info['family'] ?? [],
                'children' => $info['children'] ?? [],
                'personal_declarations' => $info['personal_declarations'] ?? [],
                'reference' => $info['reference'] ?? [],
                'skills' => $info['skills'] ?? [],
                'voluntary_work' => $info['voluntary_work'] ?? [],
                'ranking' => $rating->ranking ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'progress' => $progressCount . '/' . $totalApplicants,
            'progress_count' => $progressCount,
            'total_applicants' => $totalApplicants,
            'applicants' => $applicants,
        ]);
    }
}

