<?php

namespace App\Http\Controllers;

use ZipArchive;
use App\Mail\EmailApi;

use App\Models\Submission;

use Illuminate\Support\Str;
use App\Models\ApplicantZip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Imports\ApplicantFormImport;
use App\Models\excel\nPersonal_info;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ApplicantSubmissionController extends Controller
{

    // // this function saving a information of applicant using excel  and get the job_post_id the will be save on submission  pivot table
    // public function applicant_store(Request $request) // old working
    // {
    //     $validated = $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
    //         'zip_file' => 'required|file|mimes:zip', // ✅ ZIP now required
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     $excelFile = $request->file('excel_file');
    //     $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

    //     try {
    //         // ✅ Import the Excel file
    //         $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
    //         Excel::import($import, $excelFile);

    //         // Store the Excel file
    //         $excelFile->storeAs('excels', $excelFileName);

    //         // ✅ Get the created nPersonal_info ID from import
    //         $nPersonalInfoId = $import->getPersonalInfoId();

    //         // ✅ Store ZIP file
    //         $zipFile = $request->file('zip_file');
    //         $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
    //         $zipPath = $zipFile->storeAs('zips', $zipFileName);

    //         // ✅ Save ZIP record linked to nPersonal_info
    //         $zipRecord = ApplicantZip::create([
    //             'nPersonalInfo_id' => $nPersonalInfoId,
    //             'zip_path' => $zipPath,
    //         ]);

    //         // Extract ZIP
    //         $zip = new ZipArchive;
    //         $zipFullPath = storage_path('app/public/' . $zipPath);

    //         // Check file exists
    //         if (!file_exists($zipFullPath)) {
    //             throw new \Exception("ZIP file not found at path: $zipFullPath");
    //         }

    //         // Open and extract
    //         if ($zip->open($zipFullPath) === true) {
    //             $extractPath = storage_path('app/public/applicant_files/' . $nPersonalInfoId);
    //             if (!file_exists($extractPath)) {
    //                 mkdir($extractPath, 0755, true);
    //             }

    //             $zip->extractTo($extractPath);
    //             $zip->close();


    //         } else {
    //             throw new \Exception('Failed to open ZIP file.');
    //         }

    //         return response()->json([
    //             'success'=> true,
    //             'message' => 'Applicant imported successfully.',
    //             'excel_file_name' => $excelFileName,
    //             'nPersonalInfo_id' => $nPersonalInfoId,
    //             'zip' => [
    //                 'zip_file_name' => $zipFileName,
    //                 'zip_path' => $zipPath,
    //                 'zip_id' => $zipRecord->id,
    //             ],

    //         ]);
    //     } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
    //         return response()->json([
    //             'message' => 'Validation failed during Excel import.',
    //             'errors' => $e->failures()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         if (str_contains($e->getMessage(), 'Personal Information validation failed')) {
    //             return response()->json([
    //                 'message' => $e->getMessage(),
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
            'zip_file' => 'required|file|mimes:zip',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $excelFile = $request->file('excel_file');
        $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

        try {
            // ✅ Import Excel
            $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
            Excel::import($import, $excelFile);

            $excelFile->storeAs('excels', $excelFileName);

            $nPersonalInfoId = $import->getPersonalInfoId();

            // ✅ Store ZIP file
            $zipFile = $request->file('zip_file');
            $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
            $zipPath = $zipFile->storeAs('zips', $zipFileName);

            $zipRecord = ApplicantZip::create([
                'nPersonalInfo_id' => $nPersonalInfoId,
                'zip_path' => $zipPath,
            ]);

            // ✅ Extract ZIP
            $zip = new \ZipArchive;
            $zipFullPath = storage_path('app/public/' . $zipPath);

            if (!file_exists($zipFullPath)) {
                throw new \Exception("ZIP file not found at path: $zipFullPath");
            }

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

            // ✅ Fetch applicant info and job details
            $applicant = nPersonal_info::find($nPersonalInfoId);
            $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);

            if ($applicant && $applicant->email_address) {
                $subject = "Application Received";

                // 📨 Personalized email message
                $message = "
                Dear {$applicant->firstname} {$applicant->lastname},<br><br>
                Thank you for submitting your application for the position of
                <strong>{$job->Position}</strong> under the <strong>{$job->Office}</strong>.<br><br>
                We have successfully received your documents and your application is now being reviewed by our HR team.
                You will be notified once your evaluation has been completed.<br><br>
                Best regards,<br>
                Recruitment, Selection and Placement Team
            ";

                Mail::to($applicant->email_address)->queue(new EmailApi($message, $subject));
            }

            return response()->json([
                'success' => true,
                'message' => 'Applicant imported successfully and confirmation email sent.',
                'excel_file_name' => $excelFileName,
                'nPersonalInfo_id' => $nPersonalInfoId,
                'zip' => [
                    'zip_file_name' => $zipFileName,
                    'zip_path' => $zipPath,
                    'zip_id' => $zipRecord->id,
                ],
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed during Excel import.',
                'errors' => $e->failures()
            ], 422);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Personal Information validation failed')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'message' => 'Failed to import Excel file.',
                'error' => $e->getMessage()
            ], 500);
        }
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


    // public function employee_applicant(Request $request) //  this store function to apply the employee on erms
    // {
    //     $validated = $request->validate([
    //         'ControlNo' => 'required|nullable|string', // 👈 no longer required
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     $submit = Submission::create([
    //         'ControlNo' => $validated['ControlNo'] ?? null,
    //         'status' => 'pending',
    //         'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
    //         'nPersonalInfo_id' => null, // 👈 explicitly null
    //     ]);


    //     return response()->json([
    //         'message' => 'Submission created successfully',
    //         'data' => $submit
    //     ], 201);
    // }

    // public function employee_applicant(Request $request)
    // {
    //     // ✅ Validate input
    //     $validated = $request->validate([
    //         'ControlNo' => 'nullable|string', // external applicants
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     // ✅ Create submission
    //     $submit = Submission::create([
    //         'ControlNo' => $validated['ControlNo'] ?? null,
    //         'status' => 'pending',
    //         'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
    //         'nPersonalInfo_id' => null, // external applicant
    //     ]);

    //     // ✅ Fetch external applicant email and name
    //     $controlNo = $validated['ControlNo'];
    //     $applicantEmail = DB::table('xPersonalAddt')->where('ControlNo', $controlNo)->first();
    //     $applicantName  = DB::table('xPersonal')->where('ControlNo', $controlNo)->first();

    //     // ✅ Determine email and fullname
    //     $email = $applicantEmail->EmailAdd ?? $applicantEmail->emailAdd ?? null;
    //     $fullname = $applicantName ? trim("{$applicantName->Firstname} {$applicantName->Surname}") : 'Applicant';

    //     // ✅ Fetch job details
    //     $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);
    //     $position = $job->Position ?? 'the applied position';
    //     $office   = $job->Office ?? 'the corresponding office';

    //     // ✅ Send confirmation email if email exists
    //     if (!empty($email)) {
    //         $subject = "Application Received";
    //         $message = "
    //         Dear {$fullname},<br><br>
    //         Your application for the position of <strong>{$position}</strong> under <strong>{$office}</strong> has been received successfully.<br>
    //         Please wait for further updates regarding your application.<br><br>
    //         Thank you for applying.
    //     ";

    //         Mail::to($email)->queue(new EmailApi($message, $subject));
    //         Log::info("✅ Email queued for external applicant: {$email}");
    //     } else {
    //         Log::warning("⚠️ External applicant has no email address. ControlNo: {$controlNo}");
    //     }

    //     return response()->json([
    //         'message' => 'Submission created successfully and email notification sent (if email exists)',
    //         'data' => $submit
    //     ], 201);
    // }


    public function index()
    {
        $submission = Submission::all();

        return response()->json($submission);
    }

    public function employee_applicant(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'ControlNo' => 'nullable|string',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $controlNo = $validated['ControlNo'] ?? null;

        // ✅ Create submission first
        $submit = Submission::create([
            'ControlNo' => $controlNo,
            'status' => 'pending',
            'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
            'nPersonalInfo_id' => null,
        ]);

        // ✅ Fetch applicant email and name in a single query
        $applicant = DB::table('xPersonalAddt')
            ->join('xPersonal', 'xPersonal.ControlNo', '=', 'xPersonalAddt.ControlNo')
            ->select(
                'xPersonalAddt.EmailAdd as email',
                'xPersonal.Firstname',
                'xPersonal.Surname'
            )
            ->where('xPersonalAddt.ControlNo', $controlNo)
            ->first();

        // ✅ Determine email and fullname
        $email = $applicant->email ?? null;
        $fullname = $applicant ? trim("{$applicant->Firstname} {$applicant->Surname}") : 'Applicant';

        // ✅ Fetch job in one call
        $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);
        $position = $job->Position ?? 'the applied position';
        $office   = $job->Office ?? 'the corresponding office';

        // ✅ Queue email if exists
        if (!empty($email)) {
            $subject = "Application Received";
            $message = "
            Dear {$fullname},<br><br>
            Your application for the position of <strong>{$position}</strong> under <strong>{$office}</strong> has been received successfully.<br>
            Please wait for further updates regarding your application.<br><br>
            Thank you for applying.
        ";
            Mail::to($email)->queue(new EmailApi($message, $subject));
            Log::info("Email queued for external applicant: {$email}");
        } else {
            Log::warning("External applicant has no email address. ControlNo: {$controlNo}");
        }

        return response()->json([
            'message' => 'Submission created successfully and email notification sent (if email exists)',
            'data' => $submit
        ], 201);
    }
}
