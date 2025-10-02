<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ElectiveController extends Controller
{
    //

    public function elective(Request $request){



        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'PositionID' => 'required|string',
            'PageNo'     => 'required|string|max:255',
            'ItemNo'     => 'required|string', // Added ItemNo validation
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $positionId = $request->input('PositionID');
        $pageNo = $request->input('PageNo');
        $itemNo = $request->input('ItemNo'); // Get ItemNo from request

        try {

            // ✅ Check if PageNo already exists for another record
            $exists = DB::table('tblStructureDetails')
                ->where('PageNo', $pageNo)
                ->where('ItemNo', $itemNo)
                ->where('PositionID', '!=', $positionId) // exclude current record
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'PageNo and ItemNo already exist for another position. Update not allowed.'
                ], 422);
            }


            $updatedCount = DB::table('tblStructureDetails')
                ->where('PositionID', $positionId)
                ->where('ItemNo', $itemNo) // Added ItemNo to the where clause
                ->update(['PageNo' => $pageNo]);

            if ($updatedCount > 0) {
                return response()->json(['message' => 'PageNo updated successfully!'], 200);
            } else {
                return response()->json(['message' => 'Record not found for the given PositionID and ItemNo, or no changes were made to PageNo.'], 404);
            }
        } catch (\Exception $e) {
            // Log::error('Error updating PageNo: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the PageNo.'], 500);
        }

    }
}
