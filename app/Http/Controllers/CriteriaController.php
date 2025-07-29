<?php

namespace App\Http\Controllers;

use App\Models\criteria\c_behavioral_bei;
use App\Models\criteria\c_education;
use App\Models\criteria\c_experience;
use App\Models\criteria\c_performance;
use App\Models\criteria\c_training;
use App\Models\criteria\criteria_rating;
use Illuminate\Http\Request;

class CriteriaController extends Controller
{
    // //
    // protected $criteria;

    // public function __construct($criteria)
    // {
    //     $this->criteria = $criteria;
    // }

    // public function store_criteria(Request $request)
    // {
    //     $validated = $request->validate([
    //         // 'job_batches_rsp_id' => 'required|integer',
    //         'job_batches_rsp_id' => 'required|array',
    //         'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',

    //         'education.Rate' => 'required|string',
    //         'education.description' => 'required|array',
    //         'education.description.*' => 'required|string',

    //         'experience.Rate' => 'required|string',
    //         'experience.description' => 'required|array',
    //         'experience.description.*' => 'required|string',

    //         'training.Rate' => 'required|string',
    //         'training.description' => 'required|array',
    //         'training.description.*' => 'required|string',

    //         'performance.Rate' => 'required|string',
    //         'performance.description' => 'required|array',
    //         'performance.description.*' => 'required|string',

    //         'behavioral.Rate' => 'required|string',
    //         'behavioral.description' => 'required|array',
    //         'behavioral.description.*' => 'required|string',
    //     ]);


    //     // Find or create the main criteria_rating
    //     $criteria = criteria_rating::firstOrCreate(
    //         ['job_batches_rsp_id' => $validated['job_batches_rsp_id']]
    //     );

    //     // Now update or create education

    //     $criteria->educations()->updateOrCreate(
    //         ['criteria_rating_id' => $criteria->id],
    //         [
    //             'Rate' => $request->education['Rate'],
    //             'description' => implode(', ', $request->education['description']),
    //         ]
    //     );

    //     $criteria->experiences()->updateOrCreate(
    //         ['criteria_rating_id' => $criteria->id],
    //         [
    //             'Rate' => $request->experience['Rate'],
    //             'description' => implode(', ', $request->experience['description']),
    //         ]
    //     );
    //     $criteria->trainings()->updateOrCreate(
    //         ['criteria_rating_id' => $criteria->id],
    //         [
    //             'Rate' => $request->training['Rate'],
    //             'description' => implode(', ', $request->training['description']),
    //         ]
    //     );
    //     $criteria->performances()->updateOrCreate(
    //         ['criteria_rating_id' => $criteria->id],
    //         [
    //             'Rate' => $request->performance['Rate'],
    //             'description' => implode(', ', $request->performance['description']),
    //         ]
    //     );
    //     $criteria->behaviorals()->updateOrCreate(
    //         ['criteria_rating_id' => $criteria->id],
    //         [
    //             'Rate' => $request->behavioral['Rate'],
    //             'description' => implode(', ', $request->behavioral['description']),
    //         ]
    //     );


    //     return response()->json([
    //         'message' => $criteria->wasRecentlyCreated ? 'Created new criteria' : 'Updated existing criteria',
    //         'criteria' => $criteria->load([
    //             'educations',
    //             'experiences',
    //             'trainings',
    //             'performances',
    //             'behaviorals',
    //         ]),
    //     ]);
    // }

    public function store_criteria(Request $request)
    {
        $validated = $request->validate([
            'job_batches_rsp_id' => 'required|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',

            'education.Rate' => 'required|string',
            'education.description' => 'required|array',
            'education.description.*' => 'required|string',

            'experience.Rate' => 'required|string',
            'experience.description' => 'required|array',
            'experience.description.*' => 'required|string',

            'training.Rate' => 'required|string',
            'training.description' => 'required|array',
            'training.description.*' => 'required|string',

            'performance.Rate' => 'required|string',
            'performance.description' => 'required|array',
            'performance.description.*' => 'required|string',

            'behavioral.Rate' => 'required|string',
            'behavioral.description' => 'required|array',
            'behavioral.description.*' => 'required|string',
        ]);

        $results = [];

        foreach ($validated['job_batches_rsp_id'] as $jobId) {
            $criteria = criteria_rating::firstOrCreate([
                'job_batches_rsp_id' => $jobId
            ]);

            $criteria->educations()->updateOrCreate(
                ['criteria_rating_id' => $criteria->id],
                [
                    'Rate' => $request->education['Rate'],
                    'description' => implode(', ', $request->education['description']),
                ]
            );

            $criteria->experiences()->updateOrCreate(
                ['criteria_rating_id' => $criteria->id],
                [
                    'Rate' => $request->experience['Rate'],
                    'description' => implode(', ', $request->experience['description']),
                ]
            );

            $criteria->trainings()->updateOrCreate(
                ['criteria_rating_id' => $criteria->id],
                [
                    'Rate' => $request->training['Rate'],
                    'description' => implode(', ', $request->training['description']),
                ]
            );

            $criteria->performances()->updateOrCreate(
                ['criteria_rating_id' => $criteria->id],
                [
                    'Rate' => $request->performance['Rate'],
                    'description' => implode(', ', $request->performance['description']),
                ]
            );

            $criteria->behaviorals()->updateOrCreate(
                ['criteria_rating_id' => $criteria->id],
                [
                    'Rate' => $request->behavioral['Rate'],
                    'description' => implode(', ', $request->behavioral['description']),
                ]
            );

            $results[] = $criteria->load([
                'educations',
                'experiences',
                'trainings',
                'performances',
                'behaviorals',
            ]);
        }

        $count = count($results);
        $jobIds = array_column($results, 'job_batches_rsp_id');

        return response()->json([
            'message' => "Criteria stored for {$count} job(s): " . implode(', ', $jobIds),
            'criteria' => $results,
        ]);
    }



    public function delete($id)
    {
        $criteria = criteria_rating::find($id);

        if (!$criteria) {
            return response()->json([
                'status' => false,
                'message' => 'Criteria not found.'
            ], 404);
        }

        $criteria->delete();

        return response()->json([
            'status' => true,
            'message' => 'Criteria deleted successfully.'
        ]);
    }



      // this is for view criteria on admin to view the criteria of the job post 
    public function view_criteria($job_batches_rsp_id)
    {
        // Find the criteria_rating record for this job_batches_rsp_id
        $criteria = criteria_rating::with([
            'educations',
            'experiences',
            'trainings',
            'performances',
            'behaviorals'
        ])->where('job_batches_rsp_id', $job_batches_rsp_id)->first();

        if (!$criteria) {
            return response()->json(['message' => 'No criteria found for this job'], 404);
        }

        return response()->json([
            'education'   => $criteria->educations,
            'experience'  => $criteria->experiences,
            'training'    => $criteria->trainings,
            'performance' => $criteria->performances,
            'behavioral'  => $criteria->behaviorals,
        ]);
    }
}
