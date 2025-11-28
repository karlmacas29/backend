<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CriteriaRequest;
use App\Models\library\CriteriaLibrary;
use App\Models\criteria\criteria_rating;


class CriteriaController extends Controller
{

    // creating a criteria per job post and if the job post already have criteria then try to create a new one criteria for that post it will be update the old criteria
    public function  store(CriteriaRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();
        $results = [];

        // foreach ($validated['job_batches_rsp_id'] as $jobId) {
        //     // Update if exists, otherwise create
        //     $criteria = criteria_rating::updateOrCreate(
        //         ['job_batches_rsp_id' => $jobId],
        //         ['status' => 'created'] // or whatever status you want to set
        //     );

        $jobId = $validated['job_batches_rsp_id'];


        $criteria = criteria_rating::updateOrCreate(
            ['job_batches_rsp_id' => $jobId],
            ['status' => 'created']
        );

        // DELETE old records
        $criteria->educations()->delete();
        $criteria->experiences()->delete();
        $criteria->trainings()->delete();
        $criteria->performances()->delete();
        $criteria->behaviorals()->delete();

        // INSERT new education
        foreach ($request->education as $item) {
            $criteria->educations()->create([
                'weight' => $item['weight'],
                'description' => $item['description'],
                'percentage' => $item['percentage']
            ]);
        }

        // INSERT new experience
        foreach ($request->experience as $item) {
            $criteria->experiences()->create([
                'weight' => $item['weight'],
                'description' => $item['description'],
                'percentage' => $item['percentage']
            ]);
        }

        // INSERT training
        foreach ($request->training as $item) {
            $criteria->trainings()->create([
                'weight' => $item['weight'],
                'description' => $item['description'],
                'percentage' => $item['percentage']
            ]);
        }

        // INSERT performance
        foreach ($request->performance as $item) {
            $criteria->performances()->create([
                'weight' => $item['weight'],
                'description' => $item['description'],
                'percentage' => $item['percentage']
            ]);
        }

        // INSERT behavioral
        foreach ($request->behavioral as $item) {
            $criteria->behaviorals()->create([
                'weight' => $item['weight'],
                'description' => $item['description'],
                'percentage' => $item['percentage']
            ]);
        }
        // Log::info('BEHAVIORAL DATA RECEIVED:', $request->behavioral);
        // Fetch the job details
        $job = \App\Models\JobBatchesRsp::find($criteria->job_batches_rsp_id);

        $jobPosition = $job->Position ?? 'N/A';
        $jobOffice = $job->Office ?? 'N/A';

        // Log creation or update with user and job info
        activity('Criteria')
            ->causedBy($user)
            ->performedOn($criteria)
            ->withProperties([
                'name' => $user->name,
                'job_position' => $jobPosition,
                'job_office' => $jobOffice,
                'status' => $criteria->status,
            ])
            ->log($criteria->wasRecentlyCreated
                ? "User '{$user->name}' created criteria for {$jobPosition} position in {$jobOffice}"
                : "User '{$user->name}' updated criteria for {$jobPosition} position in {$jobOffice}");

        return response()->json([
            'success' => true,
            'message' => "Criteria stored for  job",
            'criteria' => $results,
        ]);


    }




    // deleting the criteria of job_post
    public function delete($id)
    {

        $user = Auth::user(); // Get the authenticated user

        $criteria = criteria_rating::find($id);

        if (!$criteria) {
            return response()->json([
                'status' => false,
                'message' => 'Criteria not found.'
            ], 404);
        }

        $job = \App\Models\JobBatchesRsp::find($criteria->job_batches_rsp_id);
        $jobPosition = $job->Position ?? 'N/A';
        $jobOffice = $job->Office ?? 'N/A';

        $criteria->delete();


        // Log the deletion
        activity('Criteria')
            ->causedBy($user)
            ->performedOn($criteria)
            ->withProperties([
                'name' => $user->name,
                'job_position' => $jobPosition,
                'job_office' => $jobOffice,
            ])
            ->log("User '{$user->name}' deleted criteria for {$jobPosition} position in {$jobOffice}");

        return response()->json([
            'status' => true,
            'message' => 'Criteria deleted successfully.'
        ]);
    }

