<?php

namespace App\Http\Controllers;


use Exception;
use App\Models\User;
use App\Models\Submission;
use App\Models\draft_score;
use App\Models\rating_score;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Services\RatingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\criteria\criteria_rating;
use App\Models\Job_batches_user;
use Illuminate\Support\Facades\Validator;

class RaterController extends Controller
{

    public function view($raterId)
    {
        $rater = User::select('id', 'name', 'position', 'office')
            ->with(['job_batches_rsp' => function ($query) {
                $query->select(
                    'job_batches_rsp.id',
                    'job_batches_rsp.Office',
                    'job_batches_rsp.Position'
                )->withCount('submissions');
            }])
            ->findOrFail($raterId);

        // remove pivot column
        $rater->job_batches_rsp->makeHidden(['pivot']);

        // rename submissions_count → applicant
        $rater->job_batches_rsp->each(function ($job) {
            $job->applicant = $job->submissions_count;
            unset($job->submissions_count);
        });

        return response()->json($rater);
    }



    public function showScoresWithHistory($jobpostId)
    {
        // ✅ Ensure job post exists
        $jobpost = JobBatchesRsp::findOrFail($jobpostId);

        // ✅ Count assigned and completed raters
        $totalAssigned = Job_batches_user::where('job_batches_rsp_id', $jobpostId)->whereHas('user', function ($active) {
            $active->where('active', 1);
        })->count();
        $totalCompleted = Job_batches_user::where('job_batches_rsp_id', $jobpostId)
            ->where('status', 'complete')
            ->count();

        // 🔹 Step 1: Fetch ALL scores (per rater) including ControlNo
        $allScores = rating_score::select(
            'rating_score.id',
            'rating_score.user_id as rater_id',
            'users.name as rater_name',
            'rating_score.nPersonalInfo_id',
            'rating_score.ControlNo',
            'rating_score.job_batches_rsp_id',
            'rating_score.education_score as education',
            'rating_score.experience_score as experience',
            'rating_score.training_score as training',
            'rating_score.performance_score as performance',
            'rating_score.behavioral_score as bei',
            'rating_score.total_qs',
            'rating_score.grand_total',
            'rating_score.ranking',
            'rating_score.rater_name',
            'nPersonalInfo.firstname',
            'nPersonalInfo.lastname',
            'nPersonalInfo.image_path',
            'submission.id as submission_id'
        )

            ->leftJoin('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
            ->leftJoin('users', 'users.id', '=', 'rating_score.user_id')
            ->leftJoin('submission', function ($join) {
                $join->on('submission.job_batches_rsp_id', '=', 'rating_score.job_batches_rsp_id')
                    ->where(function ($q) {
                        $q->whereColumn('submission.nPersonalInfo_id', 'rating_score.nPersonalInfo_id')
                            ->orWhereColumn('submission.ControlNo', 'rating_score.ControlNo');
                    });
            })
            ->where('rating_score.job_batches_rsp_id', $jobpostId)
            ->get();

        // 🔹 Step 2: Group scores by applicant (use nPersonalInfo_id if exists, otherwise ControlNo)
        $scoresByApplicant = $allScores->groupBy(function ($row) {
            return $row->nPersonalInfo_id ?: 'control_' . $row->ControlNo;
        });

        $results = [];

        foreach ($scoresByApplicant as $applicantKey => $scoreRows) {
            $firstRow = $scoreRows->first();

            // ✅ Case 1: Applicant has nPersonalInfo record
            if ($firstRow->nPersonalInfo_id) {
                $firstname = $firstRow->firstname;
                $lastname = $firstRow->lastname;

                // Build image URL if exists
                $imageUrl = null;
                if ($firstRow->image_path && Storage::disk('public')->exists($firstRow->image_path)) {
                    $baseUrl = config('app.url');
                    $imageUrl = $baseUrl . '/storage/' . $firstRow->image_path;
                }
            }
            // ✅ Case 2: Applicant only has ControlNo (no nPersonalInfo_id)
            else {
                $xPDS = new \App\Http\Controllers\xPDSController();
                $employeeData = $xPDS->getPersonalDataSheet(new \Illuminate\Http\Request([
                    'controlno' => $firstRow->ControlNo
                ]));

                $employeeJson = $employeeData->getData(true);
                $firstname = $employeeJson['User'][0]['Firstname'] ?? '';
                $lastname = $employeeJson['User'][0]['Surname'] ?? '';
                $imageUrl = $employeeJson['User'][0]['Pics'] ?? null;
            }

            // 🔹 Step 3: Compute applicant’s final score
            $scoresArray = $scoreRows->map(function ($row) {
                return [
                    'education'   => (float) $row->education,
                    'experience'  => (float) $row->experience,
                    'training'    => (float) $row->training,
                    'performance' => (float) $row->performance,
                    'bei'         => (float) $row->bei,
                ];
            })->toArray();

            $computed = RatingService::computeFinalScore($scoresArray);

            $applicantData = array_merge(
                [
                    'submission_id'     => (string) $firstRow->submission_id,
                    'nPersonalInfo_id'   => (string) $firstRow->nPersonalInfo_id,
                    'ControlNo'          => $firstRow->ControlNo,
                    'firstname'          => $firstname,
                    'lastname'           => $lastname,
                    'image_url'          => $imageUrl,
                    'job_batches_rsp_id' => (string) $firstRow->job_batches_rsp_id,
                ],
                $computed,
                [
                    'history' => $scoreRows->map(function ($item) {
                        return [
                            'id'            => $item->id,
                            'rater_id'      => $item->rater_id,
                            'rater_name'    => $item->rater_name,
                            'education'     => $item->education,
                            'experience'    => $item->experience,
                            'training'      => $item->training,
                            'performance'   => $item->performance,
                            'bei'           => $item->bei,
                            'total_qs'      => $item->total_qs,
                            'grand_total'   => $item->grand_total,
                            'ranking'       => $item->ranking,
                        ];
                    })
                ]
            );

            $results[$applicantKey] = $applicantData;
        }

        // 🔹 Step 4: Rank applicants for this job post
        $rankedApplicants = RatingService::addRanking(array_values($results));

        return response()->json([
            'jobpost_id'      => $jobpostId,
            'total_assigned'  => $totalAssigned,
            'total_completed' => $totalCompleted,
            'applicants'      => $rankedApplicants
        ]);
    }


    public function index()
    {

        $data = rating_score::all();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // public function dashboard()
    // {

    //     $data = Auth::user();


    // }


    public function getAssignedJobs()
    {
        $user = Auth::user();

        $jobBatchIds = DB::table('job_batches_user')
            ->where('user_id', $user->id)
            ->pluck('job_batches_rsp_id');

        $assignedJobs = \App\Models\JobBatchesRsp::whereIn('id', $jobBatchIds)
            ->get()
            ->map(function ($job) use ($user) {
                $job->submitted = rating_score::where('user_id', $user->id)
                    ->where('job_batches_rsp_id', $job->id)
                    ->where('submitted', true)
                    ->exists();
                return $job;
            });

        return response()->json([
            'status' => true,
            'assigned_jobs' => $assignedJobs
        ]);
    }


    public function get_criteria_applicant($id)
    {

        $userId = Auth::id(); // ✅ get current logged-in rater

        // Get criteria
        $criteria = criteria_rating::with(['educations', 'experiences', 'trainings', 'performances', 'behaviorals'])
            ->where('job_batches_rsp_id', $id)
            ->get();

        // Get applicants with relationships
        $submissions = Submission::where('job_batches_rsp_id', $id)
            ->with([
                'nPersonalInfo.education',
                'nPersonalInfo.work_experience',
                'nPersonalInfo.training',
                'nPersonalInfo.eligibity',
                'nPersonalInfo.rating_score',
                // ⚠️ Don’t eager-load all draft scores (they belong to multiple raters)
            ])
            ->where('status', 'qualified')
            ->get();

        $applicants = $submissions->map(function ($submission) use ($userId) {
            $info = $submission->nPersonalInfo;

            if (!$info && $submission->ControlNo) {
                $xPDS = new \App\Http\Controllers\xPDSController();
                $employeeData = $xPDS->getPersonalDataSheet(new \Illuminate\Http\Request([
                    'controlno' => $submission->ControlNo
                ]));

                $employeeJson = $employeeData->getData(true);

                $info = [
                    'firstname' => $employeeJson['User'][0]['Firstname'] ?? '',
                    'lastname' => $employeeJson['User'][0]['Surname'] ?? '',
                    'education' => $employeeJson['Education'] ?? [],
                    'eligibity' => $employeeJson['Eligibility'] ?? [],
                    'work_experience' => $employeeJson['Experience'] ?? [],
                    'training' => $employeeJson['Training'] ?? [],
                ];

                $ratingScore = \App\Models\rating_score::where('ControlNo', $submission->ControlNo)->first();

                // ✅ Only fetch draft_score for the logged-in rater
                $draftScore  = \App\Models\draft_score::where('ControlNo', $submission->ControlNo)
                    ->where('user_id', $userId)
                    ->where('job_batches_rsp_id', $submission->job_batches_rsp_id) // 🔑 filter by current job post
                    ->first();
            } else {
                $ratingScore = $info->rating_score ?? null;

                // ✅ Filter draft_score by rater
                $draftScore = \App\Models\draft_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
                    ->where('user_id', $userId)
                    ->where('job_batches_rsp_id', $submission->job_batches_rsp_id) // 🔑 filter by current job post
                    ->first();
            }

            // 🔄 Standardize datasets (education, eligibility, training, experience)
            $educationData = collect($info['education'] ?? [])->map(function ($edu) {
                return [
                    'level'           => $edu['Education'] ?? $edu['level'] ?? null,
                    'school_name'     => $edu['School'] ?? $edu['school_name'] ?? null,
                    'degree'          => $edu['Degree'] ?? $edu['degree'] ?? null,
                    'attendance_from' => $edu['DateAttend'] ?? $edu['attendance_from'] ?? null,
                    'attendance_to'   => $edu['attendance_to'] ?? null,
                    'year_graduated'  => $edu['year_graduated'] ?? null,
                    'highest_units'   => $edu['NumUnits'] ?? $edu['highest_units'] ?? null,
                    'scholarship'     => $edu['Honors'] ?? $edu['scholarship'] ?? null,
                ];
            });

            $eligibityData = collect($info['eligibity'] ?? [])->map(function ($eli) {
                return [
                    'eligibility'          => $eli['CivilServe'] ?? $eli['eligibility'] ?? null,
                    'rating'               => $eli['Rates'] ?? $eli['rating'] ?? null,
                    'date_of_examination'  => $eli['Dates'] ?? $eli['date_of_examination'] ?? null,
                    'place_of_examination' => $eli['Place'] ?? $eli['place_of_examination'] ?? null,
                    'license_number'       => $eli['LNumber'] ?? $eli['license_number'] ?? null,
                    'date_of_validity'     => $eli['LDate'] ?? $eli['date_of_validity'] ?? null,
                ];
            });

            $trainingData = collect($info['training'] ?? [])->map(function ($train) {
                return [
                    'training_title'      => $train['Training'] ?? $train['training_title'] ?? null,
                    'inclusive_date_from' => $train['DateFrom'] ?? $train['inclusive_date_from'] ?? null,
                    'inclusive_date_to'   => $train['DateTo'] ?? $train['inclusive_date_to'] ?? null,
                    'number_of_hours'     => $train['NumHours'] ?? $train['number_of_hours'] ?? null,
                    'type'                => $train['Type'] ?? $train['type'] ?? null,
                    'conducted_by'        => $train['Conductor'] ?? $train['conducted_by'] ?? null,
                ];
            });

            $experienceData = collect($info['work_experience'] ?? [])->map(function ($exp) {
                return [
                    'work_date_from'       => $exp['WFrom'] ?? $exp['work_date_from'] ?? null,
                    'work_date_to'         => $exp['WTo'] ?? $exp['work_date_to'] ?? null,
                    'position_title'       => $exp['WPosition'] ?? $exp['position_title'] ?? null,
                    'department'           => $exp['WCompany'] ?? $exp['department'] ?? null,
                    'monthly_salary'       => $exp['WSalary'] ?? $exp['monthly_salary'] ?? null,
                    'salary_grade'         => $exp['WGrade'] ?? $exp['salary_grade'] ?? null,
                    'status_of_appointment' => $exp['Status'] ?? $exp['status_of_appointment'] ?? null,
                    'government_service'   => $exp['WGov'] ?? $exp['government_service'] ?? null,
                ];
            });

            return [
                'id'              => $submission->id,
                'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                'ControlNo'       => $submission->ControlNo,
                'firstname'       => $info['firstname'] ?? '',
                'lastname'        => $info['lastname'] ?? '',
                'rating_score'    => [
                    'education_score'  => $ratingScore->education_score ?? null,
                    'experience_score' => $ratingScore->experience_score ?? null,
                    'training_score'   => $ratingScore->training_score ?? null,
                    'performance_score' => $ratingScore->performance_score ?? null,
                    'behavioral_score' => $ratingScore->behavioral_score ?? null,
                    'total_qs'         => $ratingScore->total_qs ?? null,
                    'grand_total'      => $ratingScore->grand_total ?? null,
                    'ranking'          => $ratingScore->ranking ?? null,
                ],
                'draft_score'     => [
                    'education_score'  => $draftScore->education_score ?? null,
                    'experience_score' => $draftScore->experience_score ?? null,
                    'training_score'   => $draftScore->training_score ?? null,
                    'performance_score' => $draftScore->performance_score ?? null,
                    'behavioral_score' => $draftScore->behavioral_score ?? null,
                    'total_qs'         => $draftScore->total_qs ?? null,
                    'grand_total'      => $draftScore->grand_total ?? null,
                    'ranking'          => $draftScore->ranking ?? null,
                ],
                'education'       => $educationData,
                'work_experience' => $experienceData,
                'training'        => $trainingData,
                'eligibity'       => $eligibityData,
            ];
        });

        return response()->json([
            'status'    => true,
            'criteria'  => $criteria,
            'applicants' => $applicants,
        ]);
    }

    public function get_all_raters()
    {
        try {
            $users = User::where('role_id', 2)
                ->with(['job_batches_rsp' => function ($q) {
                    $q->select('job_batches_rsp.id', 'job_batches_rsp.Position');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {
                    $pendingCount = $user->job_batches_rsp()
                        ->wherePivot('status', 'pending')
                        ->count();

                    $completeCount = $user->job_batches_rsp()
                        ->wherePivot('status', 'complete')
                        ->count();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'job_batches_rsp' => $user->job_batches_rsp->pluck('Position')->implode(', '),
                        'office' => $user->office,
                        'pending' => $pendingCount,
                        'active' => $user->active,
                        'completed' => $completeCount,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Raters retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve raters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // this function will fetch all rater username on the login page
    public function get_rater_usernames()
    {
        try {
            $users = User::where('role_id', 2)->where('active', 1)
                ->orderBy('created_at', 'desc') // Order by latest created first
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'office' => $user->office,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Raters retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve raters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store_score(Request $request) // storing the score of the applicant
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $raterName = $user->name; // get rater name from users table
            $data = $request->all();

            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data format. Expected an array of submissions.'
                ], 422);
            }

            // ✅ Check if already submitted
            $jobBatchId = $data[0]['job_batches_rsp_id'] ?? null;

            $exists = rating_score::where('user_id', $userId)
                ->where('job_batches_rsp_id', $jobBatchId)
                ->where('submitted', true)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted your scores for this job post.',
                    'close_form' => true
                ], 409);
            }

            $results = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($data as $index => $item) {
                if (!is_array($item)) {
                    $errors[] = [
                        'index' => $index,
                        'errors' => ['Invalid format. Each item must be an object.']
                    ];
                    continue;
                }

                $validator = Validator::make($item, [
                    'nPersonalInfo_id' => 'nullable|exists:nPersonalInfo,id',
                    'ControlNo' => 'nullable|string|max:255',
                    'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
                    'education_score' => 'required|numeric|min:0|max:100',
                    'experience_score' => 'required|numeric|min:0|max:100',
                    'training_score' => 'required|numeric|min:0|max:100',
                    'performance_score' => 'required|numeric|min:0|max:100',
                    'behavioral_score' => 'required|numeric|min:0|max:100',
                    'total_qs' => 'required|numeric|min:0|max:75',
                    'grand_total' => 'required|numeric|min:0|max:100',
                    'ranking' => 'required|integer',

                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'index' => $index,
                        'errors' => $validator->errors()
                    ];
                    continue;
                }

                $validated = $validator->validated();

                // Create record with submitted = true
                $submission = rating_score::create([
                    'user_id' => $userId,
                    'nPersonalInfo_id' => $validated['nPersonalInfo_id'],
                    'ControlNo' => $validated['ControlNo'],
                    'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
                    'education_score' => $validated['education_score'],
                    'experience_score' => $validated['experience_score'],
                    'training_score' => $validated['training_score'],
                    'performance_score' => $validated['performance_score'],
                    'behavioral_score' => $validated['behavioral_score'],
                    'total_qs' => $validated['total_qs'],
                    'grand_total' => $validated['grand_total'],
                    'ranking' => $validated['ranking'],
                    'evaluated_at' => now(),
                    'submitted' => true,
                    'rater_name' => $raterName, // ✅ automatically assign rater's name

                ]);

                $results[] = $submission;
            }
            // ✅ Auto-update pivot table when a rater has scored
            DB::table('job_batches_user')
                ->where('user_id', $userId)
                // ->where('job_batches_rsp_id', $jobBatchId)
                ->where('job_batches_rsp_id', $validated['job_batches_rsp_id'])
                ->update(['status' => 'complete']);

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed for some items',
                    'errors' => $errors,
                    'processed_count' => count($results)
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully created all records.',
                'data' => $results,
                'count' => count($results),

            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storing rating scores: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while storing the ratings. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function draft_score(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $raterName = $user->name; // get rater name from users table
            $data = $request->all();


            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data format. Expected an array of submissions.'
                ], 422);
            }

            $results = [];
            $errors = [];

            foreach ($data as $index => $item) {
                if (!is_array($item)) {
                    $errors[] = [
                        'index' => $index,
                        'errors' => ['Invalid format. Each item must be an object.']
                    ];
                    continue;
                }

                $validator = Validator::make($item, [
                    'nPersonalInfo_id' => 'nullable|exists:nPersonalInfo,id',
                    'ControlNo' => 'nullable|string|max:255',
                    'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
                    'education_score' => 'nullable|numeric|min:0|max:100',
                    'experience_score' => 'nullable|numeric|min:0|max:100',
                    'training_score' => 'nullable|numeric|min:0|max:100',
                    'performance_score' => 'nullable|numeric|min:0|max:100',
                    'behavioral_score' => 'nullable|numeric|min:0|max:100',
                    'total_qs' => 'nullable|numeric|min:0|max:75',
                    'grand_total' => 'nullable|numeric|min:0|max:100',
                    'ranking' => 'nullable|integer',


                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'index' => $index,
                        'errors' => $validator->errors()
                    ];
                    continue;
                }

                $validated = $validator->validated();

                $submission = draft_score::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'nPersonalInfo_id' => $validated['nPersonalInfo_id'],
                        'ControlNo' => $validated['ControlNo'],
                        'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
                    ],
                    [
                        'education_score' => $validated['education_score'],
                        'experience_score' => $validated['experience_score'],
                        'training_score' => $validated['training_score'],
                        'performance_score' => $validated['performance_score'],
                        'behavioral_score' => $validated['behavioral_score'],
                        'total_qs' => $validated['total_qs'],
                        'grand_total' => $validated['grand_total'],
                        'ranking' => $validated['ranking'],
                        'evaluated_at' => now(),
                        'rater_name' => $raterName, // ✅ automatically assign rater's name

                    ]
                );

                $results[] = $submission;
            }

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully.',
                'data' => $results,
                'errors' => $errors,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error storing draft scores: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the draft.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    // deleting applicant on the job_post he/she applicant
    public function delete($id)
    {
        $submission = rating_score::find($id);

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
