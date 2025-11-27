<?php

namespace App\Services;

use Carbon\Carbon;
use App\Mail\EmailApi;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\TempRegHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class ApplicantHiringService


{

    public function hireApplicant($submissionId,Request $request)
    {
        DB::beginTransaction();
        try {
            // These methods exist on Illuminate\Http\Request
            $request->validate([
                'sepdate' => 'nullable|date',
                'sepcause' => 'nullable|string|max:255',
                'vicename' => 'nullable|string|max:255',
                'vicecause' => 'nullable|string|max:255',
            ]);

            $sepdate = $request->input('sepdate');
            $sepcause = $request->input('sepcause');
            $vicename = $request->input('vicename');
            $vicecause = $request->input('vicecause');

            $submission = Submission::with([
                'nPersonalInfo.children',
                'nPersonalInfo.family',
                'nPersonalInfo.work_experience',
                'nPersonalInfo.eligibity',
                'nPersonalInfo.education',
                'nPersonalInfo.voluntary_work',
                'nPersonalInfo.training',
                'nPersonalInfo.references',
                'nPersonalInfo.skills',
                'nPersonalInfo.personal_declarations'
            ])->findOrFail($submissionId);

            $applicant = $submission->nPersonalInfo;

            // Case 1: Already employee
            if (!$applicant && $submission->ControlNo) {
                $finalControlNo = $submission->ControlNo;
            } else {
                if (!$applicant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Applicant personal info not found.'
                    ], 404);
                }

                $family = $applicant->family;
                $personal_declarations = $applicant->personal_declarations->first();
                $existingControlNo = $applicant->control_no ?? $applicant->controlno ?? $applicant->ControlNo ?? $submission->ControlNo ?? null;

                $finalControlNo = $existingControlNo ?? $this->generateControlNo();

                if (!$existingControlNo) {
                    $this->insertPersonalInfo($applicant, $family,  $personal_declarations, $finalControlNo);
                    $this->insertChildren($applicant->children, $finalControlNo);
                    $this->insertWorkExperience($applicant->work_experience, $finalControlNo);
                    $this->insertEligibility($applicant->eligibity, $finalControlNo);
                    $this->insertEducation($applicant->education, $finalControlNo);
                    $this->insertVoluntaryWork($applicant->voluntary_work, $finalControlNo);
                    $this->insertTraining($applicant->training, $finalControlNo);
                    $this->insertSkills($applicant->skills, $finalControlNo);
                    $this->insertNonAcademic($applicant->skills, $finalControlNo);
                    $this->insertOrganization($applicant->skills, $finalControlNo);
                    $this->insertReferences($applicant->references, $finalControlNo);
                }
            }

            // Update job post and submission status
            $jobPost = JobBatchesRsp::findOrFail($submission->job_batches_rsp_id);

            if ($jobPost->status === 'Occupied') {
                return response()->json([
                    'success' => false,
                    'message' => 'This job post is already occupied.'
                ], 400);
            }

            $jobPost->update(['status' => 'Occupied']);
            $submission->update(['status' => 'Hired']);

            // Update plantilla structure
            $this->updatePlantillaStructure($jobPost, $finalControlNo, $sepdate, $sepcause, $vicename, $vicecause);

            // ✅ Send email notification to the hired applicant
            $externalApplicant = DB::table('xPersonalAddt')
                ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
                ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
                ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
                ->first();

            $activeApplicant = $applicant ?? $externalApplicant;

            // if ($activeApplicant) {
            //     $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;
            //     $fullname = $applicant
            //         ? trim("{$applicant->firstname} {$applicant->lastname}")
            //         : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

            //     $position = $jobPost->Position ?? 'the applied position';
            //     $office = $jobPost->Office ?? 'the corresponding office';

            //     if (!empty($email)) {
            //         $subject = "🎉 Congratulations! You're Hired!";
            //         $message = "
            //         Dear {$fullname},<br><br>
            //         We are delighted to inform you that you have been <strong>officially hired</strong>
            //         for the position of <strong>{$position}</strong> under <strong>{$office}</strong>.<br><br>
            //         Please expect further instructions regarding your onboarding and necessary documentation.<br><br>
            //         Congratulations once again and welcome to the team!<br><br>
            //         Best regards,<br>
            //         <strong>Human Resource Department</strong>
            //     ";

            //         // Send email
            //         Mail::to($email)->queue(new EmailApi($message, $subject));
            //     }
            // } else {
            //     Log::warning("⚠️ No applicant email found for hired submission ID: {$submissionId}");
            // }

            $user = Auth::user();

            if ($user && $user instanceof \App\Models\User) {

                $position = $jobPost->Position ?? $jobPost->position ?? 'Unknown Position';

                activity($user->username)
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->withProperties([
                        'username' => $user->username,
                        'office' => $user->office,
                        'job_position'      => $position,
                        'hired_control_no'  => $finalControlNo,
                        'submission_id'     => $submission->id,
                        'ip_address'        => $request->ip(),
                        'user_agent'        => $request->header('User-Agent'),
                    ])
                    ->log("{$user->name} hired applicant (ControlNo: {$finalControlNo}) for job post {$jobPost->id}.");
            }


            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Applicant hired successfully, plantilla updated, and email sent.',
                'control_no' => $finalControlNo,
                'job_post'   => $jobPost->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error hiring applicant',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    private function generateControlNo()
    {
        $maxControlNo = DB::table('xPersonal')->max('ControlNo');
        $nextNumber   = $maxControlNo ? intval($maxControlNo) + 1 : 1;
        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function insertPersonalInfo($applicant, $family,  $personal_declarations, $controlNo)
    {
        DB::table('xPersonal')->insert([
            'ControlNo'    => $controlNo,
            'Surname'      => $applicant->lastname,
            'Firstname'    => $applicant->firstname,
            'Middlename'   => $applicant->middlename ?? null,
            'Sex'          => $applicant->sex,
            'CivilStatus'  => $applicant->civil_status ?? null,
            'BirthDate'    => $applicant->date_of_birth,
            'BirthPlace'   => $applicant->place_of_birth ?? null,
            'Address'      => trim(($applicant->residential_house ?? null) . ' ' .
                ($applicant->residential_street ?? null) . ' ' .
                ($applicant->residential_barangay ?? null) . ' ' .
                ($applicant->residential_city ?? null) . ' ' .
                ($applicant->residential_province ?? null)),
            'Citizenship'  => $applicant->citizenship ?? null,
            'Religion'     => $applicant->religion ?? null,
            'Heights'      => $applicant->height ?? null,
            'Weights'      => $applicant->weight ?? null,
            'BloodType'    => $applicant->blood_type ?? null,
            'TelNo'        => $applicant->telephone_number ?? null,
            'TINNo'        => $applicant->tin_no ?? null,
            'GSISNo'       => $applicant->gsis_no ?? null,
            'PAGIBIGNo'    => $applicant->pagibig_no ?? null,
            'SSSNo'        => $applicant->sss_no ?? null,
            'PHEALTHNo'    => $applicant->philhealth_no ?? null,
            'Pics' => $applicant->image_path ?? null,
            'FatherName'   => $family->father_lastname ?? null,
            'MotherName'   => $family->mother_lastname ?? null,
            'MaidenName'   => $family->spouse_middlename ?? null,
            'SpouseName'   => $family->spouse_name ?? null,
            'Occupation'   => $family->spouse_occupation ?? null,
           // need to fix identify the  what is the q-r
            'Q1' =>  $personal_declarations->{'question_34a'} ?? null,
            'R1' =>  $personal_declarations->{'response_34'} ?? null,

            'Q2' =>  $personal_declarations->{'question_34b'}  ?? null,
            'R2' =>  $personal_declarations->{'response_34'} ?? null,

            'Q3' =>  $personal_declarations->{'question_35a'} ?? null,
            'R3' =>  $personal_declarations->{'response_35a'} ?? null,

            'Q4' =>  $personal_declarations->{'question_36'}  ?? null,
            'R4' =>  $personal_declarations->{'response_36'}  ?? null,

            'Q5' =>  $personal_declarations->{'question_37'}  ?? null,
            'R5' =>  $personal_declarations->{'response_37'}  ?? null,

            'Q6' =>  $personal_declarations->{'question_39'} ?? null,
            'R6' =>  $personal_declarations->{'response_39'}   ?? null,

            'Q7' =>  $personal_declarations->{'question_40a'} ?? null,
            'R7' =>  $personal_declarations->{'response_40a'}  ?? null,

            'R11' => $personal_declarations->{'question_40b'} ?? null,
            'Q11' =>  $personal_declarations->{'response_40b'}?? null,
            'Q22' =>  $personal_declarations->{'question_40c'} ?? null,
        ]);
    }

    private function insertChildren($children, $controlNo)
    {
        foreach ($children ?? [] as $child) {
            DB::table('xChildren')->insert([
                'ControlNo' => $controlNo,
                'ChildName' => $child->child_name,
                'BirthDate' => $child->birth_date,
            ]);
        }
    }

    private function insertWorkExperience($experiences, $controlNo)
    {
        foreach ($experiences ?? [] as $exp) {
            DB::table('xExperience')->insert([
                'CONTROLNO'  => $controlNo,
                'WFrom'      => $exp->work_date_from,
                'WTo'        => $exp->work_date_to,
                'WPosition'  => $exp->position_title,
                'WCompany'   => $exp->department,
                'WSalary'    => $exp->monthly_salary,
                'WGrade'     => $exp->salary_grade,
                'Status'     => $exp->status_of_appointment,
                'WGov'       => $exp->government_service,
            ]);
        }
    }

    private function insertEligibility($eligibilities, $controlNo)
    {
        foreach ($eligibilities ?? [] as $eli) {
            DB::table('xCivilService')->insert([
                'ControlNo'   => $controlNo,
                'CivilServe'  => $eli->eligibility,
                'Dates'       => $eli->date_of_examination,
                'Rates'       => $eli->rating,
                'Place'       => $eli->place_of_examination,
                'LNumber'     => $eli->license_number,
                'LDate'       => $eli->date_of_validity,
            ]);
        }
    }

    private function insertEducation($educations, $controlNo)
    {
        foreach ($educations ?? [] as $edu) {
            DB::table('xEducation')->insert([
                'ControlNo'   => $controlNo,
                'Education'   => substr($edu->level, 0, 20),
                'School'      => substr($edu->school_name, 0, 50),
                'Degree'      => substr($edu->degree, 0, 50),
                'NumUnits'    => is_numeric($edu->highest_units) ? (float) $edu->highest_units : 0.0,
                'YearLevel'   => substr((string) ($edu->year_graduated ?? ''), 0, 4),
                'DateAttend'  => substr($edu->attendance_from . ' - ' . $edu->attendance_to, 0, 15),
                'Honors'      => substr((string) ($edu->scholarship ?? ''), 0, 30),
                'Graduated'   => $edu->attendance_to,
            ]);
        }
    }

    private function insertVoluntaryWork($works, $controlNo)
    {
        foreach ($works ?? [] as $work) {
            DB::table('xNGO')->insert([
                'CONTROLNO'   => $controlNo,
                'OrgName'     => $work->organization_name,
                'DateFrom'    => $work->inclusive_date_from,
                'DateTo'      => $work->inclusive_date_to,
                'NoHours'     => $work->number_of_hours,
                'OrgPosition' => $work->position,
            ]);
        }
    }

    private function insertTraining($trainings, $controlNo)
    {
        foreach ($trainings ?? [] as $train) {
            if (!$train) continue;
            DB::table('xTrainings')->insert([
                'ControlNo'   => $controlNo,
                'Training'    => $train->training_title ?? '',
                'Dates'       => ($train->inclusive_date_from ?? '') . ' - ' . ($train->inclusive_date_to ?? ''),
                'NumHours'    => $train->number_of_hours ?? 0,
                'Conductor'   => $train->conducted_by ?? '',
                'DateFrom'    => $train->inclusive_date_from ?? null,
                'DateTo'      => $train->inclusive_date_to ?? null,
                'Type'        => $train->type ?? '',
            ]);
        }
    }

    private function insertSkills($skills, $controlNo)
    {
        foreach ($skills ?? [] as $skill) {
            DB::table('xSkills')->insert([
                'ControlNo' => $controlNo,
                'Skills'    => $skill->skill,
            ]);
        }
    }

    private function insertNonAcademic($academics, $controlNo)
    {
        foreach ($academics ?? [] as $acad) {
            DB::table('xNonAcademic')->insert([
                'ControlNo'   => $controlNo,
                'NonAcademic' => $acad->non_academic ?? '',
            ]);
        }
    }

    private function insertOrganization($organizations, $controlNo)
    {
        foreach ($organizations ?? [] as $org) {
            DB::table('xOrganization')->insert([
                'ControlNo'     => $controlNo,
                'Organization'  => $org->organization ?? '',
            ]);
        }
    }

    private function insertReferences($references, $controlNo)
    {
        foreach ($references ?? [] as $ref) {
            DB::table('xReference')->insert([
                'ControlNo' => $controlNo,
                'Names'     => $ref->full_name,
                'Address'   => $ref->address,
                'TelNo'     => $ref->contact_number,
            ]);
        }
    }

    private function updatePlantillaStructure($jobPost, $controlNo, $sepdate, $sepcause, $vicename, $vicecause)
    {

        $tblStructureDetails_ID = $jobPost->tblStructureDetails_ID;
        $itemNo = $jobPost->ItemNo;
        $pageNo = $jobPost->PageNo;

        // Move old records to history, then delete old records
        $oldRecords = DB::table('tempRegAppointmentReorg')->where('ControlNo', $controlNo)->get();
        foreach ($oldRecords as $row) {
            TempRegHistory::create((array) $row);
        }
        DB::table('tempRegAppointmentReorg')->where('ControlNo', $controlNo)->delete();

        $designation = DB::table('yDesignation')
            ->select('Codes', 'Descriptions', 'Status')
            ->where('Descriptions', $jobPost->Position)
            ->where('Status', 'REGULAR') // always prefer Regular
            ->first();

        $office = DB::table('yOffice')
            ->where('Descriptions', $jobPost->Office)
            ->orWhere('Codes', $jobPost->Office)
            ->first();

        $officeCode = $office->Codes ?? '00000';

        $salary = DB::table('tblSalarySchedule')
            ->where('Grade', $jobPost->SalaryGrade)
            ->where('Steps', 1)
            ->first();

        $rateMon  = $salary->Salary ?? 0;
        $rateDay  = $rateMon > 0 ? $rateMon / 22 : 0;
        $rateYear = $rateMon * 12;

        $fromDate = Carbon::now()->startOfDay();
        $toDate   = $fromDate->copy()->addYears(50);


        $Division = DB::table('yDivision')
            ->where('Descriptions', $jobPost->Division)
            ->orWhere('Codes', $jobPost->Division)
            ->first();

        $DivCode = $Division->Codes ?? '00000';


        $Section = DB::table('ySection')
            ->where('Descriptions', $jobPost->Section)
            ->orWhere('Codes', $jobPost->Section)
            ->first();

        $SecCode = $Section->Codes ?? '00000';

        $Unit = DB::table('yUnit')
            ->where('Descriptions', $jobPost->Unit)
            ->orWhere('Codes', $jobPost->Unit)
            ->first();

        $UnitCode =    $Unit->Codes ?? '00000';

        DB::table('xService')
            ->where('ControlNo', $controlNo)
            ->where('ToDate', '>', $fromDate)
            ->update(['ToDate' => Carbon::parse($fromDate)->subDay()]);

        DB::table('xService')->insert([
            'ControlNo'    => $controlNo, // 1
            'FromDate'     => $fromDate->format('Y-m-d H:i:s'), // 1
            'ToDate'       => $toDate->format('Y-m-d H:i:s'), // 1
            'DesigCode'    => $designation->Codes ?? '00000', // 1
            'Designation'  => $designation->Descriptions ?? $jobPost->Position, // 1
            'StatCode'     => '00001', // 1
            'Status'       => 'REGULAR',
            'OffCode'      => $officeCode ?? '00000', // 1
            'Office'       => $jobPost->Office ?? 'NONE',  // 1
            'BranCode'     => '00001',
            'Branch'       => 'LGU-TAGUM',
            'LVRemarks'    => '',
            'RateDay'      => $rateDay, // 1
            'RateMon'      => $rateMon, // 1
            'RateYear'     => $rateYear, // 1
            'SepDate'      => '',
            'SepCause'     => '',
            'AppCode'      => '0',
            'DivCode'      => $DivCode ?? null, // 1
            'Divisions'    => $jobPost->Division ?? null, // 1
            'SecCode'      => $SecCode ?? null, // 1
            'Sections'     => $jobPost->Section ?? null, // 1
            'PlantCode'    => $jobPost->PlantCode ?? null, // 1
            'Renew'        => $jobPost->Renew ?? null, // 1
            'Grades'       => $jobPost->SalaryGrade ?? null, // 1
            'Steps'        => 1,
            'Charges'      => '',
        ]);

        $structure = DB::table('tblStructureDetails')
            ->where('ID', $jobPost->tblStructureDetails_ID)
            ->where('PageNo', $jobPost->PageNo)
            ->where('ItemNo', $jobPost->ItemNo)
            ->first();

        // $nextId = DB::table('tempRegAppointmentReorg')->max('ID') + 1;

        DB::table('tempRegAppointmentReorg')->insert([
            // 'ID'            => $nextId,
            'ControlNo'     => $controlNo,//1
            'DesigCode'     => $designation->Codes ?? null, //1
            'NewDesignation' => $designation->Descriptions ?? $jobPost->Position, //1
            'Designation'   => $designation->Descriptions ?? $jobPost->Position, //1
            'SG'            => $jobPost->SalaryGrade, //1
            'Step'          => 1,
            'Status'        => $designation->Status, //1
            'OffCode'       => $officeCode, //1
            'NewOffice'     => $jobPost->Office,
            'Office'        => $jobPost->Office,
            'MRate'         => $rateMon, //1
            'Official'      => 0,
            'Renew'         => 'REAPPOINTMENT PURSUANT TO RA6656',
            'ItemNo'        => $itemNo,
            'Pages'         => $pageNo,
            'StructureID'   => $structure->StructureID ?? null,
            'DivCode' => $DivCode ?? null, // 1
            'SecCode' => $SecCode ?? null, // 1
            // 'groupcode',//
            // 'group',//
            'unitcode' =>     $UnitCode ?? null,
            'unit' => $jobPost->Unit ?? null,
            'sepdate' => $sepdate,
            'sepcause' => $sepcause,
            'vicename' => $vicename,
            'vicecause' => $vicecause

        ]);

        DB::table('posting_date')->insert([
            'ControlNo'     => $controlNo, //1
            'post_date' =>$jobPost->post_date,
            'end_date' => $jobPost->end_date,
            'job_batches_rsp_id' =>$jobPost->id
        ]);
    }
}
