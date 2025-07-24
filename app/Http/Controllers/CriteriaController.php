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

    public function store_criteria(Request $request)
    {
        // Validate input for criteria_rating
        $validated = $request->validate([
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
            'education.Rate' => 'required',
            'education.Min_qualification'=> 'required',
            'education.Title' => 'required',
            'education.Description' => 'required',

            'experience.Rate' => 'required',
            'experience.Min_qualification' => 'required',
            'experience.Title' => 'required',
            'experience.Description' => 'required',

            'training.Rate' => 'required',
            'training.Title' => 'required',
            'training.Desription' => 'required',

            'performance.Rate' => 'required',
            'performance.Title' => 'required',
            'performance.Outstanding_rating' => 'required',
            'performance.Very_Satisfactory' => 'required',
            'performance.Below_rating' => 'required',


            'behavioral.Rate' => 'required',
            'behavioral.Title' => 'required',
            'behavioral.Desription' => 'required',
        ]);

        // Step 1: Create criteria_rating
        $criteria = criteria_rating::create([
            'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
        ]);

        // Step 2: Create c_education using the created criteria_rating ID
        $education = c_education::create([
            'criteria_rating_id' => $criteria->id,
            'Rate' => $request->education['Rate'],
            'Min_qualification' => $request->education['Min_qualification'],
            'Title' => $request->education['Title'],
            'Description' => $request->education['Description'],
        ]);

        $experience = c_experience::create([
            'criteria_rating_id' => $criteria->id,
            'Rate' => $request->experience['Rate'],
            'Min_qualification' =>$request->experienced['Min_qualification'],
            'Title' =>  $request->experience['Title'],

        ]);
        $training = c_training::create([
            'criteria_rating_id' => $criteria->id,
            'Rate' => $request->training['Rate'],
              'Title' =>  $request->training['Title'],
            'Description' => $request->training['Description'],
        ]);

        $performance = c_performance::create([
            'criteria_rating_id' => $criteria->id,
            'Rate' => $request->performance['Rate'],
            'Title' =>  $request->performance['Title'],
            'Outstanding_rating' => $request->performance['Outstanding_rating'],
            'Very_Satisfactory' => $request->performance['Very_Satisfactory'],
            'Below_rating' => $request->performance['Below_rating'],
        ]);

        $behavioral = c_behavioral_bei::create([
            'criteria_rating_id' => $criteria->id,
            'Rate' => $request->behavioral['Rate'],
            'Title' =>  $request->behavioral['Title'],
            'Description' => $request->behavioral['Description'],
        ]);

        return response()->json([
            'message' => 'Successfully saved criteria and education',
            'criteria' => $criteria,
            'education' => $education,
            'experience'=> $experience,
            'training'=> $training,
            'performance' => $performance,
            'behavioral' => $behavioral
        ]);
    }
}
