<?php

namespace App\Http\Controllers;

use App\Models\RatersBatch;
use Illuminate\Http\Request;

class RatersBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //get in api
    public function index()
    {
        $ratersBatch = RatersBatch::all();

        // Decode `position` JSON into an array for display
        $ratersBatch->transform(function ($batch) {
            $batch->position = json_decode($batch->position);
            return $batch;
        });

        return response()->json($ratersBatch);
    }

    /**
     * Store a newly created resource in storage.
     */
    //post in api
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'raters' => 'required|string',
            'assign_batch' => 'required|date',
            'position' => 'required|array', // Validate as an array
            'office' => 'required|string',
            'pending' => 'required|integer',
            'completed' => 'required|integer',
        ]);

        // Convert `position` to JSON and save it
        $validatedData['position'] = json_encode($validatedData['position']);

        $ratersBatch = RatersBatch::create($validatedData);

        return response()->json($ratersBatch, 201); // Respond in JSON format
    }

    /**
     * Display the specified resource.
     */
    //get (i believe where id = id)
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    //put
    public function update(Request $request, string $id)
    {
        $ratersBatch = RatersBatch::findOrFail($id);

        $validatedData = $request->validate([
            'raters' => 'string',
            'assign_batch' => 'date',
            'position' => 'string',
            'office' => 'string',
            'pending' => 'integer',
            'completed' => 'integer',
        ]);

        $ratersBatch->update($validatedData);

        return response()->json($ratersBatch, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //delete
    public function destroy(string $id)
    {
        //
    }
}
