<?php

namespace App\Http\Controllers;

use App\Models\OnFundedPlantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OnFundedPlantillaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plantillas = OnFundedPlantilla::all();
        return response()->json([
            'status' => 'success',
            'data' => $plantillas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PositionID' => 'required|string',
            'file' => 'nullable|mimes:pdf|max:5120' // 5MB max size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $plantilla = new OnFundedPlantilla();
        $plantilla->PositionID = $request->PositionID;

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store file in the 'public/plantilla_files' directory
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla record created successfully',
            'data' => $plantilla
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $plantilla = OnFundedPlantilla::find($id);

        if (!$plantilla) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plantilla record not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $plantilla
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'PositionID' => 'sometimes|required|string',
            'file' => 'nullable|mimes:pdf|max:5120' // 5MB max size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $plantilla = OnFundedPlantilla::find($id);

        if (!$plantilla) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plantilla record not found'
            ], 404);
        }

        if ($request->has('PositionID')) {
            $plantilla->PositionID = $request->PositionID;
        }

        // Handle file upload if present
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($plantilla->fileUpload && Storage::disk('public')->exists($plantilla->fileUpload)) {
                Storage::disk('public')->delete($plantilla->fileUpload);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store file in the 'public/plantilla_files' directory
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla record updated successfully',
            'data' => $plantilla
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plantilla = OnFundedPlantilla::find($id);

        if (!$plantilla) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plantilla record not found'
            ], 404);
        }

        // Delete file if exists
        if ($plantilla->fileUpload && Storage::disk('public')->exists($plantilla->fileUpload)) {
            Storage::disk('public')->delete($plantilla->fileUpload);
        }

        $plantilla->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Plantilla record deleted successfully'
        ]);
    }
}