      // this is for view criteria on admin to view the criteria of the job post
    public function viewCriteria($job_batches_rsp_id)
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



    public function criteriaLibStore(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user
        // --------------------------
        // 1. VALIDATION
        // --------------------------
        $validated = $request->validate([
            'sg_min' => 'required|integer',
            'sg_max' => 'required|integer|gte:sg_min',

            'education.weight' => 'required|integer',
            'education.description' => 'required|array',
            'education.percentage.*' => 'required|integer',

            'experience.weight' => 'required|integer',
            'experience.description' => 'required|array',
            'experience.description.*' => 'required|string',
            'experience.percentage.*' => 'required|integer',

            'training.weight' => 'required|integer',
            'training.description' => 'required|array',
            'training.description.*' => 'required|string',
            'training.percentage.*' => 'required|integer',

            'performance.weight' => 'required|integer',
            'performance.description' => 'required|array',
            'performance.description.*' => 'required|string',
            'performance.percentage.*' => 'required|integer',

            'behavioral.weight' => 'required|integer',
            'behavioral.description' => 'required|array',
            'behavioral.description.*' => 'required|string',
            'behavioral.percentage.*' => 'required|integer'
        ]);

        // --------------------------
        // 2. FIND OR CREATE SG RANGE
        // --------------------------
        $sgMin = $validated['sg_min'];
        $sgMax = $validated['sg_max'];

        $criteriaRange = CriteriaLibrary::firstOrCreate(
            [
                'sg_min' => $sgMin,
                'sg_max' => $sgMax
            ],

        );

        // Clear old items if re-updating
        $criteriaRange->criteriaLibEducation()->delete();
        $criteriaRange->criteriaLibExperience()->delete();
        $criteriaRange->criteriaLibTraining()->delete();
        $criteriaRange->criteriaLibPerformance()->delete();
        $criteriaRange->criteriaLibBehavioral()->delete();

        // --------------------------
        // 3. SAVE EDUCATION
        // --------------------------
        foreach ($validated['education']['description'] as $index => $desc) {
            $criteriaRange->criteriaLibEducation()->updateOrCreate([
                'weight' => $validated['education']['weight'],
                'description' => $desc,
                'percentage' => $validated['education']['percentage'][$index],
            ]);
        }

        // --------------------------
        // 4. SAVE EXPERIENCE
        // --------------------------
        foreach ($validated['experience']['description'] as $index => $desc) {
            $criteriaRange->criteriaLibExperience()->updateOrCreate([
                'weight' => $validated['experience']['weight'],
                'description' => $desc,
                'percentage' => $validated['experience']['percentage'][$index],
            ]);
        }

        // --------------------------
        // 5. SAVE TRAINING
        // --------------------------
        foreach ($validated['training']['description'] as $index => $desc) {
            $criteriaRange->criteriaLibTraining()->updateOrCreate([
                'weight' => $validated['training']['weight'],
                'description' => $desc,
                'percentage' => $validated['training']['percentage'][$index],
            ]);
        }

        // --------------------------
        // 6. SAVE PERFORMANCE
        // --------------------------
        foreach ($validated['performance']['description'] as $index => $desc) {
            $criteriaRange->criteriaLibPerformance()->updateOrCreate([
                'weight' => $validated['performance']['weight'],
                'description' => $desc,
                'percentage' => $validated['performance']['percentage'][$index],
            ]);
        }

        // --------------------------
        // 7. SAVE BEHAVIORAL
        // --------------------------
        foreach ($validated['behavioral']['description'] as $index => $desc) {
            $criteriaRange->criteriaLibBehavioral()->updateOrCreate([
                'weight' => $validated['behavioral']['weight'],
                'description' => $desc,
                'percentage' => $validated['behavioral']['percentage'][$index],
            ]);
        }

        // --------------------------
        // 8. RESPONSE
        // --------------------------

        activity('Criteria Library')
            ->causedBy($user)
            ->performedOn($criteriaRange)
            ->withProperties([
                'performed_by' => $user->name,
                'sg_min' => $criteriaRange->sg_min,
                'sg_max' => $criteriaRange->sg_max,
            ])
            ->log("User '{$user->name}' created a new SG range: {$criteriaRange->sg_min}-{$criteriaRange->sg_max}.");

        return response()->json([
            'message' => 'Salary Grade Range Criteria saved successfully',
            'criteria_range_id' => $criteriaRange->id
        ], 201);
    }


