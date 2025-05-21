<?php

namespace App\Http\Controllers;

use App\Models\JobBatchesRsp;
use Illuminate\Http\Request;

class JobBatchesRspController extends Controller
{
    // List all
    public function index()
    {
        return response()->json(JobBatchesRsp::orderBy('created_at', 'asc')->get());
    }

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
        ]);

        // No default for post_date or end_date; must be set explicitly if required
        $jobBatch = JobBatchesRsp::create($validated);

        return response()->json($jobBatch, 201);
    }

    // Read single by PositionID and ItemNo
    public function show($PositionID, $ItemNo)
    {
        // Ensure you have `use Illuminate\Support\Facades\DB;` at the top of your file.
        $jobBatches = \Illuminate\Support\Facades\DB::select('SELECT * FROM job_batches_rsp WHERE PositionID = ? AND ItemNo = ?', [$PositionID, $ItemNo]);

        if (empty($jobBatches)) {
            return response()->json(['error' => 'No matching record found'], 404);
        }

        // DB::select returns an array of objects, so we take the first one.
        return response()->json($jobBatches[0]);
    }

    // Update
    public function update(Request $request, $id)
    {
        $jobBatch = JobBatchesRsp::findOrFail($id);

        $validated = $request->validate([
            'Office' => 'nullable|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'sometimes|required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'PageNo' => 'nullable|string',
            'ItemNo' => 'nullable|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
        ]);

        $jobBatch->update($validated);

        return response()->json($jobBatch);
    }

    // Delete
    public function destroy($id)
    {
        $jobBatch = JobBatchesRsp::findOrFail($id);
        $jobBatch->delete();

        return response()->json(null, 204);
    }
}
