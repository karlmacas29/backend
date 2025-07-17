<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\ApplicantFormImport;
use App\Models\nPersonal_info;
use Maatwebsite\Excel\Facades\Excel;

class ApplicantSubmissionController extends Controller
{
    //
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv',
    //     ]);

    //     Excel::import(new ApplicantFormImport, $request->file('excel_file'));
    //     return response()->json(['message' => 'Applicant submissions imported successfully.']);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // if you're also uploading images directly
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('applicant_images', 'public');
            // You can pass this to your import if needed
        }

        Excel::import(new ApplicantFormImport, $request->file('excel_file'));
        return response()->json(['message' => 'Applicant submissions imported successfully.']);
    }
    // Delete a user and associated rspControl data
    public function deleteUser($id)
    {
        try {
            DB::beginTransaction();

            $user = nPersonal_info::findOrFail($id);

            // Prevent deleting currently authenticated user


            // Delete associated rspControl first (if not using cascading deletes)
            if ($user->rspControl) {
                $user->rspControl->delete();
            }

            // Delete the user
            $user->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User and associated permissions deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
