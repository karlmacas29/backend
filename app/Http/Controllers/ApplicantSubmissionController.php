<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Imports\ApplicantFormImport;
use App\Models\excel\nPersonal_info;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ApplicantSubmissionController extends Controller
{

    // this function saving a information of applicant using excel  and get the job_post_id the will be save on submission  pivot table
    public function store(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',

        ]);

        $file = $request->file('excel_file');
        $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

        try {
            // Try to import using the uploaded file directly
            // Excel::import(new ApplicantFormImport($validated['job_batches_rsp_id']), $file);
            $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $fileName);
            Excel::import($import, $file);

            // Only save the file if import was successful
            $file->storeAs('excels', $fileName);

            return response()->json([
                'message' => 'Applicant submissions imported successfully.',
                'excel_file_name' => $fileName
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed during Excel import.',
                'errors' => $e->failures()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to import Excel file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    //get the image value..
    public function read_excel()
    {
        $excel_file = base_path('/storage/app/public/excels/1754817018_DfVtG272.xlsx');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($excel_file);
        $sheet = $spreadsheet->getActiveSheet();

        $drawings = $sheet->getDrawingCollection();

        foreach ($drawings as $index => $drawing) {
            $coordinates = $drawing->getCoordinates();

            // Check if it's a file-based drawing or memory-based
            if (method_exists($drawing, 'getPath')) {
                $drawing_path = $drawing->getPath(); // "zip://" path
                $extension = pathinfo($drawing_path, PATHINFO_EXTENSION);

                // Ensure images directory exists
                $image_dir = public_path('storage/images');
                if (!file_exists($image_dir)) {
                    mkdir($image_dir, 0777, true);
                }

                $filename = $coordinates . '_' . $index . '.' . $extension;
                $save_path = $image_dir . '/' . $filename;

                $contents = file_get_contents($drawing_path);
                file_put_contents($save_path, $contents);

                $public_url = asset('storage/images/' . $filename);

                // ✅ Properly echo coordinates and path
                echo "<p>Coordinate: <strong>{$coordinates}</strong></p>";
                echo "<p>Drawing Path: <code>{$drawing_path}</code></p>";
                echo "<img src='{$public_url}' style='max-width:200px'><br><br>";
            } else {
                echo "<p>Drawing at {$coordinates} is not file-based (probably MemoryDrawing).</p>";
            }
        }
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv',
    //         'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // if you're also uploading images directly
    //     ]);

    //     // Handle image upload if present
    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('applicant_images', 'public');
    //         // You can pass this to your import if needed
    //     }

    //     Excel::import(new ApplicantFormImport, $request->file('excel_file'));
    //     return response()->json(['message' => 'Applicant submissions imported successfully.']);
    // }
    // Delete a user and associated rspControl data
    // public function deleteUser($id)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $user = nPersonal_info::findOrFail($id);
    //         // Prevent deleting currently authenticated user
    //         // Delete associated rspControl first (if not using cascading deletes)
    //         if ($user->rspControl) {
    //             $user->rspControl->delete();
    //         }
    //         // Delete the user
    //         $user->delete();
    //         DB::commit();
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'User and associated permissions deleted successfully'
    //         ]);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not found'
    //         ], 404);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to delete user',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // this function is to delete all applicant on the excel
    public function deleteAllUsers()
    {
        try {
            DB::beginTransaction();

            $users = nPersonal_info::all();

            foreach ($users as $user) {
                if ($user->uploaded_file_image) {
                    $fileName = $user->uploaded_file_image->excel_file_name;
                    if ($fileName && Storage::exists('excels/' . $fileName)) {
                        Storage::delete('excels/' . $fileName);
                    }

                    $user->uploaded_file_image->delete();
                }

                $user->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'All users and their associated data deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