    public function criteriaLibUpdate(Request $request, $criteriaId)
    {

        $user = Auth::user(); // Get the authenticated user
        // --------------------------
        // 1. VALIDATION
        // --------------------------
        $validated = $request->validate([
            'sg_min' => 'required|integer',
            'sg_max' => 'required|integer|gte:sg_min',

            'education' => 'required|array',
            'education.*.weight' => 'required|integer',
            'education.*.description' => 'required|string',
            'education.*.percentage' => 'required|integer',

            'experience' => 'required|array',
            'experience.*.weight' => 'required|integer',
            'experience.*.description' => 'required|string',
            'experience.*.percentage' => 'required|integer',

            'training' => 'required|array',
            'training.*.weight' => 'required|integer',
            'training.*.description' => 'required|string',
            'training.*.percentage' => 'required|integer',

            'performance' => 'required|array',
            'performance.*.weight' => 'required|integer',
            'performance.*.description' => 'required|string',
            'performance.*.percentage' => 'required|integer',

            'behavioral' => 'required|array',
            'behavioral.*.weight' => 'required|integer',
            'behavioral.*.description' => 'required|string',
            'behavioral.*.percentage' => 'required|integer',
        ]);

        // --------------------------
        // 2. FIND EXISTING RANGE
        // --------------------------
        $criteriaRange = CriteriaLibrary::findOrFail($criteriaId);

        // Update SG range values
        $criteriaRange->update([
            'sg_min' => $validated['sg_min'],
            'sg_max' => $validated['sg_max']
        ]);

        // --------------------------
        // Helper function to save category items
        // --------------------------
        $saveItems = function ($relation, $items) use ($criteriaRange) {
            $existingIds = [];

            foreach ($items as $item) {
                $record = $criteriaRange->$relation()->updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'weight' => $item['weight'], // from the item
                        'description' => $item['description'],
                        'percentage' => $item['percentage'],
                    ]
                );
                $existingIds[] = $record->id;
            }

