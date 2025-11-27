<?php

namespace App\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;

use App\Mail\EmailApi;

use App\Models\Submission;
use Illuminate\Support\Str;
use App\Models\ApplicantZip;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Imports\ApplicantDataImport;
use App\Imports\ApplicantFormImport;
use App\Models\excel\nPersonal_info;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ApplicantSubmissionController extends Controller
{

    // list of applicant
    public function listOfApplicants()
    {
        // Fetch all submissions including related info
        $submissions = Submission::with('nPersonalInfo:id,firstname,lastname,date_of_birth')->get();

        // Group by unique person using firstname, lastname, birthdate
        $submissions = $submissions->filter(function ($item) {
            return $item->nPersonalInfo !== null;
        });

        $applicants = $submissions
            ->groupBy(function ($item) {
                return strtolower(
                    $item->nPersonalInfo->firstname . '|' .
                        $item->nPersonalInfo->lastname . '|' .
                        $item->nPersonalInfo->date_of_birth
                );
            })

            ->map(function ($group) {
                $first = $group->first(); // main record for data output
                return [
                    'nPersonal_id'   => $first->nPersonalInfo->id,
                    'firstname'     => $first->nPersonalInfo->firstname,
                    'lastname'      => $first->nPersonalInfo->lastname,
                    'date_of_birth' => $first->nPersonalInfo->date_of_birth,
                    'jobpost'       => $group->count() // count how many submissions
                ];
            })
            ->values();

        return response()->json($applicants);
    }


    public function getApplicantDetails(Request $request) // applicant details
    {
        $validated = $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        // normalize input
        $firstname = trim(strtolower($validated['firstname']));
        $lastname = trim(strtolower($validated['lastname']));
        // ensure a Y-m-d string for whereDate
        $date_of_birth = \Carbon\Carbon::parse($validated['date_of_birth'])->toDateString();

        $applicants = Submission::select('id', 'nPersonalInfo_id', 'job_batches_rsp_id', 'status')
            ->whereHas('nPersonalInfo', function ($query) use ($firstname, $lastname, $date_of_birth) {
                $query->whereDate('date_of_birth', $date_of_birth)
                    ->where(function ($q) use ($firstname, $lastname) {
                        // normal order: firstname = input.firstname AND lastname = input.lastname
                        $q->whereRaw('LOWER(TRIM(firstname)) = ?', [$firstname])
                            ->whereRaw('LOWER(TRIM(lastname)) = ?', [$lastname]);
                        // OR swapped order: firstname = input.lastname AND lastname = input.firstname
                        $q->orWhereRaw('(LOWER(TRIM(firstname)) = ? AND LOWER(TRIM(lastname)) = ?)', [$lastname, $firstname]);
                    });
            })
            ->with([
                'nPersonalInfo:id,firstname,lastname,date_of_birth',
                'jobPost:id,Position,Office,SalaryGrade,salaryMin,salaryMax,status'
            ])
            ->get();

        if ($applicants->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No applicant found with the provided details.',
                'input' => [
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'date_of_birth' => $date_of_birth,
                ]
            ], 404);
        }

        return response()->json([
            'message' => 'Applicants retrieved successfully.',
            'count' => $applicants->count(),
            'data' => $applicants
        ]);
    }

    public function index()
    {
        $submission = Submission::all();

        return response()->json($submission);
    }


    public function employeeApplicant(Request $request)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'ControlNo' => 'required|string',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $controlNo = $validated['ControlNo'];
        $jobId     = $validated['job_batches_rsp_id'];

        // ✅ Fetch applicant info
        $applicant = DB::table('xPersonal')
            ->join('xPersonalAddt', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
            ->select(
                'xPersonal.Firstname',
                'xPersonal.Surname',
                'xPersonal.BirthDate',
                'xPersonalAddt.EmailAdd'
            )
            ->where('xPersonal.ControlNo', $controlNo)
            ->first();

        if (!$applicant) {
            return response()->json([
                'message' => 'Applicant not found.'
            ], 404);
        }

        // Convenience variables
        $firstname = $applicant->Firstname;
        $lastname  = $applicant->Surname;
        $birthdate = $applicant->BirthDate;

        $currentJob = JobBatchesRsp::findOrFail($jobId);


        // 2) GET ALL JOB POSTS WITH SAME START/END DATE

        $jobGroupIds = JobBatchesRsp::where('post_date', $currentJob->post_date)
            ->where('end_date', $currentJob->end_date)
            ->pluck('id');


        // 3) COUNT HOW MANY JOBS THE APPLICANT APPLIED WITHIN GROUP

        $applicationCount = DB::table('submission')
            ->join('xPersonal', 'xPersonal.ControlNo', '=', 'submission.ControlNo')
            ->whereIn('submission.job_batches_rsp_id', $jobGroupIds)
            ->where('xPersonal.Firstname', $firstname)
            ->where('xPersonal.Surname', $lastname)
            ->where('xPersonal.BirthDate', $birthdate)
            ->count();

        $post_date = Carbon::parse($currentJob->post_date)->format('F d, Y');
        $end_date   = Carbon::parse($currentJob->end_date)->format('F d, Y');


        if ($applicationCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => "$firstname $lastname, You have already applied for 3 job posts with the same application period (" .
                    $post_date . " to " . $end_date . ")."
            ], 422);
        }

        //  CHECK IF APPLICANT ALREADY APPLIED TO THIS JOB
        $existing = DB::table('submission')
            ->join('xPersonal', 'xPersonal.ControlNo', '=', 'submission.ControlNo')
            ->where('submission.job_batches_rsp_id', $jobId)
            ->where('xPersonal.Firstname', $firstname)
            ->where('xPersonal.Surname', $lastname)
            ->where('xPersonal.BirthDate', $birthdate)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "$firstname $lastname, you have already applied for this job.",
                'submission_id' => $existing->id ?? null
            ], 409);
        }

        //  CREATE SUBMISSION (NO DUPLICATE FOUND)
        $submit = Submission::create([
            'ControlNo' => $controlNo,
            'status' => 'pending',
            'job_batches_rsp_id' => $jobId,
            'nPersonalInfo_id' => null,
        ]);

        //  SEND EMAIL USING YOUR PRIVATE FUNCTION
        if (!empty($applicant->EmailAdd)) {
            // Format data for private function
            $emailApplicant = (object) [
                'email_address' => $applicant->EmailAdd,
                'firstname'     => $firstname,
                'lastname'      => $lastname
            ];

            $this->sendApplicantEmail($emailApplicant, $jobId, false);
        }

        return response()->json([
            'success' => true,
            'message' => 'Submission created successfully and email sent.',
            'data' => $submit
        ], 201);
    }


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

        if ($deleteRoot) {
            return rmdir($dir);
        }

        return true;
    }



    public function applicantStore(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,xlsm',
            'zip_file' => 'required|file|mimes:zip',
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
            'email' => 'required|email:rfc,dns'
        ]);

        try {

            // Step 1: Read and parse Excel WITHOUT saving to database
            $excelFile = $request->file('excel_file');
            $excelData = $this->parseExcelData($excelFile);

            // ➤ ADD THE MATCHING CHECK HERE
            $excelEmail = strtolower(trim($excelData['personal_info']['email_address']));
            $userEmail  = strtolower(trim($validated['email']));

            if ($excelEmail !== $userEmail) {
                return response()->json([
                    'success' => false,
                    'message' => "Please check your email, it doesn’t match with the email inside the PDS (Excel file).",
                    'email' => "  Your email use on verification:$userEmail"
                ], 422);
            }

            // Step 2: Check for duplicate applicant based on NAME and BIRTHDATE ONLY
            // This allows same email to apply multiple times
            $existingSubmission = Submission::whereHas('nPersonalInfo', function ($query) use ($excelData) {
                $query->where('firstname', $excelData['personal_info']['firstname'])
                    ->where('lastname', $excelData['personal_info']['lastname'])
                    ->whereDate('date_of_birth', $excelData['personal_info']['date_of_birth']);
            })
                ->where('job_batches_rsp_id', $validated['job_batches_rsp_id'])
                ->first();


            // 1. Get current job post
            $currentJob = JobBatchesRsp::findOrFail($validated['job_batches_rsp_id']);

            // 2. Get all job posts with the SAME start & end date
            $jobGroupIds = JobBatchesRsp::where('post_date', $currentJob->post_date)
                ->where('end_date', $currentJob->end_date)
                ->pluck('id');

            // 3. Count how many applications the applicant submitted in this job group
            $applicationCount = Submission::whereIn('job_batches_rsp_id', $jobGroupIds)
                ->whereHas('nPersonalInfo', function ($q) use ($excelData) {
                    $q->where('firstname', $excelData['personal_info']['firstname'])
                        ->where('lastname',  $excelData['personal_info']['lastname'])
                        ->whereDate('date_of_birth', $excelData['personal_info']['date_of_birth']);
                })
                ->count();

            $post_date = Carbon::parse($currentJob->post_date)->format('F d, Y');
            $end_date   = Carbon::parse($currentJob->end_date)->format('F d, Y');

            // 4. Block if already applied 3 times
            if ($applicationCount >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => "You have already applied for 3 job posts with the same application period (" .
                        $post_date . " to " .     $end_date  . ").",
                ], 422);
            }

            if ($existingSubmission) {
                // Store files temporarily
                $zipFile = $request->file('zip_file');
                $tempZipFileName = 'temp_' . $this->generateFileName($zipFile);
                $tempZipPath = $zipFile->storeAs('temp_zips', $tempZipFileName);

                $excelFileName = $this->generateFileName($excelFile);
                $tempExcelPath = $excelFile->storeAs('temp_excels', 'temp_' . $excelFileName);

                // Generate unique token for this confirmation
                $confirmationToken = Str::random(32);

                // Store parsed data in cache with 10 minute expiration
                Cache::put("applicant_confirmation_{$confirmationToken}", [
                    'excel_data' => $excelData,
                    'temp_zip_path' => $tempZipPath,
                    'temp_excel_path' => $tempExcelPath,
                    'job_batches_rsp_id' => $validated['job_batches_rsp_id'],
                    'existing_submission_id' => $existingSubmission->id,
                ], now()->addMinutes(10));

                return response()->json([
                    'success' => false,
                    'message' => "You've already applied for this job. Do you want to update your previous application?",
                    'confirmation_token' => $confirmationToken,
                    'expires_in_minutes' => 10,
                ]);
            }

            // Step 3: Check if email already exists for ANY job (optional warning, not blocking)
            $emailExists = nPersonal_info::where('email_address', $excelData['personal_info']['email_address'])->exists();

            if ($emailExists) {
                Log::info('Email already exists but allowing submission', [
                    'email' => $excelData['personal_info']['email_address'],
                    'job_id' => $validated['job_batches_rsp_id']
                ]);
            }

            // No duplicate based on name+birthdate - proceed with normal save
            DB::beginTransaction();
            try {
                $applicant = $this->saveApplicantData($excelData, $validated['job_batches_rsp_id']);

                // Process ZIP File
                $zipFile = $request->file('zip_file');
                $zipFileName = $this->generateFileName($zipFile);
                $zipPath = $zipFile->storeAs('zips', $zipFileName);

                $this->extractApplicantZip($zipPath, $applicant->id);

                ApplicantZip::updateOrCreate(
                    ['nPersonalInfo_id' => $applicant->id],
                    ['zip_path' => $zipPath]
                );

                // Store Excel file
                $excelFileName = $this->generateFileName($excelFile);
                $excelFile->storeAs('excels', $excelFileName);

                // Send Email
                $this->sendApplicantEmail($applicant, $validated['job_batches_rsp_id'], false);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Applicant imported successfully and confirmation email sent.',
                    'is_update' => false,
                    'excel_file_name' => $excelFileName,
                    'nPersonalInfo_id' => $applicant->id,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed during Excel import.',
                'errors' => $e->failures(),
            ], 422);
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('Failed to import Excel file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to import Excel file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Handle user confirmation (YES or NO)
     */
    public function confirmDuplicateApplicant(Request $request)
    {
        $validated = $request->validate([
            'confirmation_token' => 'required|string',
            'confirm_update' => 'required|boolean',
        ]);

        // Retrieve cached data
        $cachedData = Cache::get("applicant_confirmation_{$validated['confirmation_token']}");

        if (!$cachedData) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmation expired or invalid. Please re-upload your application.',
            ], 410);
        }

        // Add validation to ensure cached data is properly structured
        if (!isset($cachedData['excel_data']) || !is_array($cachedData['excel_data'])) {
            // Log::error('Invalid cached data structure', [
            //     'cached_data' => $cachedData
            // ]);
            Cache::forget("applicant_confirmation_{$validated['confirmation_token']}");
            return response()->json([
                'success' => false,
                'message' => 'Invalid application data. Please re-upload your application.',
            ], 400);
        }

        // User said NO - Delete temporary files
        if (!$validated['confirm_update']) {
            $this->deleteTemporaryFiles($cachedData['temp_zip_path'], $cachedData['temp_excel_path']);
            Cache::forget("applicant_confirmation_{$validated['confirmation_token']}");

            return response()->json([
                'success' => true,
                'message' => 'Application update cancelled. Temporary data has been removed.',
            ]);
        }


        // User said YES - Proceed with update
        DB::beginTransaction();
        try {
            $existingSubmission = Submission::findOrFail($cachedData['existing_submission_id']);
            $oldApplicant = $existingSubmission->nPersonalInfo;

            // Update applicant with new data
            $this->updateApplicantData($oldApplicant, $cachedData['excel_data']);

            // Process ZIP file
            $tempZipFullPath = storage_path('app/public/' . $cachedData['temp_zip_path']);

            if (!file_exists($tempZipFullPath)) {
                throw new \Exception('Temporary ZIP file not found.');
            }

            // Move ZIP from temp to permanent storage
            $newZipFileName = $this->generateFileName(new \SplFileInfo($tempZipFullPath));
            $newZipPath = 'zips/' . $newZipFileName;
            Storage::move($cachedData['temp_zip_path'], $newZipPath);

            // Extract ZIP to old applicant's folder (overwriting old files)
            $this->extractApplicantZip($newZipPath, $oldApplicant->id);

            ApplicantZip::updateOrCreate(
                ['nPersonalInfo_id' => $oldApplicant->id],
                ['zip_path' => $newZipPath]
            );

            // Move Excel from temp to permanent storage
            $tempExcelFullPath = storage_path('app/public/' . $cachedData['temp_excel_path']);
            $newExcelFileName = $this->generateFileName(new \SplFileInfo($tempExcelFullPath));
            Storage::move($cachedData['temp_excel_path'], 'excels/' . $newExcelFileName);

            // Clear cache
            Cache::forget("applicant_confirmation_{$validated['confirmation_token']}");

            // Reload applicant with updated data
            $updatedApplicant = $oldApplicant->fresh()->load([
                'family',
                'children',
                'education',
                'eligibity',
                'work_experience',
                'voluntary_work',
                'training',
                'personal_declarations',
                'skills',
                'references'
            ]);

            DB::commit();

            // Send update confirmation email
            $this->sendApplicantEmail($updatedApplicant, $cachedData['job_batches_rsp_id'], true);

            return response()->json([
                'success' => true,
                'message' => 'Your application has been successfully updated.',
                'is_update' => true,
                'nPersonalInfo_id' => $oldApplicant->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup temp files on error
            if (isset($cachedData['temp_zip_path']) && isset($cachedData['temp_excel_path'])) {
                $this->deleteTemporaryFiles($cachedData['temp_zip_path'], $cachedData['temp_excel_path']);
            }

            Cache::forget("applicant_confirmation_{$validated['confirmation_token']}");

            return response()->json([
                'message' => 'Failed to process confirmation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse Excel data WITHOUT saving to database
     */
    private function parseExcelData($excelFile)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile->getRealPath());

        // Parse Personal Information Sheet
        $personalInfoSheet = $spreadsheet->getSheetByName('Personal Information');
        $personalInfo = $this->parsePersonalInformation($personalInfoSheet);

        // Parse Family Background Sheet
        $familySheet = $spreadsheet->getSheetByName('Family Background');
        $family = $this->parseFamilyBackground($familySheet);

        // Parse Children Sheet
        $childrenSheet = $spreadsheet->getSheetByName('Children');
        $children = $this->parseChildren($childrenSheet);

        // Parse Educational Background Sheet
        $educationSheet = $spreadsheet->getSheetByName('Educational Background');
        $education = $this->parseEducation($educationSheet);

        // Parse Civil Service Eligibility Sheet
        $eligibilitySheet = $spreadsheet->getSheetByName('Civil Service Eligibity');
        $eligibility = $this->parseEligibility($eligibilitySheet);

        // Parse Work Experience Sheet
        $workExpSheet = $spreadsheet->getSheetByName('Work Experience');
        $workExperience = $this->parseWorkExperience($workExpSheet);

        // Parse Voluntary Work Sheet
        $voluntarySheet = $spreadsheet->getSheetByName('Voluntary Work');
        $voluntaryWork = $this->parseVoluntaryWork($voluntarySheet);

        // Parse Learning and Development Sheet
        $trainingSheet = $spreadsheet->getSheetByName('Learning and Development');
        $training = $this->parseTraining($trainingSheet);

        // Parse Skills Sheet
        $skillsSheet = $spreadsheet->getSheetByName('Skill and Non academic');
        $skills = $this->parseSkills($skillsSheet);

        // Parse Personal Declarations Sheet
        $declarationsSheet = $spreadsheet->getSheetByName('Personal Declarations');
        $declarations = $this->parsePersonalDeclarations($declarationsSheet);

        // ADD THIS: Parse References from Personal Declarations sheet
        $references = $this->parseReferences($declarationsSheet);




        return [
            'personal_info' => $personalInfo,
            'family' => $family,
            'children' => $children,
            'education' => $education,
            'eligibity' => $eligibility,
            'work_experience' => $workExperience,
            'voluntary_work' => $voluntaryWork,
            'training' => $training,
            'personal_declarations' => $declarations,
            'skills' => $skills,
            'references' => $references // Add if you have references sheet
        ];
    }

    /**
     * Parse Personal Information sheet
     */
    private function parsePersonalInformation($sheet)
    {
        $date_of_birth = $this->parseExcelDate($sheet->getCell('D13')->getValue());

        $isMale = $sheet->getCell('D16')->getValue();
        $isFemale = $sheet->getCell('E16')->getValue();

        $filipino = $sheet->getCell('J13')->getValue();
        $by_birth = $sheet->getCell('J14')->getValue();
        $dual_citizenship = $sheet->getCell('L13')->getValue();
        $by_naturalization = $sheet->getCell('L14')->getValue();

        $single = $sheet->getCell('D17')->getValue();
        $married = $sheet->getCell('E17')->getValue();
        $separated = $sheet->getCell('E18')->getValue();
        $widowed = $sheet->getCell('D18')->getValue();
        $others = $sheet->getCell('D19')->getValue();

        // Determine sex
        $sex = null;
        if ($isMale === true || $isMale === 'TRUE') {
            $sex = 'Male';
        } elseif ($isFemale === true || $isFemale === 'TRUE') {
            $sex = 'Female';
        } else {
            $sex = 'prefer not to say';
        }

        // Determine citizenship
        $citizenship_status = $this->determineCitizenshipStatus($filipino, $by_birth, $dual_citizenship, $by_naturalization);

        // Determine civil status
        $civil_status = $this->determineCivilStatus($single, $married, $separated, $widowed, $others);

        // Handle image from cell R3
        $imagePath = null;
        $drawings = $sheet->getDrawingCollection();
        foreach ($drawings as $drawing) {
            $coord = strtoupper((string)$drawing->getCoordinates());
            if ($coord === 'R3') {
                $imagePath = $this->extractAndSaveImageTemp($drawing);
                break;
            }
        }

        return [
            'lastname' => $sheet->getCell('D10')->getValue(),
            'firstname' => $sheet->getCell('D11')->getValue(),
            'middlename' => $sheet->getCell('D12')->getValue(),
            'name_extension' => $sheet->getCell('L11')->getValue(),
            'sex' => $sex,
            'civil_status' => $civil_status,
            'citizenship' => $citizenship_status,
            'date_of_birth' => $date_of_birth,
            'place_of_birth' => $sheet->getCell('D15')->getValue(),
            'height' => $sheet->getCell('D21')->getValue(),
            'weight' => $sheet->getCell('D23')->getValue(),
            'blood_type' => $sheet->getCell('D24')->getValue(),
            'gsis_no' => $sheet->getCell('D26')->getValue(),
            'pagibig_no' => $sheet->getCell('D28')->getValue(),
            'philhealth_no' => $sheet->getCell('D30')->getValue(),
            'sss_no' => $sheet->getCell('D31')->getValue(),
            'tin_no' => $sheet->getCell('D32')->getValue(),
            'image_path' => $imagePath,
            'residential_house' => $sheet->getCell('I17')->getValue(),
            'residential_street' => $sheet->getCell('L17')->getValue(),
            'residential_subdivision' => $sheet->getCell('I19')->getValue(),
            'residential_barangay' => $sheet->getCell('L19')->getValue(),
            'residential_city' => $sheet->getCell('I21')->getValue(),
            'residential_province' => $sheet->getCell('L21')->getValue(),
            'residential_zip' => $sheet->getCell('I23')->getValue(),
            'permanent_house' => $sheet->getCell('I24')->getValue(),
            'permanent_street' => $sheet->getCell('L24')->getValue(),
            'permanent_subdivision' => $sheet->getCell('I26')->getValue(),
            'permanent_barangay' => $sheet->getCell('L26')->getValue(),
            'permanent_city' => $sheet->getCell('I28')->getValue(),
            'permanent_province' => $sheet->getCell('L28')->getValue(),
            'permanent_zip' => $sheet->getCell('I30')->getValue(),
            'telephone_number' => $sheet->getCell('I31')->getValue(),
            'cellphone_number' => $sheet->getCell('I32')->getValue(),
            'email_address' => $sheet->getCell('I33')->getValue(),
        ];
    }

    /**
     * Parse Family Background sheet
     */
    private function parseFamilyBackground($sheet)
    {
        // Adjust based on your family sheet structure
        return [
            'spouse_name' => $sheet->getCell('D2')->getValue(),
            'spouse_firstname' => $sheet->getCell('D3')->getValue(),
            'spouse_occupation' => $sheet->getCell('D5')->getValue(),
            'spouse_employer' => $sheet->getCell('D6')->getValue(),
            'spouse_extension' => $sheet->getCell('I3')->getValue(),
            'spouse_middlename' => $sheet->getCell('D4')->getValue(),
            'spouse_employer_address' => $sheet->getCell('D7')->getValue(),
            'spouse_employer_telephone' => $sheet->getCell('D8')->getValue(),

            'father_lastname' => $sheet->getCell('D9')->getValue(),
            'father_firstname' => $sheet->getCell('D10')->getValue(),
            'father_middlename' => $sheet->getCell('D11')->getValue(),
            'father_extension' => $sheet->getCell('I10')->getValue(),


            'mother_lastname' => $sheet->getCell('D13')->getValue(),
            'mother_firstname' => $sheet->getCell('D14')->getValue(),
            'mother_middlename' => $sheet->getCell('D15')->getValue(),
            'mother_maidenname' => $sheet->getCell('D12')->getValue(),
            // Add other family fields based on your sheet
        ];
    }

    /**
     * Parse Children sheet
     */
    private function parseChildren($sheet)
    {
        $children = [];
        $startRow = 3; // Changed from 0 to 3 to match your Children_sheet class

        $highestRow = $sheet->getHighestRow();



        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $childName = $sheet->getCell("A{$rowIndex}")->getValue();
            $birthDate = $sheet->getCell("B{$rowIndex}")->getValue();


            // Skip empty rows
            if (empty($childName) && empty($birthDate)) {

                continue;
            }

            // Skip header rows
            $rowValues = [$childName, $birthDate];
            if ($this->isHeaderRow($rowValues)) {
                continue;
            }

            // Skip if name is empty
            if (empty($childName)) {
                continue;
            }

            $formattedDate = null;
            if ($birthDate) {
                $formattedDate = $this->parseExcelDate($birthDate);
            }

            $childRecord = [
                'child_name' => $childName,
                'birth_date' => $formattedDate,
            ];

            $children[] = $childRecord;
        }



        return $children;
    }
    /**
     * Parse Education sheet
     */
    private function parseEducation($sheet)
    {
        $education = [];
        $startRow = 3;
        $highestRow = $sheet->getHighestRow();

        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $level = $sheet->getCell("A{$rowIndex}")->getValue();

            // Skip empty rows
            if (empty($level)) {
                continue;
            }

            // Skip header rows
            $schoolName = $sheet->getCell("B{$rowIndex}")->getValue();
            if ($this->isHeaderRow([$level, $schoolName])) {
                continue;
            }

            $education[] = [
                'level' => $level,
                'school_name' => $schoolName,
                'degree' => $sheet->getCell("C{$rowIndex}")->getValue(),
                'attendance_from' => $this->parseExcelDate($sheet->getCell("D{$rowIndex}")->getValue()),
                'attendance_to' => $this->parseExcelDate($sheet->getCell("E{$rowIndex}")->getValue()),
                'highest_units' => $this->sanitizeNumericValue($sheet->getCell("F{$rowIndex}")->getValue()),
                'year_graduated' => $this->sanitizeNumericValue($sheet->getCell("G{$rowIndex}")->getValue()),
                'scholarship' => $sheet->getCell("H{$rowIndex}")->getValue(),
            ];
        }

        return $education;
    }

    /**
     * Parse Eligibility sheet
     */
    private function parseEligibility($sheet)
    {
        $eligibilityRecords = []; // FIXED: Changed variable name from $eligibility to $eligibilityRecords
        $startRow = 3; // Changed to match your Civil_service_eligibity_sheet class (row 5 - 2 for headers)

        $highestRow = $sheet->getHighestRow();



        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $eligibilityName = $sheet->getCell("A{$rowIndex}")->getValue();
            $rating = $sheet->getCell("B{$rowIndex}")->getValue();
            $dateExam = $sheet->getCell("C{$rowIndex}")->getValue();
            $placeExam = $sheet->getCell("D{$rowIndex}")->getValue();
            $licenseNumber = $sheet->getCell("E{$rowIndex}")->getValue();
            $licenseValidity = $sheet->getCell("F{$rowIndex}")->getValue();



            // Skip empty rows
            if (empty($eligibilityName) && empty($rating)) {
                continue;
            }

            // Skip header rows
            if ($this->isHeaderRow([$eligibilityName, $rating, $licenseNumber])) {
                continue;
            }

            // Skip if eligibility is empty (required field)
            if (empty($eligibilityName)) {
                continue;
            }

            $eligibilityRecord = [
                'eligibility' => $eligibilityName,
                'rating' => $this->sanitizeNumericValue($rating),
                'date_of_examination' => $this->parseExcelDate($dateExam),
                'place_of_examination' => $placeExam,
                'license_number' => $this->sanitizeLicenseNumber($licenseNumber),
                'date_of_validity' => $this->parseExcelDate($licenseValidity),
            ];

            $eligibilityRecords[] = $eligibilityRecord; // FIXED: Append to array instead of overwriting

        }



        return $eligibilityRecords; // FIXED: Return the array
    }


    /**
     * Parse Work Experience sheet
     */
    private function parseWorkExperience($sheet)
    {
        $workExperience = [];
        $startRow = 3;
        $highestRow = $sheet->getHighestRow();

        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $work_date_from = $this->parseExcelDate($sheet->getCell("A{$rowIndex}")->getValue());

            // Skip empty rows
            if (empty($work_date_from)) {
                continue;
            }

            // Skip header rows
            $work_date_to = $this->parseExcelDate($sheet->getCell("B{$rowIndex}")->getValue());
            if ($this->isHeaderRow([$work_date_from, $work_date_to])) {
                continue;
            }

            $workExperience[] = [
                'work_date_from' => $work_date_from,
                'work_date_to' => $work_date_to,
                'position_title' => $sheet->getCell("C{$rowIndex}")->getValue(),
                'department' => $sheet->getCell("D{$rowIndex}")->getValue(),
                'monthly_salary' => $this->sanitizeNumericValue($sheet->getCell("E{$rowIndex}")->getValue()),
                'salary_grade' => $sheet->getCell("F{$rowIndex}")->getValue(),
                'status_of_appointment' => $sheet->getCell("G{$rowIndex}")->getValue(),
                'government_service' => $sheet->getCell("H{$rowIndex}")->getValue(),

            ];
        }

        return $workExperience;
    }

    /**
     * Parse Voluntary Work sheet
     */
    private function parseVoluntaryWork($sheet)
    {
        $voluntaryWork = [];
        $startRow = 3;
        $highestRow = $sheet->getHighestRow();

        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $orgName = $sheet->getCell("A{$rowIndex}")->getValue();

            // Skip empty rows
            if (empty($orgName)) {
                continue;
            }

            // Skip header rows
            if ($this->isHeaderRow([$orgName])) {
                continue;
            }

            $voluntaryWork[] = [
                'organization_name' => $orgName,
                // 'organization_address' => $sheet->getCell("B{$rowIndex}")->getValue(),
                'inclusive_date_from' => $this->parseExcelDate($sheet->getCell("B{$rowIndex}")->getValue()),
                'inclusive_date_to' => $this->parseExcelDate($sheet->getCell("C{$rowIndex}")->getValue()),
                'number_of_hours' => $this->sanitizeNumericValue($sheet->getCell("D{$rowIndex}")->getValue()),
                'position' => $sheet->getCell("E{$rowIndex}")->getValue(),
            ];
        }

        return $voluntaryWork;
    }

    /**
     * Parse Personal Declarations sheet (including references)
     */


    /**
     * Parse Training sheet
     */
    private function parseTraining($sheet)
    {
        $training = [];
        $startRow = 3;
        $highestRow = $sheet->getHighestRow();

        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $trainingTitle = $sheet->getCell("A{$rowIndex}")->getValue();

            // Skip empty rows
            if (empty($trainingTitle)) {
                continue;
            }

            // Skip header rows
            if ($this->isHeaderRow([$trainingTitle])) {
                continue;
            }

            $training[] = [
                'training_title' => $trainingTitle,
                'inclusive_date_from' => $this->parseExcelDate($sheet->getCell("B{$rowIndex}")->getValue()),
                'inclusive_date_to' => $this->parseExcelDate($sheet->getCell("C{$rowIndex}")->getValue()),
                'number_of_hours' => $this->sanitizeNumericValue($sheet->getCell("D{$rowIndex}")->getValue()),
                'type' => $sheet->getCell("E{$rowIndex}")->getValue(),
                'conducted_by' => $sheet->getCell("F{$rowIndex}")->getValue(),
            ];
        }

        return $training;
    }

    /**
     * Parse Skills sheet
     */
    private function parseSkills($sheet)
    {
        $skills = [];
        $startRow = 3;
        $highestRow = $sheet->getHighestRow();

        for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
            $skill = $sheet->getCell("A{$rowIndex}")->getValue();
            $non_academic = $sheet->getCell("B{$rowIndex}")->getValue();
            $organization = $sheet->getCell("C{$rowIndex}")->getValue();
            // Skip empty rows
            if (empty($skill)) {
                continue;
            }

            // Skip header rows
            if ($this->isHeaderRow([$non_academic])) {
                continue;
            }

            $skills[] = [
                'skill' => $skill,
                'non_academic' => $non_academic,
                'organization' => $organization,
            ];
        }

        return $skills;
    }

    /**
     * Parse Personal Declarations sheet
     */
    /**
     * Parse Personal Declarations sheet (including references)
     */

    /**
     * Parse References from Personal Declarations sheet
     */
    private function parseReferences($sheet)
    {
        $references = [];
        $startRow = 43; // References start at row 43 based on your Personal_declarations_sheet
        $endRow = 50;   // References end at row 50



        for ($row = $startRow; $row <= $endRow; $row++) {
            $full_name = $sheet->getCell('A' . $row)->getValue();
            $address = $sheet->getCell('F' . $row)->getValue();
            $contact_number = $sheet->getCell('I' . $row)->getValue();


            // Skip empty rows
            if (empty($full_name) && empty($address) && empty($contact_number)) {
                continue;
            }

            // Skip if name is empty (required field)
            if (empty($full_name)) {
                continue;
            }

            $referenceRecord = [
                'full_name' => $full_name,
                'address' => $address,
                'contact_number' => $contact_number,
            ];

            $references[] = $referenceRecord;
        }



        return $references;
    }
    private function parsePersonalDeclarations($sheet)
    {
        Log::info('=== PARSING PERSONAL DECLARATIONS SHEET ===');

        // Helper function for checkbox values
        $isChecked = function ($yes, $no) {
            $result = null;
            if ($yes === true || strtoupper((string)$yes) === 'TRUE') {
                $result = 'YES';
            } elseif ($no === true || strtoupper((string)$no) === 'TRUE') {
                $result = 'NO';
            }

            Log::debug('isChecked function', [
                'yes' => $yes,
                'no' => $no,
                'result' => $result
            ]);

            return $result;
        };

        // Helper function to sanitize response values
        $sanitizeResponse = function ($value) {
            if ($value === null || $value === '' || $value === false) {
                return null;
            }
            // Convert boolean true to a string if needed
            if ($value === true) {
                return 'N/A';
            }
            return $value;
        };

        // Parse all questions with logging
        $declarations = [
            // Q34
            'question_34a' => $isChecked(
                $sheet->getCell('G5')->getValue(),
                $sheet->getCell('I5')->getValue()
            ),
            'question_34b' => $isChecked(
                $sheet->getCell('G6')->getValue(),
                $sheet->getCell('I6')->getValue()
            ),
            'response_34' => $sanitizeResponse($sheet->getCell('I7')->getValue()),

            // Q35
            'question_35a' => $isChecked(
                $sheet->getCell('G9')->getValue(),
                $sheet->getCell('I9')->getValue()
            ),
            'response_35a' => $sanitizeResponse($sheet->getCell('I10')->getValue()),
            'question_35b' => $isChecked(
                $sheet->getCell('G12')->getValue(),
                $sheet->getCell('I12')->getValue()
            ),
            'response_35b_date' => $this->parseExcelDate($sheet->getCell('I15')->getValue()),
            'response_35b_status' => $sanitizeResponse($sheet->getCell('I16')->getValue()),

            // Q36
            'question_36' => $isChecked(
                $sheet->getCell('G18')->getValue(),
                $sheet->getCell('I18')->getValue()
            ),
            'response_36' => $sanitizeResponse($sheet->getCell('I19')->getValue()),

            // Q37
            'question_37' => $isChecked(
                $sheet->getCell('G20')->getValue(),
                $sheet->getCell('I20')->getValue()
            ),
            'response_37' => $sanitizeResponse($sheet->getCell('I21')->getValue()),

            // Q38
            'question_38a' => $isChecked(
                $sheet->getCell('G23')->getValue(),
                $sheet->getCell('I23')->getValue()
            ),
            'response_38a' => $sanitizeResponse($sheet->getCell('I25')->getValue()),
            'question_38b' => $isChecked(
                $sheet->getCell('G26')->getValue(),
                $sheet->getCell('I26')->getValue()
            ),
            'response_38b' => $sanitizeResponse($sheet->getCell('I28')->getValue()),

            // Q39
            'question_39' => $isChecked(
                $sheet->getCell('G30')->getValue(),
                $sheet->getCell('I30')->getValue()
            ),
            'response_39' => $sanitizeResponse($sheet->getCell('I31')->getValue()),

            // Q40
            'question_40a' => $isChecked(
                $sheet->getCell('G34')->getValue(),
                $sheet->getCell('I34')->getValue()
            ),
            'response_40a' => $sanitizeResponse($sheet->getCell('I35')->getValue()),
            'question_40b' => $isChecked(
                $sheet->getCell('G36')->getValue(),
                $sheet->getCell('I36')->getValue()
            ),
            'response_40b' => $sanitizeResponse($sheet->getCell('I37')->getValue()),
            'question_40c' => $isChecked(
                $sheet->getCell('G38')->getValue(),
                $sheet->getCell('I38')->getValue()
            ),
            'response_40c' => $sanitizeResponse($sheet->getCell('I39')->getValue()),
        ];

        // Log each declaration with null check
        Log::info('=== PERSONAL DECLARATIONS PARSED DATA ===');

        foreach ($declarations as $key => $value) {
            $isNull = $value === null;
            $isEmpty = empty($value);

            Log::info("Declaration: {$key}", [
                'value' => $value,
                'type' => gettype($value),
                'is_null' => $isNull,
                'is_empty' => $isEmpty,
                'length' => is_string($value) ? strlen($value) : 'N/A'
            ]);
        }

        // Count null and empty values
        $nullCount = 0;
        $emptyCount = 0;
        foreach ($declarations as $key => $value) {
            if ($value === null) $nullCount++;
            if (empty($value)) $emptyCount++;
        }

        Log::info('=== PERSONAL DECLARATIONS SUMMARY ===', [
            'total_fields' => count($declarations),
            'null_count' => $nullCount,
            'empty_count' => $emptyCount,
            'filled_count' => count($declarations) - $nullCount
        ]);

        // Log specific problematic fields (questions with null responses)
        $nullFields = [];
        foreach ($declarations as $key => $value) {
            if ($value === null) {
                $nullFields[] = $key;
            }
        }

        if (!empty($nullFields)) {
            Log::warning('Fields with NULL values', [
                'null_fields' => $nullFields
            ]);
        }

        Log::info('=== PERSONAL DECLARATIONS PARSING COMPLETE ===');

        return $declarations;
    }
    /**
     * Helper: Parse Excel date
     */
    private function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper: Determine citizenship status
     */
    private function determineCitizenshipStatus($filipino, $by_birth, $dual_citizenship, $by_naturalization)
    {
        if ($filipino && $by_birth) {
            return 'Filipino by Birth';
        } elseif ($filipino && $by_naturalization) {
            return 'Filipino by Naturalization';
        } elseif ($dual_citizenship && $by_birth) {
            return 'Dual Citizenship by Birth';
        } elseif ($dual_citizenship && $by_naturalization) {
            return 'Dual Citizenship by Naturalization';
        }
        return null;
    }

    /**
     * Helper: Determine civil status
     */
    private function determineCivilStatus($single, $married, $separated, $widowed, $others)
    {
        if ($single) return 'Single';
        if ($married) return 'Married';
        if ($separated) return 'Separated';
        if ($widowed) return 'Widowed';
        if ($others) return 'Others';
        return null;
    }

    /**
     * Helper: Sanitize numeric value
     */
    private function sanitizeNumericValue($value)
    {
        if (empty($value)) {
            return null;
        }

        // Check if it's a header text
        if (
            is_string($value) &&
            (stripos($value, 'RATING') !== false ||
                stripos($value, 'If Applicable') !== false ||
                stripos($value, 'applicable') !== false)
        ) {
            return null;
        }

        // Remove any non-numeric characters except decimal point
        $cleaned = preg_replace('/[^0-9.]/', '', (string)$value);

        return !empty($cleaned) ? $cleaned : null;
    }

    /**
     * Helper: Sanitize license number
     */
    private function sanitizeLicenseNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        // Check if it's a header text
        if (
            is_string($value) &&
            (stripos($value, 'LICENSE') !== false ||
                stripos($value, 'If Applicable') !== false ||
                stripos($value, 'applicable') !== false)
        ) {
            return null;
        }

        return trim($value);
    }

    /**
     * Helper: Check if row is a header
     */
    private function isHeaderRow($values)
    {
        $headerKeywords = [
            'RATING',
            'LICENSE',
            'If Applicable',
            'applicable',
            'CAREER SERVICE',
            'ELIGIBILITY',
            'NAME',
            'DATE',
            'POSITION',
            'COMPANY',
            'SCHOOL',
            'LEVEL',
            'ORGANIZATION',
            'TRAINING',
            'SKILL',
            'HOBBY'
        ];

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $upperValue = strtoupper($value);
            foreach ($headerKeywords as $keyword) {
                if (stripos($upperValue, strtoupper($keyword)) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper: Extract and save image temporarily
     */
    private function extractAndSaveImageTemp($drawing)
    {
        try {
            $tempPath = 'temp_images/' . Str::random(16) . '.png';

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                ob_start();
                call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                $imageContents = ob_get_contents();
                ob_end_clean();
                Storage::put($tempPath, $imageContents);
            } elseif ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                $imageContents = file_get_contents($drawing->getPath());
                Storage::put($tempPath, $imageContents);
            }

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Failed to extract image: ' . $e->getMessage());
            return null;
        }
    }

    private function saveApplicantData($excelData, $jobBatchesRspId)
    {
        Log::info('=== STARTING NEW APPLICANT SAVE ===', [
            'job_batches_rsp_id' => $jobBatchesRspId
        ]);

        // Create personal info
        $applicant = nPersonal_info::create($excelData['personal_info']);
        Log::info('Personal info created', ['applicant_id' => $applicant->id]);

        // Create family record (SINGLE RECORD)
        if (!empty($excelData['family'])) {
            $excelData['family']['nPersonalInfo_id'] = $applicant->id;
            $applicant->family()->create($excelData['family']);
            Log::info('Family record created');
        }

        // Create related records with MULTIPLE entries
        $multipleRelations = [
            'children',
            'education',
            'eligibity',
            'work_experience',
            'voluntary_work',
            'training',
            'skills',
            'references'
        ];

        foreach ($multipleRelations as $relation) {
            Log::info("Processing relation: {$relation}", [
                'has_data' => !empty($excelData[$relation]),
                'data_count' => is_array($excelData[$relation]) ? count($excelData[$relation]) : 0,
                'data' => $excelData[$relation] ?? null
            ]);

            if (!empty($excelData[$relation])) {
                $createdCount = 0;
                foreach ($excelData[$relation] as $index => $record) {
                    $record['nPersonalInfo_id'] = $applicant->id;

                    Log::info("Creating {$relation} record #{$index}", [
                        'data' => $record
                    ]);

                    try {
                        $created = $applicant->$relation()->create($record);
                        $createdCount++;
                        Log::info("{$relation} record created successfully", [
                            'id' => $created->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create {$relation} record", [
                            'index' => $index,
                            'data' => $record,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }
                Log::info("Created {$createdCount} {$relation} records");
            } else {
                Log::info("No {$relation} records to create");
            }
        }

        // Handle personal_declarations separately (SINGLE RECORD)
        if (!empty($excelData['personal_declarations']) && is_array($excelData['personal_declarations'])) {
            $declarationData = $excelData['personal_declarations'];
            $declarationData['nPersonalInfo_id'] = $applicant->id;

            Log::info("Creating personal_declarations record", [
                'data' => $declarationData
            ]);

            try {
                $created = $applicant->personal_declarations()->create($declarationData);
                Log::info("personal_declarations record created successfully", [
                    'id' => $created->id
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to create personal_declarations record", [
                    'data' => $declarationData,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        // Create submission
        Submission::create([
            'nPersonalInfo_id' => $applicant->id,
            'job_batches_rsp_id' => $jobBatchesRspId,
        ]);
        Log::info('Submission record created');

        Log::info('=== NEW APPLICANT SAVE COMPLETE ===', ['applicant_id' => $applicant->id]);

        return $applicant->fresh()->load([
            'family',
            'children',
            'education',
            'eligibity',
            'work_experience',
            'voluntary_work',
            'training',
            'personal_declarations',
            'skills',
            'references'
        ]);
    }



    private function updateApplicantData($oldApplicant, $excelData)
    {
        Log::info('=== STARTING APPLICANT UPDATE ===', [
            'applicant_id' => $oldApplicant->id,
            'excel_data_type' => gettype($excelData),
            'excel_data_keys' => is_array($excelData) ? array_keys($excelData) : 'not an array'
        ]);

        // Ensure $excelData is an array
        if (!is_array($excelData)) {
            Log::error('Excel data is not an array', [
                'type' => gettype($excelData),
                'value' => $excelData
            ]);
            throw new \Exception('Invalid excel data format');
        }

        // Ensure personal_info exists and is an array
        if (!isset($excelData['personal_info']) || !is_array($excelData['personal_info'])) {
            Log::error('Personal info missing or invalid', [
                'has_personal_info' => isset($excelData['personal_info']),
                'personal_info_type' => isset($excelData['personal_info']) ? gettype($excelData['personal_info']) : 'not set'
            ]);
            throw new \Exception('Personal info data is missing or invalid');
        }

        // Update personal info
        $oldApplicant->update($excelData['personal_info']);
        Log::info('Personal info updated');

        // Update family (SINGLE RECORD)
        $oldApplicant->family()->delete();
        if (!empty($excelData['family']) && is_array($excelData['family'])) {
            $excelData['family']['nPersonalInfo_id'] = $oldApplicant->id;
            $oldApplicant->family()->create($excelData['family']);
            Log::info('Family record updated');
        }

        // Update related records that have MULTIPLE entries
        $multipleRelations = [
            'children',
            'education',
            'eligibity',
            'work_experience',
            'voluntary_work',
            'training',
            'skills',
            'references'
        ];

        foreach ($multipleRelations as $relation) {
            $relationData = $excelData[$relation] ?? null;

            Log::info("Processing multiple relation: {$relation}", [
                'exists' => isset($excelData[$relation]),
                'is_array' => is_array($relationData),
                'data_count' => is_array($relationData) ? count($relationData) : 0,
                'data_type' => gettype($relationData)
            ]);

            // Delete existing records
            $deletedCount = $oldApplicant->$relation()->count();
            $oldApplicant->$relation()->delete();
            Log::info("Deleted {$deletedCount} existing {$relation} records");

            if (!empty($relationData) && is_array($relationData)) {
                $createdCount = 0;
                foreach ($relationData as $index => $record) {
                    if (!is_array($record)) {
                        Log::warning("Skipping non-array record in {$relation}", [
                            'index' => $index,
                            'type' => gettype($record)
                        ]);
                        continue;
                    }

                    $record['nPersonalInfo_id'] = $oldApplicant->id;

                    Log::info("Creating {$relation} record #{$index}", [
                        'data' => $record
                    ]);

                    try {
                        $created = $oldApplicant->$relation()->create($record);
                        $createdCount++;
                        Log::info("{$relation} record created successfully", [
                            'id' => $created->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create {$relation} record", [
                            'index' => $index,
                            'data' => $record,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }
                Log::info("Created {$createdCount} new {$relation} records");
            } else {
                Log::info("No new {$relation} records to create");
            }
        }

        // Handle personal_declarations separately (SINGLE RECORD, not multiple)
        Log::info("Processing single relation: personal_declarations", [
            'exists' => isset($excelData['personal_declarations']),
            'is_array' => is_array($excelData['personal_declarations'] ?? null),
            'data' => $excelData['personal_declarations'] ?? null
        ]);

        // Delete existing personal declarations
        $oldApplicant->personal_declarations()->delete();
        Log::info("Deleted existing personal_declarations records");

        if (!empty($excelData['personal_declarations']) && is_array($excelData['personal_declarations'])) {
            $declarationData = $excelData['personal_declarations'];

            // Check if this is already a single record (associative array with field names as keys)
            // vs an array of records (numeric indices)
            $isAssociativeArray = array_keys($declarationData) !== range(0, count($declarationData) - 1);

            if ($isAssociativeArray) {
                // It's a single record (e.g., ['question_34a' => 'YES', ...])
                $declarationData['nPersonalInfo_id'] = $oldApplicant->id;

                Log::info("Creating single personal_declarations record", [
                    'data' => $declarationData
                ]);

                try {
                    $created = $oldApplicant->personal_declarations()->create($declarationData);
                    Log::info("personal_declarations record created successfully", [
                        'id' => $created->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create personal_declarations record", [
                        'data' => $declarationData,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            } else {
                Log::warning("personal_declarations appears to be an array of records instead of a single record", [
                    'data_structure' => $declarationData
                ]);
            }
        } else {
            Log::info("No personal_declarations data to create");
        }

        Log::info('=== APPLICANT UPDATE COMPLETE ===', ['applicant_id' => $oldApplicant->id]);
    }
    /**
     * Delete temporary files
     */
    private function deleteTemporaryFiles($tempZipPath, $tempExcelPath)
    {
        try {
            if (Storage::exists($tempZipPath)) {
                Storage::delete($tempZipPath);
            }
            if (Storage::exists($tempExcelPath)) {
                Storage::delete($tempExcelPath);
            }
            Log::info('Temporary files deleted', [
                'zip' => $tempZipPath,
                'excel' => $tempExcelPath
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete temporary files', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a unique file name with timestamp and random string.
     */
    private function generateFileName($file)
    {
        if ($file instanceof \SplFileInfo) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        } else {
            $extension = $file->getClientOriginalExtension();
        }
        return time() . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Extract and manage ZIP files safely.
     */
    private function extractApplicantZip($zipPath, $nPersonalInfoId)
    {
        $zipFullPath = storage_path('app/public/' . $zipPath);

        if (!file_exists($zipFullPath)) {
            throw new \Exception("ZIP file not found at path: $zipFullPath");
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipFullPath) !== true) {
            throw new \Exception('Failed to open ZIP file.');
        }

        $extractPath = storage_path("app/public/applicant_files/{$nPersonalInfoId}");
        if (file_exists($extractPath)) {
            $this->deleteDirectory($extractPath, false);
        } else {
            mkdir($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();
    }

    /**
     * Send applicant confirmation or update email.
     */
    private function sendApplicantEmail($applicant, $jobId, $isUpdate)
    {
        $job = \App\Models\JobBatchesRsp::findOrFail($jobId);

        $subject = $isUpdate ? 'Application Updated' : 'Application Received';

        $template = 'mail-template.application';


        Mail::to($applicant->email_address)->queue(new EmailApi(
            $subject,
            $template,
            [
                'mailSubject' => $subject,
                'firstname' => $applicant->firstname,
                'lastname' => $applicant->lastname,
                'jobOffice' => $job->Office,
                'jobPosition' => $job->Position,
                'isUpdate' => $isUpdate,
            ]


        ));
    }

    // Add this method to your controller for testing
    public function testCache()
    {
        $testData = [
            'children' => [
                ['child_name' => 'Test Child', 'birth_date' => '2020-01-01']
            ]
        ];

        Cache::put('test_key', $testData, now()->addMinutes(10));
        $retrieved = Cache::get('test_key');

        Log::info('Cache Test', [
            'original' => $testData,
            'retrieved' => $retrieved,
            'match' => $testData === $retrieved,
            'cache_driver' => config('cache.default')
        ]);

        return response()->json([
            'original' => $testData,
            'retrieved' => $retrieved,
            'match' => $testData === $retrieved
        ]);
    }
}
