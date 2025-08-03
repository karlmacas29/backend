<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnFundedPlantilla;
use Illuminate\Support\Facades\DB;
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

 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
            'PositionID' => 'required|string',
            'ItemNo' => 'nullable|string', // Added ItemNo validation
            'fileUpload' => 'nullable|mimes:pdf|max:5120' // 5MB max size. Changed 'file' to 'fileUpload'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed', // Added a general message
                'errors' => $validator->errors()
            ], 422);
        }

        $plantilla = new OnFundedPlantilla();
        $plantilla->PositionID = $request->PositionID;
        $plantilla->ItemNo = $request->ItemNo; // Assign ItemNo

        // Handle file upload if present
        if ($request->hasFile('fileUpload')) { // Changed 'file' to 'fileUpload'
            $file = $request->file('fileUpload'); // Changed 'file' to 'fileUpload'
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store file in the 'public/plantilla_files' directory
            // Ensure this path is symlinked: php artisan storage:link
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        } else {
            // Optional: Log if no file is received when one might be expected
            // \Log::info('No fileUpload field present in the request for PositionID: ' . $request->PositionID);
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
            'ItemNo' => 'sometimes|nullable|string', // Added ItemNo validation
            'fileUpload' => 'nullable|mimes:pdf|max:5120' // 5MB max size. Changed 'file' to 'fileUpload'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed', // Added a general message
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

        if ($request->has('ItemNo')) { // Check if ItemNo is in request
            $plantilla->ItemNo = $request->ItemNo; // Assign ItemNo if present
        }

        // Handle file upload if present
        if ($request->hasFile('fileUpload')) { // Changed 'file' to 'fileUpload'
            // Delete old file if exists
            if ($plantilla->fileUpload && Storage::disk('public')->exists($plantilla->fileUpload)) {
                Storage::disk('public')->delete($plantilla->fileUpload);
            }

            $file = $request->file('fileUpload'); // Changed 'file' to 'fileUpload'
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store file in the 'public/plantilla_files' directory
            // Ensure this path is symlinked: php artisan storage:link
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
    /**
     * Display the specified resource by PositionID.
     *
     * @param  string  $positionID
     * @return \Illuminate\Http\Response
     */
    public function showByFunded($positionID, $itemNO)
    {
        $plantilla = OnFundedPlantilla::where('PositionID', $positionID)
            ->where('ItemNo', $itemNO)
            ->first();

        if (!$plantilla) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plantilla record not found for the given PositionID and ItemNo'
            ], 404);
        }

        if (!$plantilla->fileUpload) {
            return response()->json([
                'status' => 'error',
                'message' => 'No file associated with this Plantilla record for the given PositionID and ItemNo'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $plantilla
        ]);
    }

    public function deleteAllPlantillas()
    {
        try {
            DB::beginTransaction();

            $plantillas = OnFundedPlantilla::all();

            foreach ($plantillas as $plantilla) {
                // Delete file if it exists
                if ($plantilla->fileUpload && Storage::disk('public')->exists($plantilla->fileUpload)) {
                    Storage::disk('public')->delete($plantilla->fileUpload);
                }

                // Delete related models here if needed (example: $plantilla->positions()->delete();)

                $plantilla->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'All Plantilla records and associated files deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Plantilla records',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