            // Delete removed items
            $criteriaRange->$relation()->whereNotIn('id', $existingIds)->delete();
        };

        // --------------------------
        // Save all categories
        // --------------------------
        $saveItems('criteriaLibEducation', $validated['education']);
        $saveItems('criteriaLibExperience', $validated['experience']);
        $saveItems('criteriaLibTraining', $validated['training']);
        $saveItems('criteriaLibPerformance', $validated['performance']);
        $saveItems('criteriaLibBehavioral', $validated['behavioral']);



        activity('Criteria Library')
            ->causedBy($user)
            ->performedOn($criteriaRange)
            ->withProperties([
                'name' => $user->name,
                'sg_min' => $criteriaRange->sg_min,
                'sg_max' => $criteriaRange->sg_max,
            ])
            ->log("User '{$user->name}' updated the SG range to {$criteriaRange->sg_min}-{$criteriaRange->sg_max} for this criteria library.");
        // --------------------------
        // 3. LOAD UPDATED RELATIONS
        // --------------------------
        $criteriaRange->load([
            'criteriaLibEducation',
            'criteriaLibExperience',
            'criteriaLibTraining',
            'criteriaLibPerformance',
            'criteriaLibBehavioral',
        ]);

        // --------------------------
        // 4. FORMAT RESPONSE
        // --------------------------
        return response()->json([
            'sg_min' => $criteriaRange->sg_min,
            'sg_max' => $criteriaRange->sg_max,
            'created_at' => $criteriaRange->created_at,
            'updated_at' => $criteriaRange->updated_at,
            'education' => $criteriaRange->criteriaLibEducation,
            'experience' => $criteriaRange->criteriaLibExperience,
            'training' => $criteriaRange->criteriaLibTraining,
            'performance' => $criteriaRange->criteriaLibPerformance,
            'behavioral' => $criteriaRange->criteriaLibBehavioral,
        ], 200);
    }



    public function fetchCriteriaDetails($criteriaId)
    {
        $lib = CriteriaLibrary::with([
            'criteriaLibEducation:id,criteria_library_id,description,weight,percentage',
            'criteriaLibExperience:id,criteria_library_id,description,weight,percentage',
            'criteriaLibTraining:id,criteria_library_id,description,weight,percentage',
            'criteriaLibPerformance:id,criteria_library_id,description,weight,percentage',
            'criteriaLibBehavioral:id,criteria_library_id,description,weight,percentage',
        ])->findOrFail($criteriaId);

        // Format output
        $formatted = [
            'id' => $lib->id,
            'sg_min' => $lib->sg_min,
            'sg_max' => $lib->sg_max,
            'created_at' => $lib->created_at,
            'updated_at' => $lib->updated_at,
            'education' => $lib->criteriaLibEducation,
            'experience' => $lib->criteriaLibExperience,
            'training' => $lib->criteriaLibTraining,
            'performance' => $lib->criteriaLibPerformance,
            'behavioral' => $lib->criteriaLibBehavioral,
        ];

        return response()->json($formatted);
    }

    // fetch criteria base on the sg if the  job post are no criteria yet
    public function fetchNonCriteriaJob($sg)
    {
        $sg = (int) $sg; // force integer

        $lib = CriteriaLibrary::with([
            'criteriaLibEducation:id,criteria_library_id,description,weight,percentage',
            'criteriaLibExperience:id,criteria_library_id,description,weight,percentage',
            'criteriaLibTraining:id,criteria_library_id,description,weight,percentage',
            'criteriaLibPerformance:id,criteria_library_id,description,weight,percentage',
            'criteriaLibBehavioral:id,criteria_library_id,description,weight,percentage',
        ])
            ->where('sg_min', '<=', $sg)   // sg_min <= SG
            ->where('sg_max', '>=', $sg)   // sg_max >= SG
            ->first();

        if (!$lib) {
            return response()->json(['message' => 'Criteria not found'], 404);
        }

        return response()->json([
            'id' => $lib->id,
            'sg_min' => $lib->sg_min,
            'sg_max' => $lib->sg_max,
            'created_at' => $lib->created_at,
            'updated_at' => $lib->updated_at,
            'education' => $lib->criteriaLibEducation,
            'experience' => $lib->criteriaLibExperience,
            'training' => $lib->criteriaLibTraining,
            'performance' => $lib->criteriaLibPerformance,
            'behavioral' => $lib->criteriaLibBehavioral,
        ]);
    }



    public function fetchCriteriaLibrary()
    {
        $lib = CriteriaLibrary::all();

        return response()->json($lib);
    }

    public function criteriaDelete($criteriaId, Request $request)
    {
        $user = Auth::user(); // Get the authenticated user
        $criteria = CriteriaLibrary::findOrFail($criteriaId);

        $criteria->delete();

        // Activity log
        activity('Criteria Library')
            ->causedBy($user)
            ->performedOn($criteria)
            ->withProperties([
                'name' => $user->name,
            ])
            ->log("User '{$user->name}' deleted a criteria.");

        return response()->json([
            'message' => 'Criteria deleted successfully',
            'criteria' => $criteria
        ]);
    }
}
