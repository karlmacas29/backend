<?php

namespace App\Http\Controllers\rater;

use criteria;
use App\Models\User;
use App\Models\Submission;
use App\Http\Controllers\Controller;
use App\Models\criteria\criteria_rating;
use Illuminate\Support\Facades\Auth;

class rater_controller extends Controller
{

     // fetch  the job post on the table of rater 
    public function getAssignedJobs()
    {
        $user = Auth::user();
        // Get only the assigned job batches for the authenticated user
        $assignedJobs = $user->job_batches_rsp()->get();
        return response()->json([
            'status' => true,
            'assigned_jobs' => $assignedJobs,
        ]);
    }


    // public function get_criteria_applicant($id)
    // {
    //     // ✅ Get criteria for the selected job post, with related fields
    //     $criteria = criteria_rating::with(['educations', 'experiences', 'trainings', 'performances', 'behaviorals']) // add more if needed
    //         ->where('job_batches_rsp_id', $id)
    //         ->get(); // Assuming one criteria per job post

    //     // ✅ Get applicants who submitted to the job post
    //     $submissions = Submission::with('nPersonalInfo')
    //         ->where('job_batches_rsp_id', $id)
    //         ->get();

    //     // ✅ Format applicants
    //     $applicants = $submissions->map(function ($submission) {
    //         return [
    //             'id' => $submission->id,
    //             'firstname' => $submission->nPersonalInfo->firstname ?? '',
    //             'lastname' => $submission->nPersonalInfo->lastname ?? '',
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'criteria' => $criteria, // Include all related criteria here
    //         'applicants' => $applicants,
    //     ]);

     // rater criteria
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
            'nPersonalInfo.eligibity'
        ])
            ->where('job_batches_rsp_id', $id)
            ->where('status', 'qualified') // Only qualified applicants
            ->get();

        // Format applicants
        $applicants = $submissions->map(function ($submission) {
            $info = $submission->nPersonalInfo;

            return [
                'id' => $submission->id,
                'firstname' => $info->firstname ?? '',
                'lastname' => $info->lastname ?? '',
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


    public function get_all_raters()
    {
        try {
            $users = User::where('role_id', 2)
                ->orderBy('created_at', 'desc') // Order by latest created first
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'job_batches_rsp' => $user->job_batches_rsp->pluck('Position')->implode(', '),
                        'office' => $user->office,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'), // Include created date
                        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'), // Include updated date
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


// public function get_criteria_applicant($id)
// {
//     // Get criteria for the selected job post, with related fields
//     $criteria = criteria_rating::with(['educations', 'experiences', 'trainings', 'performances', 'behaviorals'])
//         ->where('job_batches_rsp_id', $id)
//         ->get();

//     // Get applicants who submitted to the job post with their detailed information
//     $submissions = Submission::with([
//         'nPersonalInfo',
//         'nPersonalInfo.education',
//             'nPersonalInfo.work_experience',
//             'nPersonalInfo.training',
//             'nPersonalInfo.eligibity'
//     ])
//         ->where('job_batches_rsp_id', $id)
//         ->get();

//     // Format applicants with detailed information
//     $applicants = $submissions->map(function ($submission) {
//         $personalInfo = $submission->nPersonalInfo;

//         return [
//             'id' => $submission->id,
//             'firstname' => $personalInfo->firstname ?? '',
//             'lastname' => $personalInfo->lastname ?? '',
//             'education' => $personalInfo->nEducations ? $personalInfo->nEducations->map(function($edu) {
//                 return [
//                     'school_name' => $edu->school_name,
//                     'degree' => $edu->degree,
//                     'attendance_from' => $edu->attendance_from,
//                     'attendance_to' => $edu->attendance_to,
//                     'year_graduated' => $edu->year_graduated,
//                     'scholarship' => $edu->scholarship,
//                     'level' => $edu->level
//                 ];
//             })->toArray() : [],
//             'work_experience' => $personalInfo->nWorkExperiences ? $personalInfo->nWorkExperiences->map(function($work) {
//                 return [
//                     'position_title' => $work->position_title,
//                     'department' => $work->department,
//                     'work_date_from' => $work->work_date_from,
//                     'work_date_to' => $work->work_date_to,
//                     'monthly_salary' => $work->monthly_salary,
//                     'status_of_appointment' => $work->status_of_appointment,
//                     'government_service' => $work->government_service
//                 ];
//             })->toArray() : [],
//             'training' => $personalInfo->nTrainings ? $personalInfo->nTrainings->map(function($training) {
//                 return [
//                     'training_title' => $training->training_title,
//                     'inclusive_date_from' => $training->inclusive_date_from,
//                     'inclusive_date_to' => $training->inclusive_date_to,
//                     'number_of_hours' => $training->number_of_hours,
//                     'type' => $training->type,
//                     'conducted_by' => $training->conducted_by
//                 ];
//             })->toArray() : [],
//             'eligibity' => $personalInfo->nEligibilities ? $personalInfo->nEligibilities->map(function($eligibility) {
//                 return [
//                     'eligibility' => $eligibility->eligibility,
//                     'rating' => $eligibility->rating,
//                     'date_of_examination' => $eligibility->date_of_examination,
//                     'place_of_examination' => $eligibility->place_of_examination,
//                     'license_number' => $eligibility->license_number,
//                     'date_of_validity' => $eligibility->date_of_validity
//                 ];
//             })->toArray() : []
//         ];
//     });

//     return response()->json([
//         'status' => true,
//         'criteria' => $criteria,
//         'applicants' => $applicants,
//     ]);
// }


    // fetch  user rating
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
}
