<?php

namespace App\Http\Controllers;

use App\Models\OnCriteriaJob;
use Illuminate\Http\Request;

class OnCriteriaJobController extends Controller
{
    // List all
    public function index()
    {
        return response()->json(OnCriteriaJob::all());
    }

    // Create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'PositionID' => 'nullable|integer',
            'ItemNo' => 'nullable|integer', // Added ItemNo validation
            'EduPercent' => 'nullable|string',
            'EliPercent' => 'nullable|string',
            'TrainPercent' => 'nullable|string',
            'ExperiencePercent' => 'nullable|string',
            'Education' => 'nullable|string',
            'Eligibility' => 'nullable|string',
            'Training' => 'nullable|string',
            'Experience' => 'nullable|string',
        ]);

        $criteria = OnCriteriaJob::create($validated);

        return response()->json($criteria, 201);
    }

    // Read single
    public function show($PositionID, $ItemNo)
    {
        $criteria = OnCriteriaJob::where('PositionID', $PositionID)
            ->where('ItemNo', $ItemNo)
            ->firstOrFail();
        return response()->json($criteria);
    }

    // Update
    public function update(Request $request, $id)
    {
        $criteria = OnCriteriaJob::findOrFail($id);

        $validated = $request->validate([
            'PositionID' => 'nullable|integer',
            'ItemNo' => 'nullable|integer', // Added ItemNo validation
            'EduPercent' => 'nullable|string',
            'EliPercent' => 'nullable|string',
            'TrainPercent' => 'nullable|string',
            'ExperiencePercent' => 'nullable|string',
            'Education' => 'nullable|string',
            'Eligibility' => 'nullable|string',
            'Training' => 'nullable|string',
            'Experience' => 'nullable|string',
        ]);

        $criteria->update($validated);

        return response()->json($criteria);
    }

    // Delete
    public function destroy($id)
    {
        $criteria = OnCriteriaJob::findOrFail($id);
        $criteria->delete();

        return response()->json(null, 204);
    }
}
