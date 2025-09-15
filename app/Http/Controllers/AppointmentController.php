<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    //

    public function find_appointment()
    {
        $data = DB::table('vwplantillaStructure')
            ->where(function ($query) {
                $query->whereNull('ControlNo')
                    ->orWhere('ControlNo', '');
            })
            ->where('office', 'OFFICE OF THE CITY ACCOUNTANT') // ✅ filter by office
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function maxControlNo()
    {

        $data = DB::table('xPersonal')->max('ControlNo');
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function job_post()
    {

        $data = DB::table('tempRegAppointmentReorg')->limit(5)->get();
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function deleteControlNo($ControlNo)
    {
        // Example: check service record
        $hasDependencies = DB::table('xPersonal')
            ->where('ControlNo', $ControlNo)
            ->exists();

        if ($hasDependencies) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot delete, employee has service records.'
            ]);
        }

        $deleted = DB::table('xPersonal')->where('ControlNo', $ControlNo)->delete();

        return response()->json([
            'status' => 200,
            'deleted' => $deleted
        ]);
    }


    public function hireApplicant($submissionId)
    {
        DB::beginTransaction();

            try {
                $submission = Submission::with(['nPersonalInfo.children', 'nPersonalInfo.family',
                 'nPersonalInfo.work_experience', 'nPersonalInfo.eligibity', 'nPersonalInfo.education',
                  'nPersonalInfo.voluntary_work', 'nPersonalInfo.training', 'nPersonalInfo.references',
                  'nPersonalInfo.skills'
                  ])
                    ->findOrFail($submissionId);
                $applicant = $submission->nPersonalInfo;

            // Case 1: Already employee (no nPersonalInfo_id, but has ControlNo)
            if (!$submission->nPersonalInfo && $submission->ControlNo) {
                $finalControlNo = $submission->ControlNo;
                $applicant = null;   // no need to pull applicant data
                $family    = null;   // no family data insert needed
            }
            // Case 2: External applicant (has nPersonalInfo)
            else {
                $applicant = $submission->nPersonalInfo;

                if (!$applicant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Applicant personal info not found.'
                    ], 404);
                }

                $family = $applicant->family;

                // Check if internal employee (has ControlNo)
                $existingControlNo = $applicant->control_no ?? $applicant->controlno ?? $applicant->ControlNo ?? null;
                if (!$existingControlNo && isset($submission->ControlNo)) {
                    $existingControlNo = $submission->ControlNo;
                }


                // // ✅ Get max ControlNo
                // $maxControlNo = DB::table('xPersonal')->max('ControlNo');
                // $nextNumber   = $maxControlNo ? intval($maxControlNo) + 1 : 1;
                // $newControlNo = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                // If internal, use existing, else generate new
                if ($existingControlNo) {
                    $finalControlNo = $existingControlNo;
                } else {
                // Generate new ControlNo for external applicants
                $maxControlNo = DB::table('xPersonal')->max('ControlNo');
                $nextNumber   = $maxControlNo ? intval($maxControlNo) + 1 : 1;
                $finalControlNo = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                // ✅ Insert applicant into xPersonal
                DB::table('xPersonal')->insert([
                    'ControlNo'    => $finalControlNo,
                    'Surname'      => $applicant->lastname,
                    'Firstname'    => $applicant->firstname,
                    'Middlename'   => $applicant->middlename ?? '',
                    'Sex'          => $applicant->sex,
                    'CivilStatus'  => $applicant->civil_status ?? '',
                    'BirthDate'    => $applicant->date_of_birth,
                    'BirthPlace'   => $applicant->place_of_birth ?? '',
                    'Address'      => trim(($applicant->residential_house ?? '') . ' ' .
                        ($applicant->residential_street ?? '') . ' ' .
                        ($applicant->residential_barangay ?? '') . ' ' .
                        ($applicant->residential_city ?? '') . ' ' .
                        ($applicant->residential_province ?? '')),
                    'Citizenship'  => $applicant->citizenship ?? 'FILIPINO',
                    'Religion'     => $applicant->religion ?? '',
                    'Heights'      => $applicant->height ?? 0.0,
                    'Weights'      => $applicant->weight ?? 0.0,
                    'BloodType'    => $applicant->blood_type ?? '',
                    'TelNo'        => $applicant->telephone_number ?? '',
                    'TINNo'        => $applicant->tin_no ?? '',
                    'GSISNo'       => $applicant->gsis_no ?? '',
                    'PAGIBIGNo'    => $applicant->pagibig_no ?? '',
                    'SSSNo'        => $applicant->sss_no ?? '',
                    'PHEALTHNo'    => $applicant->philhealth_no ?? '',

                    // ✅ Father info
                    'FatherName'       => $family->father_lastname ?? 'N/A',
                    // ✅ Mother info
                    'MotherName'        => $family->mother_lastname ?? 'N/A',
                    // ✅ Spouse info

                    'MaidenName'        => $family->spouse_middlename ?? 'N/A',
                    'SpouseName'        => $family->spouse_name ?? 'N/A',
                    'Occupation'        => $family->spouse_occupation ?? 'N/A',
                ]);


                // ✅ Children
                $children = $applicant->children ?? collect();
                foreach ($children as $child) {
                    DB::table('xChildren')->insert([
                        'ControlNo'    => $finalControlNo,
                        'ChildName' => $child->child_name,
                        'BirthDate' => $child->birth_date,
                    ]);
                }

                $work_experience = $applicant->work_experience ?? collect();
                foreach ($work_experience as $experience) {
                    DB::table('xExperience')->insert([
                        'CONTROLNO'  => $finalControlNo,
                        'WFrom' => $experience->work_date_from,
                        'WTo' => $experience->work_date_to,
                        'WPosition' => $experience->position_title,
                        'WCompany' => $experience->department,
                        'WSalary' => $experience->monthly_salary,
                        'WGrade' => $experience->salary_grade,
                        'Status' => $experience->status_of_appointment,
                        'WGov' => $experience->government_service,
                    ]);
                }

                $Eligibility = $applicant->eligibity ?? collect();
                foreach ($Eligibility as $Eli) {
                    DB::table('xCivilService')->insert([
                        'ControlNo'    => $finalControlNo,
                        'CivilServe' => $Eli->eligibility,
                        'Dates' => $Eli->date_of_examination,
                        'Rates' => $Eli->rating,
                        'Place' => $Eli->place_of_examination,
                        'LNumber' => $Eli->license_number,
                        'LDate' => $Eli->date_of_validity,

                    ]);
                }

                $Education = $applicant->education ?? collect();
                foreach ($Education as $edu) {
                    DB::table('xEducation')->insert([
                        'ControlNo'    => $finalControlNo,
                        'Education'  => substr($edu->level, 0, 20),  // match varchar(20)
                        'School'     => substr($edu->school_name, 0, 50), // match varchar(50)
                        'Degree'     => substr($edu->degree, 0, 50),
                        'NumUnits'   => is_numeric($edu->highest_units) ? (float) $edu->highest_units : 0.0,
                        'YearLevel'  => substr((string) ($edu->year_graduated ?? ''), 0, 4),
                        'DateAttend' => substr($edu->attendance_from . ' - ' . $edu->attendance_to, 0, 15),
                        'Honors'     => substr((string) ($edu->scholarship ?? ''), 0, 30),
                        'Graduated' => $edu->attendance_to,


                    ]);
                }

                $voluntary_work = $applicant->voluntary_work ?? collect();
                foreach ($voluntary_work as $vol) {
                    DB::table('xNGO')->insert([
                        'CONTROLNO'  => $finalControlNo,
                        'OrgName'  => $vol->organization_name,
                        'DateFrom'     => $vol->inclusive_date_from,
                        'DateTo'     => $vol->inclusive_date_to,
                        'NoHours'   => $vol->number_of_hours,
                        'OrgPosition'  => $vol->position,
                    ]);
                }

                $training = $applicant->training ?? collect();

                foreach ($training as $train) {
                    if (!$train) {
                        continue; // ✅ skip null rows
                    }

                    DB::table('xTrainings')->insert([
                        'ControlNo'    => $finalControlNo,
                        'Training'   => $train->training_title ?? '',
                        'Dates'      => ($train->inclusive_date_from ?? '') . ' - ' . ($train->inclusive_date_to ?? ''),
                        'NumHours'   => $train->number_of_hours ?? 0,
                        'Conductor'  => $train->conducted_by ?? '',
                        'DateFrom'   => $train->inclusive_date_from ?? null,
                        'DateTo'     => $train->inclusive_date_to ?? null,
                        'Type'       => $train->type ?? '',
                    ]);
                }


                $skills = $applicant->skills ?? collect();
                foreach ($skills as $skill) {
                    DB::table('xSkills')->insert([
                        'ControlNo'    => $finalControlNo,
                        'Skills'  => $skill->skill,
                    ]);
                }

                $academic = $applicant->skills ?? collect();
                foreach ($academic as $acad) {
                    DB::table('xNonAcademic')->insert([
                        'ControlNo'    => $finalControlNo,
                        'NonAcademic'  => $acad->non_academic,
                    ]);
                }

                $organization = $applicant->skills ?? collect();
                foreach ($organization as $org) {
                    DB::table('xOrganization')->insert([
                        'ControlNo'    => $finalControlNo,
                        'Organization'  => $org->organization,
                    ]);
                }

                $reference = $applicant->references ?? collect();
                foreach ($reference as $ref) {
                    DB::table('xReference')->insert([
                        'ControlNo'    => $finalControlNo,
                        'Names'  => $ref->full_name,
                        'Address'     => $ref->address,
                        'TelNo'     => $ref->contact_number,

                    ]);
                }
            }
        }
        // ------------------------------------
        // ✅ Step 1: Update job post to Occupied
        // ------------------------------------
        $jobPost = JobBatchesRsp::findOrFail($submission->job_batches_rsp_id);
            $jobPost->update(['status' => 'Occupied']);

            // ✅ Also mark submission as hired
            $submission->update(['status' => 'Hired']);

            // ------------------------------------
            // ✅ Step 2: Update plantilla structure
            // ------------------------------------
            // Match plantilla slot by PageNo + ItemNo
            // $PositionID = $jobPost->PositionID;
            $itemNo = $jobPost->ItemNo;
            $pageNo = $jobPost->PageNo;

            $designation = DB::table('yDesignation')
                ->where('Descriptions', $jobPost->Position) // <-- match with Descriptions column
                ->first();

            $office = DB::table('yOffice')
                ->where('Descriptions', $jobPost->Office)
                ->orWhere('Codes', $jobPost->Office) // support both string or code
                ->first();
            $officeCode = $office->Codes ?? '00000';

            $salary = DB::table('tblSalarySchedule')
                // ->where('PositionID', $jobPost->PositionID)
                ->where('Grade', $jobPost->SalaryGrade)
                ->where('Steps', 1)
                ->first();


            $fromDate = Carbon::now()->startOfDay();      // today with 00:00:00
            $toDate   = $fromDate->copy()->addYears(50);   // +5 years with 00:00:00

            // ✅ Compute salary rates
            $rateMon  = $salary->Salary ?? 0;
            $rateDay  = $rateMon > 0 ? $rateMon / 22 : 0;
            $rateYear = $rateMon > 0 ? $rateMon * 12 : 0;  // +5 years with 00:00:00

            DB::table('xService')->insert([ // insert
                    'ControlNo'    => $finalControlNo,
                // 'FromDate'    => '2025-09-12 00:00:00',
                // 'ToDate'      => '2026-09-12 00:00:00',
                'FromDate'    => $fromDate->format('Y-m-d H:i:s'),
                'ToDate'      => $toDate->format('Y-m-d H:i:s'),
                // should be code, not designation text
                'DesigCode'   => $designation->Codes ?? '00000',
                'Designation' => $designation->Descriptions ?? $jobPost->Position,

                'StatCode'    => '00001',   // keep as string if db column is char(5)
                'Status'      => 'REGULAR',

                'OffCode'     => $officeCode ?? '00000',
                'Office'      => $jobPost->Office ?? 'NONE',

                'BranCode'    => '00001',
                'Branch'      => 'LGU-TAGUM',

                'LVRemarks'   => '',
                'RateDay'     => $rateDay,
                'RateMon'     => $rateMon,
                'RateYear'    => $rateYear,

                'SepDate'     => '',
                'SepCause'    => '',

                // numeric code, not string text
                'AppCode'     => '0',

                'DivCode'     => $jobPost->DivCode ?? '00000',
                'Divisions'   => $jobPost->Division ?? 'NONE',

                'SecCode'     => $jobPost->SecCode ?? '00000',
                'Sections'    => $jobPost->Section ?? 'NONE',

                // varchar fields, safe to use descriptive text
                'PlantCode'   => $jobPost->PlantCode ?? 'APPOINTMENT',
                'Renew'       => $jobPost->Renew ?? 'APPOINTMENT',

                'Grades'     => $jobPost->SalaryGrade,
                'Steps'       => 1,

                'Charges'     => '',

            ]);

            $structure = DB::table('tblStructureDetails')
                // ->where('PositionID', $jobPost->PositionID)
                ->where('PageNo', $jobPost->PageNo)
                ->where('ItemNo', $jobPost->ItemNo)
                ->first();

            $nextId = DB::table('tempRegAppointmentReorg')->max('ID') + 1;

            DB::table('tempRegAppointmentReorg')->insert([ // insert
                'ID'            => $nextId,
                'ControlNo'    => $finalControlNo,
                'DesigCode'      => $designation->Codes ?? '00000',
                'NewDesignation' => $designation->Descriptions ?? $jobPost->Position,
                'Designation'    => $designation->Descriptions ?? $jobPost->Position,
                'SG'             => $jobPost->SalaryGrade,
                'Step'           => 1,
                'Status'       => $designation->Status,
                'OffCode'        => $jobPost->OfficeCode,
                'NewOffice'      => $jobPost->Office,
                'Office'         => $jobPost->Office,
                'MRate'          => $rateMon,
                'Official'       => '0',
                'Renew'          => 'APPOINTMENT',
                'ItemNo'          => $itemNo,
                'Pages'          => $pageNo,

                'StructureID'    => $structure->StructureID ?? null,
            ]);


        DB::commit();

        return response()->json([
            'success'    => true,
            'message'    => 'Applicant hired successfully and plantilla updated.',
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

}
// public function hireApplicant($submissionId)
// {
//     try {
//         return DB::transaction(function () use ($submissionId) {
//             $submission = Submission::with([
//                 'nPersonalInfo.children',
//                 'nPersonalInfo.family',
//                 'nPersonalInfo.work_experience',
//                 'nPersonalInfo.eligibity',
//                 'nPersonalInfo.education',
//                 'nPersonalInfo.voluntary_work',
//                 'nPersonalInfo.training',
//                 'nPersonalInfo.references',
//                 'nPersonalInfo.skills',
//             ])->findOrFail($submissionId);

//             // Resolve applicant and control number
//             [$applicant, $finalControlNo] = $this->resolveApplicant($submission);

//             // If new applicant, insert all details
//             if ($applicant && !$this->existsInXPersonal($finalControlNo)) {
//                 $this->insertPersonalInfo($finalControlNo, $applicant);
//                 $this->insertFamily($finalControlNo, $applicant->family);
//                 $this->insertChildren($finalControlNo, $applicant->children);
//                 $this->insertWorkExperience($finalControlNo, $applicant->work_experience);
//                 $this->insertEligibility($finalControlNo, $applicant->eligibity);
//                 $this->insertEducation($finalControlNo, $applicant->education);
//                 $this->insertVoluntaryWork($finalControlNo, $applicant->voluntary_work);
//                 $this->insertTraining($finalControlNo, $applicant->training);
//                 $this->insertSkills($finalControlNo, $applicant->skills);
//                 $this->insertReferences($finalControlNo, $applicant->references);
//             }

//             // Update job post & submission
//             $jobPost = $this->updateJobPost($submission);

//             // Update plantilla structure
//             $this->updatePlantillaStructure($finalControlNo, $jobPost);

//             return response()->json([
//                 'success'    => true,
//                 'message'    => 'Applicant hired successfully and plantilla updated.',
//                 'control_no' => $finalControlNo,
//                 'job_post'   => $jobPost->id,
//             ]);
//         });
//     } catch (\Throwable $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Error hiring applicant',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }
// private function resolveApplicant($submission)
// {
//     if (!$submission->nPersonalInfo && $submission->ControlNo) {
//         return [null, $submission->ControlNo];
//     }

//     $applicant = $submission->nPersonalInfo;
//     if (!$applicant) {
//         throw new \Exception('Applicant personal info not found.');
//     }

//     $controlNo = $applicant->control_no
//         ?? $applicant->controlno
//         ?? $applicant->ControlNo
//         ?? $submission->ControlNo;

//     if (!$controlNo) {
//         $controlNo = $this->generateControlNo();
//     }

//     return [$applicant, $controlNo];
// }

// private function generateControlNo()
// {
//     $max = DB::table('xPersonal')->max('ControlNo');
//     return str_pad(($max ? intval($max) + 1 : 1), 6, '0', STR_PAD_LEFT);
// }

// private function existsInXPersonal($controlNo)
// {
//     return DB::table('xPersonal')->where('ControlNo', $controlNo)->exists();
// }

// private function insertPersonalInfo($controlNo, $applicant)
// {
//     DB::table('xPersonal')->insert([
//         'ControlNo'   => $controlNo,
//         'Surname'     => $applicant->lastname,
//         'Firstname'   => $applicant->firstname,
//         'Middlename'  => $applicant->middlename ?? '',
//         'Sex'         => $applicant->sex,
//         'CivilStatus' => $applicant->civil_status ?? '',
//         'BirthDate'   => $applicant->date_of_birth,
//         'BirthPlace'  => $applicant->place_of_birth ?? '',
//         'Address'     => $this->formatAddress($applicant),
//         'Citizenship' => $applicant->citizenship ?? 'FILIPINO',
//         'Religion'    => $applicant->religion ?? '',
//     ]);
// }

// private function formatAddress($applicant)
// {
//     return trim(implode(' ', array_filter([
//         $applicant->residential_house,
//         $applicant->residential_street,
//         $applicant->residential_barangay,
//         $applicant->residential_city,
//         $applicant->residential_province,
//     ])));
// }

// private function insertChildren($controlNo, $children)
// {
//     collect($children)->each(fn($child) =>
//         DB::table('xChildren')->insert([
//             'ControlNo' => $controlNo,
//             'ChildName' => $child->child_name,
//             'BirthDate' => $child->birth_date,
//         ])
//     );
// }
