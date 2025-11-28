<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use App\Models\rating_score;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\OnCriteriaJob;
use App\Models\OnFundedPlantilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\vwplantillastructure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class JobBatchesRspController extends Controller
{

    public function unoccupied(Request $request, $JobPostingId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Unoccupied',
        ]);

        $jobPost = JobBatchesRsp::findOrFail($JobPostingId);
        $jobPost->update([
            'status' => $validated['status'],
        ]);


        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            activity('Job Post Status Update')
                ->causedBy($user)
                ->performedOn($jobPost)
                ->withProperties([
                    'name'   => $user->name,
                    'username'     => $user->username,
                    'job_post_id'  => $jobPost->id,
                    'position'     => $jobPost->Position ?? null,
                    'item_no'      => $jobPost->ItemNo ?? null,
                    'old_status'   => $jobPost->getOriginal('status'),
                    'new_status'   => $validated['status'],
                    'ip'           => request()->ip(),
                    'user_agent'   => request()->header('User-Agent'),
                ])
                ->log("{$user->name} marked job post {$jobPost->Position} as Unoccupied.");
        }


        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $jobPost,
        ]);
    }

    public function index() //  this function fetching the only didnit meet the end_date
    {
        // Only fetch jobs where end_post is today or later (still active)
        $today = Carbon::today();
        $activeJobs = JobBatchesRsp::whereDate('end_date', '>=', $today)
            ->orderBy('post_date', 'asc')
            ->whereNotIn('status', ['unoccupied', 'occupied', 'republished'])
            ->get();

        return response()->json($activeJobs);
    }


    public function jobPost()
    {
        // 🔹 Fetch job posts EXCLUDING republished ones
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status','end_date', 'tblStructureDetails_ID')
            ->whereRaw('LOWER(status) != ?', ['republished']) // ✅ exclude republished
            ->withCount([
                'submissions as total_applicants',
                'submissions as qualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['qualified']);
                },
                'submissions as unqualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['unqualified']);
                },
                'submissions as pending_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['pending']);
                },
                'submissions as hired_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->get();

        foreach ($jobPosts as $job) {
            $originalStatus = strtolower($job->status);
            $newStatus = $originalStatus;

            // Skip manual statuses (do not override)
            $manualStatuses = ['unoccupied', 'occupied', 'closed', 'republished'];
            if (in_array($originalStatus, $manualStatuses)) {
                continue;
            }

            // ✅ Check if all raters have completed their rating
            $allRatersComplete = \App\Models\Job_batches_user::where('job_batches_rsp_id', $job->id)
                ->exists() &&
                !\App\Models\Job_batches_user::where('job_batches_rsp_id', $job->id)
                    ->where('status', '!=', 'complete')
                    ->exists();

            if ($allRatersComplete) {
                $newStatus = 'rated';
            } elseif ($job->hired_count >= 1) {
                $newStatus = 'occupied';
            } elseif ($job->qualified_count > 0 || $job->unqualified_count > 0) {
                $newStatus = $job->pending_count > 0 ? 'pending' : 'assessed';
            } else {
                $newStatus = 'not started';
            }

            // ✅ Update only if changed
            if ($originalStatus !== $newStatus) {
                $job->status = $newStatus;
                $job->save();
            }
        }

        // 🔄 Reload updated list (still excluding republished)
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status' ,'end_date' ,'tblStructureDetails_ID')
            ->whereRaw('LOWER(status) != ?', ['republished']) // ✅ exclude republished again
            ->withCount([
                'submissions as total_applicants',
                'submissions as qualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['qualified']);
                },
                'submissions as unqualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['unqualified']);
                },
                'submissions as pending_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['pending']);
                },
                'submissions as hired_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->get();

        return response()->json($jobPosts);
    }



    public function jobList()

    {
        // Get all job posts with criteria and assigned raters
        $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position', 'PositionID', 'ItemNo')
        ->where('status', '!=', 'occupied') //
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

    // Delete
    public function destroy($id)
    {
        $jobBatch = JobBatchesRsp::findOrFail($id);

        $jobData = [
            'id'        => $jobBatch->id,
            'position'  => $jobBatch->Position ?? null,
            'item_no'   => $jobBatch->ItemNo ?? null,
            'office'    => $jobBatch->Office ?? null,
            'page_no'   => $jobBatch->PageNo ?? null,
            'status'    => $jobBatch->status ?? null,
        ];

        $jobBatch->delete();


        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            activity('Delete')
                ->causedBy($user)
                ->performedOn($jobBatch)
                ->withProperties([
                    'name'   => $user->name,
                    'username'     => $user->username,
                    'deleted_job'  => $jobData,         // job post details before delete
                    'ip'           => request()->ip(),
                    'user_agent'   => request()->header('User-Agent'),
                ])
                ->log("{$user->name} deleted job post ({$jobBatch->Position}).");
        }

        return response()->json([
            'message' => 'deleted successfully',
            'jobBatch' => $jobBatch,
        ]);
    }

    public function getApplicant($id)
    {
        // All submissions for this job post
        $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
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
                    'image_path' => $employeeJson['User'][0]['Pics'] ?? $employeeJson['User'][0]['image_path'] ?? null,
                ];
            }

            // Generate image URL
            $imageUrl = null;
            if ($info && isset($info['image_path']) && $info['image_path']) {
                if (Storage::disk('public')->exists($info['image_path'])) {
                    $baseUrl = config('app.url');
                    $imageUrl = $baseUrl . '/storage/' . $info['image_path'];
                }
            }


            return [
                'submission_id' => $submission->id,
                'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                'ControlNo' => $submission->ControlNo,
                'job_batches_rsp_id' => $submission->job_batches_rsp_id,
                'status' => $submission->status,
                'firstname' => $info['firstname'] ?? '',
                'lastname' => $info['lastname'] ?? '',
                'application_date' => $info['application_date']
                    ?? ($info instanceof \App\Models\excel\nPersonal_info
                        ? optional($info->created_at)->toDateString()
                        : (!empty($info['created_at'])
                            ? \Carbon\Carbon::parse($info['created_at'])->toDateString()
                            : ($submission->created_at
                                ? $submission->created_at->toDateString()
                                : null))),


                'image_url' => $imageUrl,

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


    public function jobPostView($job_post_id)
    {
        // ✅ Fetch job post with relations
        $job_post = JobBatchesRsp::with(['criteria', 'plantilla'])
            ->withCount([
                'submissions as total_applicants',
                'submissions as qualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['qualified']);
                },
                'submissions as unqualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['unqualified']);
                },
                'submissions as pending_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['pending']);
                },
                'submissions as hired_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->findOrFail($job_post_id);

        // ✅ Check if all raters completed their rating
        $allRatersComplete = \App\Models\Job_batches_user::where('job_batches_rsp_id', $job_post->id)
            ->exists() &&
            !\App\Models\Job_batches_user::where('job_batches_rsp_id', $job_post->id)
                ->where('status', '!=', 'complete')
                ->exists();

        $originalStatus = strtolower($job_post->status);
        $newStatus = $originalStatus;

        // ✅ Skip manual statuses
        $manualStatuses = ['unoccupied', 'occupied', 'closed', 'republished'];
        if (!in_array($originalStatus, $manualStatuses)) {
            if ($allRatersComplete) {
                $newStatus = 'rated';
            } elseif ($job_post->hired_count >= 1) {
                $newStatus = 'occupied';
            } elseif ($job_post->qualified_count > 0 || $job_post->unqualified_count > 0) {
                // ✅ If there’s at least one qualified or unqualified applicant
                $newStatus = $job_post->pending_count > 0 ? 'pending' : 'assessed';
            } else {
                $newStatus = 'not started';
            }

            // ✅ Update only if status changed
            if ($originalStatus !== $newStatus) {
                $job_post->status = $newStatus;
                $job_post->save();
            }
        }

        // ✅ Get complete history (both previous and next reposts)
        $history = $this->getFullJobHistory($job_post);

        // ✅ Convert to array and clean up nested relations
        $job_post_array = $job_post->toArray();
        unset($job_post_array['previous_job'], $job_post_array['next_job']);

        // ✅ Return structured response
        return response()->json(array_merge($job_post_array, [
            'history' => $history,
        ]));
    }


    private function getFullJobHistory($job)
    {
        $history = [];

        // 1️⃣ Go backwards (older reposts)
        $current = $job;
        while ($current->previousJob) {
            $current = $current->previousJob;
        }

        // 2️⃣ From the oldest, go forward (to latest reposts)
        while ($current) {
            $history[] = [
                'id' => $current->id,
                'post_date' => $current->post_date,
                'end_date' => $current->end_date,
            ];

            $current = $current->nextJob ?? null; // move forward
        }

        return $history; // always ordered oldest → latest
    }


    public function store(Request $request)
    {
        // Validate basic fields for job batch
        $jobValidated = $request->validate([
            'Office' => 'nullable|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'required|nullable|date',
            'end_date' => 'required|nullable|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string',
            'tblStructureDetails_ID' => 'nullable|string',
        ]);

        // Validate criteria if present
        $criteriaValidated = $request->only(['Education', 'Eligibility', 'Training', 'Experience']);

        // Validate file if present
        if ($request->hasFile('fileUpload')) {
            $fileValidated = $request->validate([
                'fileUpload' => 'required|mimes:pdf|max:5120',
            ]);
        }

        // --- Step 1: Update PageNo if PageNo exists ---
        if ($request->has('PageNo') && $jobValidated['tblStructureDetails_ID'] && $jobValidated['ItemNo']) {
            $exists = DB::table('tblStructureDetails')
                ->where('PageNo', $jobValidated['PageNo'])
                ->where('ItemNo', $jobValidated['ItemNo'])
                ->where('ID', '<>', $jobValidated['tblStructureDetails_ID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate PageNo and ItemNo already exists in plantilla.'
                ], 422);
            }

            DB::table('tblStructureDetails')
                // ->where('PositionID', $jobValidated['PositionID'])
                ->where('ID', $jobValidated['tblStructureDetails_ID'])
                // ->where('ItemNo', $jobValidated['ItemNo'])
                ->update(['PageNo' => $jobValidated['PageNo']]);
        }

        // --- Step2: Create Job Post if new ---
        $jobBatch = JobBatchesRsp::create($jobValidated);

        // Create criteria if exists
        if (!empty($criteriaValidated)) {
            $criteria = OnCriteriaJob::create([
                'job_batches_rsp_id' => $jobBatch->id,
                'PositionID' => $jobValidated['PositionID'] ?? null,
                'ItemNo' => $jobValidated['ItemNo'] ?? null,
                'Education' => $criteriaValidated['Education'] ?? null,
                'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
                'Training' => $criteriaValidated['Training'] ?? null,
                'Experience' => $criteriaValidated['Experience'] ?? null,
            ]);
        }

        // Handle plantilla and file upload
        $plantilla = new OnFundedPlantilla();
        $plantilla->job_batches_rsp_id = $jobBatch->id;
        $plantilla->PositionID = $jobValidated['PositionID'] ?? null;
        $plantilla->ItemNo = $jobValidated['ItemNo'] ?? null;

        if ($request->hasFile('fileUpload')) {
            $file = $request->file('fileUpload');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        // Log activity for creating a job post
        $user = Auth::user();

        if ($user instanceof \App\Models\User) {
            activity('Create')
                ->causedBy($user)
                ->performedOn($jobBatch)
                ->withProperties([
                    'name' => $user->name,
                    'username' => $user->username,
                    'job_post_id' => $jobBatch->id,
                    'position' => $jobBatch->Position ?? null,
                    'item_no' => $jobBatch->ItemNo ?? null,
                    'page_no' => $jobBatch->PageNo ?? null,
                    'salary_grade' => $jobBatch->SalaryGrade ?? null,
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ])
                ->log("{$user->name} created a new job post for position {$jobBatch->Position}.");
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Job post processed successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria ?? null,
            'plantilla' => $plantilla
        ], 201);
    }

    public function jobPostUpdate(Request $request, $jobBatchId)
    {
        // 1️⃣ Validate job batch fields
        $jobValidated = $request->validate([

            'post_date' => 'required|nullable|date',
            'end_date' => 'required|nullable|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'PositionID' => 'required|string',
            'tblStructureDetails_ID' => 'required|string',
        ]);

        // 2️⃣ Validate criteria if present
        $criteriaValidated = $request->only(['Education', 'Eligibility', 'Training', 'Experience']);

        // 3️⃣ Validate file if present
        if ($request->hasFile('fileUpload')) {
            $fileValidated = $request->validate([
                'fileUpload' => 'required|mimes:pdf|max:5120',
            ]);
        }

        // 4️⃣ Check for duplicate PageNo + ItemNo
        if ($request->has('PageNo') && $jobValidated['tblStructureDetails_ID'] && $jobValidated['ItemNo']) {
            $exists = DB::table('tblStructureDetails')
                ->where('PageNo', $jobValidated['PageNo'])
                ->where('ItemNo', $jobValidated['ItemNo'])
                ->where('ID', '<>', $jobValidated['tblStructureDetails_ID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate PageNo and ItemNo already exists in plantilla Please try again.'
                ], 422);
            }

            // Update PageNo in tblStructureDetails
            DB::table('tblStructureDetails')
                ->where('ID', $jobValidated['tblStructureDetails_ID'])
                // ->where('ItemNo', $jobValidated['ItemNo'])
                ->update(['PageNo' => $jobValidated['PageNo']]);
        }

        // 5️⃣ Update Job Batch
        $jobBatch = JobBatchesRsp::findOrFail($jobBatchId);
        $jobBatch->update($jobValidated);

        // 6️⃣ Update criteria if exists
        if (!empty($criteriaValidated)) {
            $criteria = OnCriteriaJob::updateOrCreate(
                ['job_batches_rsp_id' => $jobBatch->id],
                [
                    'PositionID' => $jobValidated['PositionID'] ?? null,
                    'ItemNo' => $jobValidated['ItemNo'] ?? null,
                    'Education' => $criteriaValidated['Education'] ?? null,
                    'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
                    'Training' => $criteriaValidated['Training'] ?? null,
                    'Experience' => $criteriaValidated['Experience'] ?? null,
                ]
            );
        }

        // 7️⃣ Update plantilla and file if exists
        $plantilla = OnFundedPlantilla::firstOrNew(['job_batches_rsp_id' => $jobBatch->id]);
        $plantilla->PositionID = $jobValidated['PositionID'] ?? null;
        $plantilla->ItemNo = $jobValidated['ItemNo'] ?? null;

        if ($request->hasFile('fileUpload')) {
            $file = $request->file('fileUpload');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        $user = Auth::user();
        activity('Update')
            ->causedBy($user)
            ->performedOn($jobBatch)
            ->withProperties([
                'name' => $user->name ?? null,
                'username' => $user->username ?? null,
                'job_post_id' => $jobBatch->id,
                'updated_fields' => $jobValidated,
                'criteria_updated' => $criteriaValidated ?? null,
                'file_uploaded' => $request->hasFile('fileUpload') ? $fileName : 'No file uploaded',
            ])
            ->log("{$user->name} Update  the job post for position {$jobBatch->Position}.");

        return response()->json([
            'status' => 'success',
            'message' => 'Job post updated successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria ?? null,
            'plantilla' => $plantilla
        ], 200);
    }


    public function republished(Request $request)
    {
        // ✅ Step 1: Validate Job Batch fields
        $jobValidated = $request->validate([
            'Office' => 'required|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'required|date',
            'end_date' => 'required|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string',
            'tblStructureDetails_ID' => 'required|string',
            'old_job_id' => 'required|integer',
        ]);

        // ✅ Step 2: Validate criteria fields
        $criteriaValidated = $request->validate([
            'Education' => 'nullable|string',
            'Eligibility' => 'nullable|string',
            'Training' => 'nullable|string',
            'Experience' => 'nullable|string',
        ]);

        // ✅ Step 3: Validate file (required even if not in JobBatchesRsp)
        $fileValidated = $request->validate([
            'fileUpload' => 'required|mimes:pdf|max:5120',
        ]);

        // ✅ Step 4: Mark old job as Republished
        JobBatchesRsp::where('id', $jobValidated['old_job_id'])
            ->update(['status' => 'Republished']);

        // ✅ Step 5: Create new Job Post
        $jobBatch = JobBatchesRsp::create($jobValidated);

        // ✅ Step 6: Create new Criteria
        $criteria = OnCriteriaJob::create([
            'job_batches_rsp_id' => $jobBatch->id,
            'PositionID' => $jobValidated['PositionID'] ?? null,
            'ItemNo' => $jobValidated['ItemNo'] ?? null,
            'Education' => $criteriaValidated['Education'] ?? null,
            'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
            'Training' => $criteriaValidated['Training'] ?? null,
            'Experience' => $criteriaValidated['Experience'] ?? null,
        ]);

        // ✅ Step 7: Handle plantilla and file upload
        $file = $request->file('fileUpload');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('plantilla_files', $fileName, 'public');

        $plantilla = OnFundedPlantilla::create([
            'job_batches_rsp_id' => $jobBatch->id,
            'PositionID' => $jobValidated['PositionID'] ?? null,
            'ItemNo' => $jobValidated['ItemNo'] ?? null,
            'fileUpload' => $filePath,
        ]);

        $user = Auth::user();
        activity('Republished')
            ->causedBy($user)
            ->performedOn($jobBatch)
            ->withProperties([
                'name' => $user->name,
                'username' => $user->username,
                'new_job_post_id' => $jobBatch->id,
                'old_job_post_id' => $jobValidated['old_job_id'],
                'criteria' => $criteriaValidated,
                'file_uploaded' => $fileName,
            ])
            // ->log('Republished a job post');
            ->log("{$user->name} Republished the job post for position {$jobBatch->Position}.");

        // ✅ Step 8: Return response
        return response()->json([
            'status' => 'success',
            'message' => 'Job post republished successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria,
            'plantilla' => $plantilla
        ], 201);
    }

    public function getApplicantPds($id)
    {
        // 🔹 Fetch the submission by its ID
        $submission = Submission::find($id);

        if (!$submission) {
            return response()->json([
                'status' => false,
                'message' => 'Applicant not found.'
            ], 404);
        }

        $info = $submission->nPersonalInfo;

        // If no local personal info but has ControlNo — fetch from employee DB
        if (!$info && $submission->ControlNo) {
            $xPDS = new \App\Http\Controllers\xPDSController();
            $employeeData = $xPDS->getPersonalDataSheet(
                new \Illuminate\Http\Request(['controlno' => $submission->ControlNo])
            );
            $employeeJson = $employeeData->getData(true);

            $info = [
                'controlno' => $submission->ControlNo,
                'firstname' => $employeeJson['User'][0]['Firstname'] ?? '',
                'lastname' => $employeeJson['User'][0]['Surname'] ?? '',
                'middlename' => $employeeJson['User'][0]['MIddlename'] ?? '',
                'name_extension' => $employeeJson['User'][0]['NameExtension'] ?? null,
                'image_path' => $employeeJson['User'][0]['Pics'] ?? $employeeJson['User'][0]['image_path'] ?? null,
                'date_of_birth' => $employeeJson['User'][0]['BirthDate'] ?? 'N/A',
                'place_of_birth' => $employeeJson['User'][0]['BirthPlace'] ?? 'N/A',
                'sex' => $employeeJson['User'][0]['Sex'] ?? 'N/A',
                'civil_status' => $employeeJson['User'][0]['CivilStatus'] ?? 'N/A',
                'height' => $employeeJson['User'][0]['Heights'] ?? 'N/A',
                'weight' => $employeeJson['User'][0]['Weights'] ?? 'N/A',
                'blood_type' => $employeeJson['User'][0]['BloodType'] ?? 'N/A',
                'telephone_number' => $employeeJson['User'][0]['TelNo'] ?? 'N/A',
                'email_address' => $employeeJson['User'][0]['EmailAdd'] ?? 'N/A',
                'cellphone_number' => $employeeJson['User'][0]['CellphoneNo'] ?? 'N/A',
                'tin_no' => $employeeJson['User'][0]['TINNo'] ?? 'N/A',
                'gsis_no' => $employeeJson['User'][0]['GSISNo'] ?? 'N/A',
                'pagibig_no' => $employeeJson['User'][0]['PAGIBIGNo'] ?? 'N/A',
                'sss_no' => $employeeJson['User'][0]['SSSNo'] ?? 'N/A',
                'philhealth_no' => $employeeJson['User'][0]['PHEALTHNo'] ?? 'N/A',
                'agency_employee_no' => $employeeJson['User'][0]['ControlNo'] ?? 'N/A',
                'citizenship' => $employeeJson['User'][0]['Citizenship'] ?? 'N/A',
                'religion' => $employeeJson['User'][0]['Religion'] ?? 'N/A',



                // Residential address
                'residential_house' => $employeeJson['User'][0]['Rhouse'] ?? null,
                'residential_street' => $employeeJson['User'][0]['Rstreet'] ?? null,
                'residential_subdivision' => $employeeJson['User'][0]['Rsubdivision'] ?? null,
                'residential_barangay' => $employeeJson['User'][0]['Rbarangay'] ?? null,
                'residential_city' => $employeeJson['User'][0]['Rcity'] ?? null,
                'residential_province' => $employeeJson['User'][0]['Rprovince'] ?? null,
                'residential_region' => $employeeJson['User'][0]['Rregion'] ?? null,
                'residential_zip' => $employeeJson['User'][0]['Rzip'] ?? null,

                // Permanent address
                'permanent_region' => $employeeJson['User'][0]['Pregion'] ?? null,
                'permanent_house' => $employeeJson['User'][0]['Phouse'] ?? null,
                'permanent_street' => $employeeJson['User'][0]['Pstreet'] ?? null,
                'permanent_subdivision' => $employeeJson['User'][0]['Psubdivision'] ?? null,
                'permanent_barangay' => $employeeJson['User'][0]['Pbarangay'] ?? null,
                'permanent_city' => $employeeJson['User'][0]['Pcity'] ?? null,
                'permanent_province' => $employeeJson['User'][0]['Pprovince'] ?? null,
                'permanent_zip' => $employeeJson['User'][0]['Pzip'] ?? null,

                'education' => $employeeJson['Education'] ?? [],
                'eligibity' => $employeeJson['Eligibility'] ?? [],
                'training' => $employeeJson['Training'] ?? [],
                'work_experience' => $employeeJson['Experience'] ?? [],
                'voluntary_work' => $employeeJson['Voluntary'] ?? [],
                'skills' => $employeeJson['Skills'] ?? [],
                'Academic' => $employeeJson['Academic'] ?? [],
                'Organization' => $employeeJson['Organization'] ?? [],
                'reference' => $employeeJson['Reference'] ?? [],
                'children' => collect($employeeJson['User'][0]['children'] ?? [])->map(function ($child) {
                    return [
                        'child_name' => $child['ChildName'] ?? $child['child_name'] ?? null,
                        'birth_date' => $child['BirthDate'] ?? $child['birth_date'] ?? null,
                    ];
                })->toArray(),
                'family' => [], // optional
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
        $trainingImages = [];
        $educationImages = [];
        $eligibilityImages = [];
        $experienceImages = [];

        if ($info && isset($info['id'])) {
            $baseFolder = storage_path('app/public/applicant_files/' . $submission->nPersonalInfo_id);

            $folders = [
                'training' => $baseFolder . '/document/training',
                'education' => $baseFolder . '/document/education',
                'eligibility' => $baseFolder . '/document/eligibility',
                'experience' => $baseFolder . '/document/experience',
            ];

            foreach ($folders as $type => $path) {
                if (is_dir($path)) {
                    $files = collect(scandir($path))
                        ->filter(fn($file) => !in_array($file, ['.', '..']))
                        ->map(fn($file) => asset('storage/applicant_files/' . $info['id'] . '/document/' . $type . '/' . $file))
                        ->values()
                        ->toArray();

                    if ($type === 'training') $trainingImages = $files;
                    if ($type === 'education') $educationImages = $files;
                    if ($type === 'eligibility') $eligibilityImages = $files;
                    if ($type === 'experience') $experienceImages = $files;
                }
            }
        }
        return response()->json([
            // 'applicant_id' => $submission->id,
            'applicant_id' => $submission->id,

            'status' => $submission->status,
            'education_remark' => $submission->education_remark,
            'experience_remark' => $submission->experience_remark,
            'training_remark' => $submission->training_remark,
            'eligibility_remark' => $submission->eligibility_remark,
            'education_qualification' => $submission->education_qualification,
            'experience_qualification' => $submission->experience_qualification,
            'training_qualification' => $submission->training_qualification,
            'eligibility_qualification' => $submission->eligibility_qualification,

            'ControlNo' => $submission->ControlNo,
            'nPersonalInfo_id' => $submission->nPersonalInfo_id,
            'firstname' => $info['firstname'] ?? '',
            'lastname' => $info['lastname'] ?? '',
            'name_extension' => $info['name_extension'] ?? null,
            'image_path' => $info['image_path'] ?? null,
            'image_url' => $imageUrl,

            'date_of_birth' => $info['date_of_birth'] ?? null,
            'place_of_birth' => $info['place_of_birth'] ?? null,
            'sex' => $info['sex'] ?? null,
            'civil_status' => $info['civil_status'] ?? null,
            'height' => $info['height'] ?? null,
            'weight' => $info['weight'] ?? null,
            'blood_type' => $info['blood_type'] ?? null,
            'telephone_number' => $info['telephone_number'] ?? null,
            'email_address' => $info['email_address'] ?? null,
            'cellphone_number' => $info['cellphone_number'] ?? null,
            'tin_no' => $info['tin_no'] ?? null,
            'gsis_no' => $info['gsis_no'] ?? null,
            'pagibig_no'=> $info['pagibig_no'] ?? null,
            'sss_no' => $info['sss_no'] ?? null,
            'philhealth_no' => $info['philhealth_no'] ?? null,
            'agency_employee_no' => $info['agency_employee_no'] ?? null,
            'citizenship' => $info['citizenship'] ?? null,
            'religion' => $info['religion'] ?? null,


            // Permanent Address
            'permanent_house' => $info['permanent_house'] ?? null,
            'permanent_street' => $info['permanent_street'] ?? null,
            'permanent_subdivision' => $info['permanent_subdivision'] ?? null,
            'permanent_barangay' => $info['permanent_barangay'] ?? null,
            'permanent_city' => $info['permanent_city'] ?? null,
            'permanent_province' => $info['permanent_province'] ?? null,
            'permanent_region' => $info['permanent_region'] ?? null,
            'permanent_zip' => $info['permanent_zip'] ?? null,

            // Residential Address
            'residential_house' => $info['residential_house'] ?? null,
            'residential_street' => $info['residential_street'] ?? null,
            'residential_subdivision' => $info['residential_subdivision'] ?? null,
            'residential_barangay' => $info['residential_barangay'] ?? null,
            'residential_city' => $info['residential_city'] ?? null,
            'residential_province' => $info['residential_province'] ?? null,
            'residential_region' => $info['residential_region'] ?? null,
            'residential_zip' => $info['residential_zip'] ?? null,

            'education' => $info['education'] ?? [],
            'training' => $info['training'] ?? [],
            'eligibity' => $info['eligibity'] ?? [],
            'work_experience' => $info['work_experience'] ?? [],
            'voluntary_work' => $info['voluntary_work'] ?? [],
            'skills' => $info['skills'] ?? [],
            'Academic' => $info['Academic'] ?? [],
            'Organization' => $info['Organization'] ?? [],
            'children' => $info['children'] ?? [],
            'family' => $info['family'] ?? [],
            'reference' => $info['reference'] ?? [],
            'training_images' => $trainingImages,
            'education_images' => $educationImages,
            'eligibility_images' => $eligibilityImages,
            'experience_images' => $experienceImages,
            'ranking' => $rating->ranking ?? null,
        ]);
    }
}
