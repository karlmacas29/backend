<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Models\Submission;

use Illuminate\Support\Str;

use App\Models\ApplicantZip;
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
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',

    //     ]);

    //     $file = $request->file('excel_file');
    //     $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

    //     try {
    //         // Try to import using the uploaded file directly
    //         // Excel::import(new ApplicantFormImport($validated['job_batches_rsp_id']), $file);
    //         $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $fileName);
    //         Excel::import($import, $file);

    //         // Only save the file if import was successful
    //         $file->storeAs('excels', $fileName);

    //         return response()->json([
    //             'message' => 'Applicant submissions imported successfully.',
    //             'excel_file_name' => $fileName
    //         ]);

    //     } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
    //         return response()->json([
    //             'message' => 'Validation failed during Excel import.',
    //             'errors' => $e->failures()
    //         ], 422);


    //     } catch (\Exception $e) {
    //         // Check for missing personal info
    //         if (str_contains($e->getMessage(), 'Personal Information resubmit')) {
    //             return response()->json([
    //                 'message' => 'Personal Information resubmit — missing firstname or lastname.'
    //             ], 422);
    //         }

    //         return response()->json([
    //             'message' => 'Failed to import Excel file.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function applicant_store(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
            'zip_file' => 'required|file|mimes:zip', // ✅ ZIP now required
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $excelFile = $request->file('excel_file');
        $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

        try {
            // ✅ Import the Excel file
            $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
            Excel::import($import, $excelFile);

            // Store the Excel file
            $excelFile->storeAs('excels', $excelFileName);

            // ✅ Get the created nPersonal_info ID from import
            $nPersonalInfoId = $import->getPersonalInfoId();

            // ✅ Store ZIP file
            $zipFile = $request->file('zip_file');
            $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
            $zipPath = $zipFile->storeAs('zips', $zipFileName);

            // ✅ Save ZIP record linked to nPersonal_info
            $zipRecord = ApplicantZip::create([
                'nPersonalInfo_id' => $nPersonalInfoId,
                'zip_path' => $zipPath,
            ]);

            // Extract ZIP
            $zip = new ZipArchive;
            $zipFullPath = storage_path('app/public/' . $zipPath);

            // Check file exists
            if (!file_exists($zipFullPath)) {
                throw new \Exception("ZIP file not found at path: $zipFullPath");
            }

            // Open and extract
            if ($zip->open($zipFullPath) === true) {
                $extractPath = storage_path('app/public/applicant_files/' . $nPersonalInfoId);
                if (!file_exists($extractPath)) {
                    mkdir($extractPath, 0755, true);
                }

                $zip->extractTo($extractPath);
                $zip->close();


            } else {
                throw new \Exception('Failed to open ZIP file.');
            }

            return response()->json([
                'message' => 'Applicant imported successfully.',
                'excel_file_name' => $excelFileName,
                'nPersonalInfo_id' => $nPersonalInfoId,
                'zip' => [
                    'zip_file_name' => $zipFileName,
                    'zip_path' => $zipPath,
                    'zip_id' => $zipRecord->id,
                ],
                // 'images' => [
                //     'training' => $trainingImages,
                //     'education' => $educationImages,
                //     'experience' => $experienceImages,
                //     'voluntary' => $voluntaryImages,
                // ],
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed during Excel import.',
                'errors' => $e->failures()
            ], 422);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Personal Information resubmit')) {
                return response()->json([
                    'message' => 'Personal Information resubmit — missing firstname or lastname.'
                ], 422);
            }

            return response()->json([
                'message' => 'Failed to import Excel or ZIP file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function getImagesFromFolder($folderPath)
    {
        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

        if (!is_dir($folderPath)) {
            return $images;
        }

        $files = scandir($folderPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $folderPath . '/' . $file;

            // Check if it's a file and has image extension
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (in_array($extension, $allowedExtensions)) {
                    $images[] = [
                        'filename' => $file,
                        'path' => $filePath,
                        'relative_path' => str_replace(storage_path('app/public/'), '', $filePath),
                        'url' => asset('storage/' . str_replace(storage_path('app/public/'), '', $filePath)),
                    ];
                }
            }
        }

        return $images;
    }


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


    public function employee_applicant(Request $request) //  this store function to apply the employee on erms
    {
        $validated = $request->validate([
            'ControlNo' => 'required|nullable|string', // 👈 no longer required
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $submit = Submission::create([
            'ControlNo' => $validated['ControlNo'] ?? null,
            'status' => 'pending',
            'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
            'nPersonalInfo_id' => null, // 👈 explicitly null
        ]);

        return response()->json([
            'message' => 'Submission created successfully',
            'data' => $submit
        ], 201);
    }


    public function index()
    {
        $submission = Submission::all();

        return response()->json($submission);
    }
}
