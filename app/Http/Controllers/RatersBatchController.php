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

        // No need to decode `position` as it is now a string
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
            'position' => 'required|string', // Validate as a string
        ]);

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
            'position' => 'string', // Validate as a string
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
