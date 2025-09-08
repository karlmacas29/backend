<?php

namespace App\Http\Controllers\rater;


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

class rater_controller extends Controller
{



    public function showScoresWithHistory($jobpostId)
    {
        // Ensure job post exists
        $jobpost = JobBatchesRsp::findOrFail($jobpostId);

        // ✅ Count assigned and completed raters
        $totalAssigned = Job_batches_user::where('job_batches_rsp_id', $jobpostId)->count();
        $totalCompleted = Job_batches_user::where('job_batches_rsp_id', $jobpostId)
            ->where('status', 'complete')
            ->count();

        // 🔹 Step 1: Fetch ALL scores (per rater) for applicants in this job post
        $allScores = rating_score::select(
            'rating_score.id',
            'rating_score.user_id as rater_id',
            'users.name as rater_name',
            'rating_score.nPersonalInfo_id',
            'rating_score.job_batches_rsp_id',
            'rating_score.education_score as education',
            'rating_score.experience_score as experience',
            'rating_score.training_score as training',
            'rating_score.performance_score as performance',
            'rating_score.behavioral_score as bei',
            'rating_score.total_qs',
            'rating_score.grand_total',
            'rating_score.ranking',
            'nPersonalInfo.firstname',
            'nPersonalInfo.lastname',
            'nPersonalInfo.image_path'
        )
            ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
            ->leftJoin('users', 'users.id', '=', 'rating_score.user_id')
            ->where('rating_score.job_batches_rsp_id', $jobpostId)
            ->get();

        // 🔹 Step 2: Group scores by applicant
        $scoresByApplicant = $allScores->groupBy('nPersonalInfo_id');

        $results = [];
        foreach ($scoresByApplicant as $applicantId => $scoreRows) {
            $firstRow = $scoreRows->first();

            // ✅ Build image URL
            $imageUrl = null;
            if ($firstRow->image_path && Storage::disk('public')->exists($firstRow->image_path)) {
                $baseUrl = config('app.url');
                $imageUrl = $baseUrl . '/storage/' . $firstRow->image_path;
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
                    'nPersonalInfo_id'   => (string) $firstRow->nPersonalInfo_id,
                    'firstname'          => $firstRow->firstname,
                    'lastname'           => $firstRow->lastname,
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

            $results[$applicantId] = $applicantData;
        }

        // 🔹 Step 4: Rank applicants for this job post
        $rankedApplicants = RatingService::addRanking(array_values($results));

        // Re-index by applicant ID
        $final = [];
        foreach ($rankedApplicants as $applicant) {
            $final[$applicant['nPersonalInfo_id']] = $applicant;
        }

        return response()->json([
            'jobpost_id'      => $jobpostId,
            'total_assigned'  => $totalAssigned,
            'total_completed' => $totalCompleted,
            'applicants'      => $final
        ]);
    }

    //   public function showScores($jobpostId)
    // {

    //   $jobpost = JobBatchesRsp::findOrFail($jobpostId);

    //     // Fetch all scores
    //     $rawScores = rating_score::select(
    //             'nPersonalInfo_id',
    //             'job_batches_rsp_id',
    //             'education_score as education',
    //             'experience_score as experience',
    //             'training_score as training',
    //             'performance_score as performance',
    //             'behavioral_score as bei'
    //         )
    //         ->get()
    //         ->groupBy(function ($row) {
    //             // Group by applicant and job post
    //             return $row->nPersonalInfo_id . '-' . $row->job_batches_rsp_id;
    //         });

    //     $results = [];

    //     foreach ($rawScores as $groupKey => $scoreRows) {
    //         $firstRow = $scoreRows->first();

    //         $scoresArray = $scoreRows->map(function ($row) {
    //             return [
    //                 'education'   => (float) $row->education,
    //                 'experience'  => (float) $row->experience,
    //                 'training'    => (float) $row->training,
    //                 'performance' => (float) $row->performance,
    //                 'bei'         => (float) $row->bei,
    //             ];
    //         })->toArray();

    //         $computed = RatingService::computeFinalScore($scoresArray);

    //         // Keep original IDs
    //         $computed['nPersonalInfo_id'] = (string) $firstRow->nPersonalInfo_id;
    //         $computed['job_batches_rsp_id'] = (string) $firstRow->job_batches_rsp_id;

    //         $results[$groupKey] = $computed;
    //     }

    //     // Rank applicants per job post
    //     $rankedApplicants = collect($results)
    //         ->groupBy('job_batches_rsp_id')
    //         ->map(function ($group) {
    //             return RatingService::addRanking($group->toArray());
    //         })
    //         ->map(function ($group) {
    //             // Re-index by nPersonalInfo_id
    //             $reindexed = [];
    //             foreach ($group as $item) {
    //                 $reindexed[$item['nPersonalInfo_id']] = $item;
    //             }
    //             return $reindexed;
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $rankedApplicants
    //     ]);
    // }

    // public function applicant_history_score($applicantId) // show the history score of the applicant
    // {
    //     // Fetch all scores belonging to the applicant
    //     $scores = rating_score::select(
    //         'rating_score.id',
    //         'rating_score.job_batches_rsp_id',
    //         'rating_score.education_score as education',
    //         'rating_score.experience_score as experience',
    //         'rating_score.training_score as training',
    //         'rating_score.performance_score as performance',
    //         'rating_score.behavioral_score as bei',
    //         'rating_score.total_qs',
    //         'rating_score.grand_total',
    //         'rating_score.ranking',
    //         'nPersonalInfo.firstname',
    //         'nPersonalInfo.lastname'
    //     )
    //         ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
    //         ->where('rating_score.nPersonalInfo_id', $applicantId)
    //         ->get();

    //     if ($scores->isEmpty()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No scores found for this applicant'
    //         ]);
    //     }

    //     // Get applicant name from first record
    //     $first = $scores->first();
    //     $firstname = $first->firstname;
    //     $lastname = $first->lastname;

    //     // Remove duplicate name fields from each score
    //     $cleanScores = $scores->map(function ($item) {
    //         unset($item->firstname, $item->lastname);
    //         return $item;
    //     });

    //     return response()->json([
    //         'status'       => true,
    //         'applicant_id' => $applicantId,
    //         'firstname'    => $firstname,
    //         'lastname'     => $lastname,
    //         'scores'       => $cleanScores
    //     ]);
    // }

    // public function applicant_history_score($applicantId)
    // {
    //     // Fetch all scores belonging to the applicant
    //     $scores = rating_score::select(
    //         'rating_score.id',
    //         'rating_score.user_id as rater_id',
    //         'users.name as rater_name', // ✅ fetch rater name
    //         'rating_score.nPersonalInfo_id',
    //         'rating_score.job_batches_rsp_id',
    //         'rating_score.education_score as education',
    //         'rating_score.experience_score as experience',
    //         'rating_score.training_score as training',
    //         'rating_score.performance_score as performance',
    //         'rating_score.behavioral_score as bei',
    //         'rating_score.total_qs',
    //         'rating_score.grand_total',
    //         'rating_score.ranking',
    //         'nPersonalInfo.firstname',
    //         'nPersonalInfo.lastname',
    //         'nPersonalInfo.image_path'

    //     )
    //         // ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
    //         // ->where('rating_score.nPersonalInfo_id', $applicantId)
    //         // ->get();
    //         ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
    //         ->leftJoin('users', 'users.id', '=', 'rating_score.user_id') // ✅ join users table
    //         ->where('rating_score.nPersonalInfo_id', $applicantId)
    //         ->get();


    //     if ($scores->isEmpty()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No scores found for this applicant'
    //         ]);
    //     }

    //     $first = $scores->first();
    //     $firstname = $first->firstname;
    //     $lastname  = $first->lastname;

    //     // ✅ Build image URL
    //     $imageUrl = null;
    //     if ($first->image_path) {
    //         if (Storage::disk('public')->exists($first->image_path)) {
    //             $baseUrl = config('app.url'); // APP_URL from .env
    //             $imageUrl = $baseUrl . '/storage/' . $first->image_path;
    //         }
    //     }
    //     // 🔹 Group by job post (since final score/rank is per job post)
    //     $grouped = $scores->groupBy('job_batches_rsp_id');

    //     $finalResults = [];

    //     foreach ($grouped as $jobpostId => $scoreRows) {
    //         // Collect all applicants for this jobpost
    //         $rawScores = rating_score::select(
    //             'rating_score.nPersonalInfo_id',
    //             'rating_score.job_batches_rsp_id',
    //             'rating_score.education_score as education',
    //             'rating_score.experience_score as experience',
    //             'rating_score.training_score as training',
    //             'rating_score.performance_score as performance',
    //             'rating_score.behavioral_score as bei',
    //             // 'nPersonalInfo.firstname',
    //             // 'nPersonalInfo.lastname'
    //         )
    //             ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
    //             ->where('rating_score.job_batches_rsp_id', $jobpostId)
    //             ->get()
    //             ->groupBy(function ($row) {
    //                 return $row->nPersonalInfo_id . '-' . $row->job_batches_rsp_id;
    //             });

    //         $results = [];
    //         foreach ($rawScores as $groupKey => $rows) {
    //             $firstRow = $rows->first();

    //             $scoresArray = $rows->map(function ($row) {
    //                 return [
    //                     'education'   => (float) $row->education,
    //                     'experience'  => (float) $row->experience,
    //                     'training'    => (float) $row->training,
    //                     'performance' => (float) $row->performance,
    //                     'bei'         => (float) $row->bei,
    //                 ];
    //             })->toArray();

    //             $computed = RatingService::computeFinalScore($scoresArray);

    //             $computed = array_merge(
    //                 $computed,
    //                 [
    //                     'nPersonalInfo_id'   => (string) $firstRow->nPersonalInfo_id,
    //                     // 'job_batches_rsp_id' => (string) $firstRow->job_batches_rsp_id,
    //                 ]
    //             );

    //             $results[$groupKey] = $computed;
    //         }

    //         // Rank applicants for this jobpost
    //         $rankedApplicants = collect($results)
    //             ->groupBy('job_batches_rsp_id')
    //             ->map(function ($group) {
    //                 return RatingService::addRanking($group->toArray());
    //             })
    //             ->collapse();

    //         // Get THIS applicant’s final score & rank for this jobpost
    //         $finalResults[$jobpostId] = $rankedApplicants->firstWhere('nPersonalInfo_id', (string) $applicantId);
    //     }

    //     return response()->json([
    //         'status'       => true,
    //         'applicant_id' => $applicantId,
    //         'firstname'    => $firstname,
    //         'lastname'     => $lastname,
    //         'image_url'    => $imageUrl, // ✅ added applicant image url
    //         'history'      => $scores->map(function ($item) {
    //             unset($item->firstname, $item->lastname, $item->image_path);
    //             return $item;
    //         }),
    //         'final_scores' => $finalResults, // ✅ includes computed final score + rank
    //     ]);
    // }


    // public function showScores($jobpostId) //fetch all applicant on the specific jobpost of final scores  and with rank.
    // {
    //     // Ensure job post exists
    //     $jobpost = JobBatchesRsp::findOrFail($jobpostId);

    //     // Fetch only scores for this job post with applicant details
    //     $rawScores = rating_score::select(
    //         'rating_score.nPersonalInfo_id',
    //         'rating_score.job_batches_rsp_id',
    //         'rating_score.education_score as education',
    //         'rating_score.experience_score as experience',
    //         'rating_score.training_score as training',
    //         'rating_score.performance_score as performance',
    //         'rating_score.behavioral_score as bei',
    //         'nPersonalInfo.firstname',
    //         'nPersonalInfo.lastname'
    //     )
    //         ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id') // ✅ singular
    //         ->where('rating_score.job_batches_rsp_id', $jobpostId)
    //         ->get()
    //         ->groupBy(function ($row) {
    //             return $row->nPersonalInfo_id . '-' . $row->job_batches_rsp_id;
    //         });

    //     $results = [];
    //     foreach ($rawScores as $groupKey => $scoreRows) {
    //         $firstRow = $scoreRows->first();

    //         $scoresArray = $scoreRows->map(function ($row) {
    //             return [
    //                 'education'   => (float) $row->education,
    //                 'experience'  => (float) $row->experience,
    //                 'training'    => (float) $row->training,
    //                 'performance' => (float) $row->performance,
    //                 'bei'         => (float) $row->bei,
    //             ];
    //         })->toArray();

    //         $computed = RatingService::computeFinalScore($scoresArray);

    //         // Reorder so firstname + lastname are on top
    //         $computed = array_merge(
    //             [
    //                 'firstname' => $firstRow->firstname,
    //                 'lastname'  => $firstRow->lastname,
    //             ],
    //             $computed,
    //             [
    //                 'nPersonalInfo_id'   => (string) $firstRow->nPersonalInfo_id,
    //                 'job_batches_rsp_id' => (string) $firstRow->job_batches_rsp_id,
    //             ]
    //         );

    //         $results[$groupKey] = $computed;
    //     }


    //     // Rank applicants for this job post only
    //     $rankedApplicants = collect($results)
    //         ->groupBy('job_batches_rsp_id')
    //         ->map(function ($group) {
    //             return RatingService::addRanking($group->toArray());
    //         })
    //         ->map(function ($group) {
    //             $reindexed = [];
    //             foreach ($group as $item) {
    //                 $reindexed[$item['nPersonalInfo_id']] = $item;
    //             }
    //             return $reindexed;
    //         });

    //     return response()->json(
    //         // 'jobpost_id'  => $jobpostId,
    //         $rankedApplicants[$jobpostId] ?? []
    //     );
    // }

    // public function showScores($jobpostId) //fetch all applicant on the specific jobpost of final scores  and with rank.
    // {
    //     // Ensure job post exists
    //     $jobpost = JobBatchesRsp::findOrFail($jobpostId);

    //     // ✅ Count how many users are assigned to this job post
    //     $totalAssigned = Job_batches_user::where('job_batches_rsp_id', $jobpostId)->count();

    //     // ✅ Count how many users marked as complete for this job post
    //     $totalCompleted = Job_batches_user::where('job_batches_rsp_id', $jobpostId)
    //         ->where('status', 'complete')
    //         ->count();

    //     // Fetch only scores for this job post with applicant details
    //     $rawScores = rating_score::select(
    //         'rating_score.nPersonalInfo_id',
    //         'rating_score.job_batches_rsp_id',
    //         'rating_score.education_score as education',
    //         'rating_score.experience_score as experience',
    //         'rating_score.training_score as training',
    //         'rating_score.performance_score as performance',
    //         'rating_score.behavioral_score as bei',
    //         'nPersonalInfo.firstname',
    //         'nPersonalInfo.lastname'
    //     )
    //         ->join('nPersonalInfo', 'nPersonalInfo.id', '=', 'rating_score.nPersonalInfo_id')
    //         ->where('rating_score.job_batches_rsp_id', $jobpostId)
    //         ->get()
    //         ->groupBy(function ($row) {
    //             return $row->nPersonalInfo_id . '-' . $row->job_batches_rsp_id;
    //         });

    //     $results = [];
    //     foreach ($rawScores as $groupKey => $scoreRows) {
    //         $firstRow = $scoreRows->first();

    //         $scoresArray = $scoreRows->map(function ($row) {
    //             return [
    //                 'education'   => (float) $row->education,
    //                 'experience'  => (float) $row->experience,
    //                 'training'    => (float) $row->training,
    //                 'performance' => (float) $row->performance,
    //                 'bei'         => (float) $row->bei,
    //             ];
    //         })->toArray();

    //         $computed = RatingService::computeFinalScore($scoresArray);

    //         // Reorder so firstname + lastname are on top
    //         $computed = array_merge(
    //             [
    //                 'firstname' => $firstRow->firstname,
    //                 'lastname'  => $firstRow->lastname,
    //             ],
    //             $computed,
    //             [
    //                 'nPersonalInfo_id'   => (string) $firstRow->nPersonalInfo_id,
    //                 'job_batches_rsp_id' => (string) $firstRow->job_batches_rsp_id,
    //             ]
    //         );

    //         $results[$groupKey] = $computed;
    //     }

    //     // Rank applicants for this job post only
    //     $rankedApplicants = collect($results)
    //         ->groupBy('job_batches_rsp_id')
    //         ->map(function ($group) {
    //             return RatingService::addRanking($group->toArray());
    //         })
    //         ->map(function ($group) {
    //             $reindexed = [];
    //             foreach ($group as $item) {
    //                 $reindexed[$item['nPersonalInfo_id']] = $item;
    //             }
    //             return $reindexed;
    //         });

    //     return response()->json([
    //         'jobpost_id'       => $jobpostId,
    //         'total_assigned'   => $totalAssigned,
    //         'total_completed'  => $totalCompleted,
    //         'applicants'       => $rankedApplicants[$jobpostId] ?? []
    //     ]);
    // }

    public function index(){

        $data = rating_score::all();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

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

    // rater criteria - fetching the applicant information   and criteria of the job_post
    public function get_criteria_applicant($id)
    {
        // Get criteria for the selected job post
        $criteria = criteria_rating::with(['educations', 'experiences', 'trainings', 'performances', 'behaviorals'])
            ->where('job_batches_rsp_id', $id)
            ->get();

        // Get applicants with personal info and nested relationships
        $submissions = Submission::with([
            'nPersonalInfo.education',
            'nPersonalInfo.work_experience',
            'nPersonalInfo.training',
            'nPersonalInfo.eligibity',
            'nPersonalInfo.rating_score', // <-- Add this line
        ])
            ->where('job_batches_rsp_id', $id)
            ->where('status', 'qualified') // Only qualified applicants
            ->get();

        // Format applicants
        $applicants = $submissions->map(function ($submission) {
            $info = $submission->nPersonalInfo;

            return [
                'id' => $submission->id,
                'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                'firstname' => $info->firstname ?? '',
                'lastname' => $info->lastname ?? '',
                // Rating score
                'rating_score' => [
                    'education_score' => $info->rating_score->education_score ?? null,
                    'experience_score' => $info->rating_score->experience_score ?? null,
                    'training_score' => $info->rating_score->training_score ?? null,
                    'performance_score' => $info->rating_score->performance_score ?? null,
                    'behavioral_score' => $info->rating_score->behavioral_score ?? null,
                    'total_qs' => $info->rating_score->total_qs ?? null,
                    'grand_total' => $info->rating_score->grand_total ?? null,
                    'ranking' => $info->rating_score->ranking ?? null,
                ],
                'education' => $info->education->map(function ($edu) {
                    return [
                        'school_name' => $edu->school_name,
                        'degree' => $edu->degree,
                        'attendance_from' => $edu->attendance_from,
                        'attendance_to' => $edu->attendance_to,
                        'year_graduated' => $edu->year_graduated,
                        'scholarship' => $edu->scholarship,
                        'level' => $edu->level,
                    ];
                }),
                'work_experience' => $info->work_experience->map(function ($work) {
                    return [
                        'position_title' => $work->position_title,
                        'department' => $work->department,
                        'work_date_from' => $work->work_date_from,
                        'work_date_to' => $work->work_date_to,
                        'monthly_salary' => $work->monthly_salary,
                        'status_of_appointment' => $work->status_of_appointment,
                        'government_service' => $work->government_service,
                    ];
                }),
                'training' => $info->training->map(function ($train) {
                    return [
                        'training_title' => $train->training_title,
                        'inclusive_date_from' => $train->inclusive_date_from,
                        'inclusive_date_to' => $train->inclusive_date_to,
                        'number_of_hours' => $train->number_of_hours,
                        'type' => $train->type,
                        'conducted_by' => $train->conducted_by,
                    ];
                }),
                'eligibity' => $info->eligibity->map(function ($elig) {
                    return [
                        'eligibility' => $elig->eligibility,
                        'rating' => $elig->rating,
                        'date_of_examination' => $elig->date_of_examination,
                        'place_of_examination' => $elig->place_of_examination,
                        'license_number' => $elig->license_number,
                        'date_of_validity' => $elig->date_of_validity,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'criteria' => $criteria,
            'applicants' => $applicants,
        ]);
    }

    // fetching the all rater on the admin table only will be fetch have role rater
    // public function get_all_raters()
    // {
    //     try {
    //         $users = User::where('role_id', 2)
    //             ->orderBy('created_at', 'desc') // Order by latest created first
    //             ->get()
    //             ->map(function ($user) {
    //                 return [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'username' => $user->username,
    //                     'job_batches_rsp' => $user->job_batches_rsp->pluck('Position')->implode(', '),
    //                     'office' => $user->office,
    //                     'created_at' => $user->created_at->format('Y-m-d H:i:s'), // Include created date
    //                     'updated_at' => $user->updated_at->format('Y-m-d H:i:s'), // Include updated date
    //                 ];
    //             });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Raters retrieved successfully',
    //             'data' => $users
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to retrieve raters',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
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
            $users = User::where('role_id', 2)
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
            $userId = Auth::id();
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
                    'nPersonalInfo_id' => 'required|exists:nPersonalInfo,id',
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
                    'submitted' => true
                ]);

                $results[] = $submission;
            }
            // ✅ Auto-update pivot table when a rater has scored
            DB::table('job_batches_user')
                ->where('user_id', $userId)
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
            $userId = Auth::id();
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
                    'nPersonalInfo_id' => 'required|exists:nPersonalInfo,id',
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
                        'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
                    ],
                    [
                        'education_score' => $validated['education_score'] ?? null,
                        'experience_score' => $validated['experience_score'] ?? null,
                        'training_score' => $validated['training_score'] ?? null,
                        'performance_score' => $validated['performance_score'] ?? null,
                        'behavioral_score' => $validated['behavioral_score'] ?? null,
                        'total_qs' => $validated['total_qs'] ?? null,
                        'grand_total' => $validated['grand_total'] ?? null,
                        'ranking' => $validated['ranking'] ?? null,
                        'evaluated_at' => now(),
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
