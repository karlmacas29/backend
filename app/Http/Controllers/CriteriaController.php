<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CriteriaRequest;
use App\Models\criteria\criteria_rating;

class CriteriaController extends Controller
{

    // creating a criteria per job post and if the job post already have criteria then try to create a new one criteria for that post it will be update the old criteria
    public function  store(CriteriaRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();
        $results = [];

        foreach ($validated['job_batches_rsp_id'] as $jobId) {
            // Update if exists, otherwise create
            $criteria = criteria_rating::updateOrCreate(
                ['job_batches_rsp_id' => $jobId],
                ['status' => 'created'] // or whatever status you want to set
            );

            // Log creation or update
            activity($user->name)
                ->causedBy($user)
                ->performedOn($criteria)
                ->log($criteria->wasRecentlyCreated ? 'Created criteria' : 'Updated criteria');


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
        $jobIds = collect($results)->pluck('job_batches_rsp_id')->toArray();

        return response()->json([
            'message' => "Criteria stored for {$count} job(s): " . implode(', ', $jobIds),
            'criteria' => $results,
        ]);
    }

    // deleting the criteria of job_post
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
