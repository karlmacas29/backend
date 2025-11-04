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


    // public function applicant_store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
    //         'zip_file' => 'required|file|mimes:zip',
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     $excelFile = $request->file('excel_file');
    //     $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

    //     try {
    //         // ✅ Import Excel
    //         $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
    //         Excel::import($import, $excelFile);

    //         $excelFile->storeAs('excels', $excelFileName);

    //         $nPersonalInfoId = $import->getPersonalInfoId();

    //         // ✅ Store ZIP file
    //         $zipFile = $request->file('zip_file');
    //         $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
    //         $zipPath = $zipFile->storeAs('zips', $zipFileName);

    //         $zipRecord = ApplicantZip::create([
    //             'nPersonalInfo_id' => $nPersonalInfoId,
    //             'zip_path' => $zipPath,
    //         ]);

    //         // ✅ Extract ZIP
    //         $zip = new \ZipArchive;
    //         $zipFullPath = storage_path('app/public/' . $zipPath);

    //         if (!file_exists($zipFullPath)) {
    //             throw new \Exception("ZIP file not found at path: $zipFullPath");
    //         }

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

    //         // ✅ Fetch applicant info and job details
    //         $applicant = nPersonal_info::find($nPersonalInfoId);
    //         $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);

    //         if ($applicant && $applicant->email_address) {
    //             $subject = "Application Received";

    //             // 📨 Personalized email message
    //             $message = "
    //             Dear {$applicant->firstname} {$applicant->lastname},<br><br>
    //             Thank you for submitting your application for the position of
    //             <strong>{$job->Position}</strong> under the <strong>{$job->Office}</strong>.<br><br>
    //             We have successfully received your documents and your application is now being reviewed by our HR team.
    //             You will be notified once your evaluation has been completed.<br><br>
    //             Best regards,<br>
    //             Recruitment, Selection and Placement Team
    //         ";

    //             Mail::to($applicant->email_address)->queue(new EmailApi($message, $subject));
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Applicant imported successfully and confirmation email sent.',
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


    // // this function is to delete all applicant on the excel
    // public function deleteAllUsers()
    // {
    //     try {
    //         DB::beginTransaction();

    //         $users = nPersonal_info::all();

    //         foreach ($users as $user) {
    //             if ($user->uploaded_file_image) {
    //                 $fileName = $user->uploaded_file_image->excel_file_name;
    //                 if ($fileName && Storage::exists('excels/' . $fileName)) {
    //                     Storage::delete('excels/' . $fileName);
    //                 }

    //                 $user->uploaded_file_image->delete();
    //             }

    //             $user->delete();
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'All users and their associated data deleted successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to delete users',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
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


    public function applicant_store(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
            'zip_file' => 'required|file|mimes:zip',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $excelFile = $request->file('excel_file');
        $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

        // Track if this is an update or new application
        $isUpdate = false;
        $existingPersonalInfoId = null;

        try {
            // ✅ Import Excel FIRST to get the email from the file
            $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
            Excel::import($import, $excelFile);

            $excelFile->storeAs('excels', $excelFileName);

            $nPersonalInfoId = $import->getPersonalInfoId();

            // ✅ Get the newly imported applicant
            $applicant = nPersonal_info::find($nPersonalInfoId);

            if (!$applicant) {
                throw new \Exception('Applicant not found after import.');
            }


            // ✅ Check if this email already applied to this job
            $existingSubmission = Submission::whereHas('nPersonalInfo', function ($query) use ($applicant) {
                $query->where('email_address', $applicant->email_address);
            })->where('job_batches_rsp_id', $validated['job_batches_rsp_id'])
                ->first();

            if ($existingSubmission && $existingSubmission->nPersonalInfo_id != $nPersonalInfoId) {
                // ✅ DUPLICATE DETECTED - This is an update scenario
                $isUpdate = true;
                $existingPersonalInfoId = $existingSubmission->nPersonalInfo_id;
                $oldApplicant = $existingSubmission->nPersonalInfo;

                Log::info('Duplicate application detected', [
                    'email' => $applicant->email_address,
                    'job_id' => $validated['job_batches_rsp_id'],
                    'old_personal_info_id' => $existingPersonalInfoId,
                    'new_personal_info_id' => $nPersonalInfoId,
                ]);

                // ✅ Update the existing personal info with new data
                $oldApplicant->update([
                    'firstname' => $applicant->firstname,
                    'lastname' => $applicant->lastname,
                    'middlename' => $applicant->middlename ?? $oldApplicant->middlename,
                    'suffix' => $applicant->suffix ?? $oldApplicant->suffix,
                    'date_of_birth' => $applicant->date_of_birth ?? $oldApplicant->date_of_birth,
                    'sex' => $applicant->sex ?? $oldApplicant->sex,
                    'civil_status' => $applicant->civil_status ?? $oldApplicant->civil_status,
                    'citizenship' => $applicant->citizenship ?? $oldApplicant->citizenship,
                    'height' => $applicant->height ?? $oldApplicant->height,
                    'weight' => $applicant->weight ?? $oldApplicant->weight,
                    'blood_type' => $applicant->blood_type ?? $oldApplicant->blood_type,
                    'gsis_idno' => $applicant->gsis_idno ?? $oldApplicant->gsis_idno,
                    'pagibig_idno' => $applicant->pagibig_idno ?? $oldApplicant->pagibig_idno,
                    'philhealth_no' => $applicant->philhealth_no ?? $oldApplicant->philhealth_no,
                    'sss_no' => $applicant->sss_no ?? $oldApplicant->sss_no,
                    'tin' => $applicant->tin ?? $oldApplicant->tin,
                    'telephone_no' => $applicant->telephone_no ?? $oldApplicant->telephone_no,
                    'mobile_no' => $applicant->mobile_no ?? $oldApplicant->mobile_no,
                    // Add other fields you want to update
                ]);

                // ✅ Delete the newly created duplicate personal info
                $applicant->delete();

                // ✅ Use the existing personal info ID
                $nPersonalInfoId = $existingPersonalInfoId;
                $applicant = $oldApplicant->fresh();
            }

            // ✅ Store ZIP file
            $zipFile = $request->file('zip_file');
            $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
            $zipPath = $zipFile->storeAs('zips', $zipFileName);

            // ✅ Update or create ZIP record
            $zipRecord = ApplicantZip::updateOrCreate(
                ['nPersonalInfo_id' => $nPersonalInfoId],
                ['zip_path' => $zipPath]
            );

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
                } else {
                    // Clear old files and folders if updating
                    $this->deleteDirectory($extractPath, false); // false = don't delete the main folder
                }

                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('Failed to open ZIP file.');
            }

            // ✅ Fetch job details
            $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);

            // ✅ Send email notification
            if ($applicant && $applicant->email_address) {
                $subject = $isUpdate ? "Application Updated" : "Application Received";

                // 📨 Different message for updates vs new applications
                if ($isUpdate) {
                    $message = "
                    Dear {$applicant->firstname} {$applicant->lastname},<br><br>
                    Your application for the position of <strong>{$job->Position}</strong>
                    under the <strong>{$job->Office}</strong> has been <strong>successfully updated</strong>.<br><br>
                    We have received your updated documents and your application is being reviewed by our HR team.
                    You will be notified once your evaluation has been completed.<br><br>
                    Best regards,<br>
                    Recruitment, Selection and Placement Team
                ";
                } else {
                    $message = "
                    Dear {$applicant->firstname} {$applicant->lastname},<br><br>
                    Thank you for submitting your application for the position of
                    <strong>{$job->Position}</strong> under the <strong>{$job->Office}</strong>.<br><br>
                    We have successfully received your documents and your application is now being reviewed by our HR team.
                    You will be notified once your evaluation has been completed.<br><br>
                    Best regards,<br>
                    Recruitment, Selection and Placement Team
                ";
                }

                Mail::to($applicant->email_address)->queue(new EmailApi($message, $subject));
            }

            // ✅ Return appropriate response based on whether it's an update or new application
            return response()->json([
                'success' => true,
                'message' => $isUpdate
                    ? 'Your application has been successfully updated.'
                    : 'Applicant imported successfully and confirmation email sent.',
                'is_update' => $isUpdate,
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


    // public function applicant_store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
    //         'zip_file' => 'required|file|mimes:zip',
    //         'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
    //     ]);

    //     $excelFile = $request->file('excel_file');
    //     $excelFileName = time() . '_' . Str::random(8) . '.' . $excelFile->getClientOriginalExtension();

    //     // Track if this is an update or new application
    //     $isUpdate = false;
    //     $existingPersonalInfoId = null;

    //     try {
    //         // ✅ Import Excel FIRST to get the email from the file
    //         $import = new ApplicantFormImport($validated['job_batches_rsp_id'], null, $excelFileName);
    //         Excel::import($import, $excelFile);

    //         $excelFile->storeAs('excels', $excelFileName);

    //         $nPersonalInfoId = $import->getPersonalInfoId();

    //         // ✅ Get the newly imported applicant

    //         $applicant = nPersonal_info::find($nPersonalInfoId);

    //         if (!$applicant) {
    //             throw new \Exception('Applicant not found after import.');
    //         }
    //         // ✅ STEP 1: Count all ACTIVE applications of this applicant
    //         $activeApplications = Submission::whereHas('nPersonalInfo', function ($query) use ($applicant) {
    //             $query->where('email_address', $applicant->email_address);
    //         })
    //             ->whereHas('jobPost', function ($query) {
    //                 $query->whereNotIn('status', ['Occupied', 'Republished', 'Unoccupied']);
    //             })
    //             ->count();

    //         // ✅ STEP 2: If applicant already has 3 ACTIVE applications, stop immediately
    //         if ($activeApplications >= 3) {

    //             // ❗ Clean up the newly imported record to avoid duplicate junk
    //             $applicant->delete();

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'You already have 3 active job applications. You can only apply again once one of your applied job posts is marked as Occupied, Republished, or Unoccupied.',
    //             ], 403);
    //         }

    //         // ✅ Check if this email already applied to this job
    //         $existingSubmission = Submission::whereHas('nPersonalInfo', function ($query) use ($applicant) {
    //             $query->where('email_address', $applicant->email_address);
    //         })->where('job_batches_rsp_id', $validated['job_batches_rsp_id'])
    //             ->first();

    //         if ($existingSubmission && $existingSubmission->nPersonalInfo_id != $nPersonalInfoId) {
    //             // ✅ DUPLICATE DETECTED - This is an update scenario
    //             $isUpdate = true;
    //             $existingPersonalInfoId = $existingSubmission->nPersonalInfo_id;
    //             $oldApplicant = $existingSubmission->nPersonalInfo;

    //             Log::info('Duplicate application detected', [
    //                 'email' => $applicant->email_address,
    //                 'job_id' => $validated['job_batches_rsp_id'],
    //                 'old_personal_info_id' => $existingPersonalInfoId,
    //                 'new_personal_info_id' => $nPersonalInfoId,
    //             ]);

    //             // ✅ Update the existing personal info with new data
    //             $oldApplicant->update([
    //                 'firstname' => $applicant->firstname,
    //                 'lastname' => $applicant->lastname,
    //                 'middlename' => $applicant->middlename ?? $oldApplicant->middlename,
    //                 'suffix' => $applicant->suffix ?? $oldApplicant->suffix,
    //                 'date_of_birth' => $applicant->date_of_birth ?? $oldApplicant->date_of_birth,
    //                 'sex' => $applicant->sex ?? $oldApplicant->sex,
    //                 'civil_status' => $applicant->civil_status ?? $oldApplicant->civil_status,
    //                 'citizenship' => $applicant->citizenship ?? $oldApplicant->citizenship,
    //                 'height' => $applicant->height ?? $oldApplicant->height,
    //                 'weight' => $applicant->weight ?? $oldApplicant->weight,
    //                 'blood_type' => $applicant->blood_type ?? $oldApplicant->blood_type,
    //                 'gsis_idno' => $applicant->gsis_idno ?? $oldApplicant->gsis_idno,
    //                 'pagibig_idno' => $applicant->pagibig_idno ?? $oldApplicant->pagibig_idno,
    //                 'philhealth_no' => $applicant->philhealth_no ?? $oldApplicant->philhealth_no,
    //                 'sss_no' => $applicant->sss_no ?? $oldApplicant->sss_no,
    //                 'tin' => $applicant->tin ?? $oldApplicant->tin,
    //                 'telephone_no' => $applicant->telephone_no ?? $oldApplicant->telephone_no,
    //                 'mobile_no' => $applicant->mobile_no ?? $oldApplicant->mobile_no,
    //                 // Add other fields you want to update
    //             ]);

    //             // ✅ Delete the newly created duplicate personal info
    //             $applicant->delete();

    //             // ✅ Use the existing personal info ID
    //             $nPersonalInfoId = $existingPersonalInfoId;
    //             $applicant = $oldApplicant->fresh();
    //         }

    //         // ✅ Store ZIP file
    //         $zipFile = $request->file('zip_file');
    //         $zipFileName = time() . '_' . Str::random(8) . '.' . $zipFile->getClientOriginalExtension();
    //         $zipPath = $zipFile->storeAs('zips', $zipFileName);

    //         // ✅ Update or create ZIP record
    //         $zipRecord = ApplicantZip::updateOrCreate(
    //             ['nPersonalInfo_id' => $nPersonalInfoId],
    //             ['zip_path' => $zipPath]
    //         );

    //         // ✅ Extract ZIP
    //         $zip = new \ZipArchive;
    //         $zipFullPath = storage_path('app/public/' . $zipPath);

    //         if (!file_exists($zipFullPath)) {
    //             throw new \Exception("ZIP file not found at path: $zipFullPath");
    //         }

    //         if ($zip->open($zipFullPath) === true) {
    //             $extractPath = storage_path('app/public/applicant_files/' . $nPersonalInfoId);

    //             if (!file_exists($extractPath)) {
    //                 mkdir($extractPath, 0755, true);
    //             } else {
    //                 // Clear old files and folders if updating
    //                 $this->deleteDirectory($extractPath, false); // false = don't delete the main folder
    //             }

    //             $zip->extractTo($extractPath);
    //             $zip->close();
    //         } else {
    //             throw new \Exception('Failed to open ZIP file.');
    //         }

    //         // ✅ Fetch job details
    //         $job = \App\Models\JobBatchesRsp::find($validated['job_batches_rsp_id']);

    //         // ✅ Send email notification
    //         if ($applicant && $applicant->email_address) {
    //             $subject = $isUpdate ? "Application Updated" : "Application Received";

    //             // 📨 Different message for updates vs new applications
    //             if ($isUpdate) {
    //                 $message = "
    //                 Dear {$applicant->firstname} {$applicant->lastname},<br><br>
    //                 Your application for the position of <strong>{$job->Position}</strong>
    //                 under the <strong>{$job->Office}</strong> has been <strong>successfully updated</strong>.<br><br>
    //                 We have received your updated documents and your application is being reviewed by our HR team.
    //                 You will be notified once your evaluation has been completed.<br><br>
    //                 Best regards,<br>
    //                 Recruitment, Selection and Placement Team
    //             ";
    //             } else {
    //                 $message = "
    //                 Dear {$applicant->firstname} {$applicant->lastname},<br><br>
    //                 Thank you for submitting your application for the position of
    //                 <strong>{$job->Position}</strong> under the <strong>{$job->Office}</strong>.<br><br>
    //                 We have successfully received your documents and your application is now being reviewed by our HR team.
    //                 You will be notified once your evaluation has been completed.<br><br>
    //                 Best regards,<br>
    //                 Recruitment, Selection and Placement Team
    //             ";
    //             }

    //             Mail::to($applicant->email_address)->queue(new EmailApi($message, $subject));
    //         }

    //         // ✅ Return appropriate response based on whether it's an update or new application
    //         return response()->json([
    //             'success' => true,
    //             'message' => $isUpdate
    //                 ? 'Your application has been successfully updated.'
    //                 : 'Applicant imported successfully and confirmation email sent.',
    //             'is_update' => $isUpdate,
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
    private function deleteDirectory($dir, $deleteRoot = true)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path, true);
            } else {
                unlink($path);
            }
        }

        // Only delete the root directory if requested
        if ($deleteRoot) {
            return rmdir($dir);
        }

        return true;
    }
}
