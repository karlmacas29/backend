<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    //

    public function find_appointment(){

        $data = DB::table('nEducation')->limit(500)->get();
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
            $family    = $applicant->family; // ✅ direct family record

            if (!$applicant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Applicant personal info not found.'
                ], 404);
            }

            // ✅ Get max ControlNo
            $maxControlNo = DB::table('xPersonal')->max('ControlNo');
            $nextNumber   = $maxControlNo ? intval($maxControlNo) + 1 : 1;
            $newControlNo = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // ✅ Insert applicant into xPersonal
            DB::table('xPersonal')->insert([
                'ControlNo'    => $newControlNo,
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
                    'ControlNo' => $newControlNo,
                    'ChildName' => $child->child_name,
                    'BirthDate' => $child->birth_date,
                ]);
            }


            $work_experience = $applicant->work_experience ?? collect();
            foreach ($work_experience as $experience) {
                DB::table('xExperience')->insert([
                    'CONTROLNO' => $newControlNo,
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
                    'ControlNo' => $newControlNo,
                    'CivilServe' => $Eli->eligibilty,
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
                    'ControlNo'  => $newControlNo,
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
                    'CONTROLNO'  => $newControlNo,
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
                    'ControlNo'  => $newControlNo,
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
                    'ControlNo'  => $newControlNo,
                    'Skills'  => $skill->skill,
                ]);
            }

            $academic = $applicant->skills ?? collect();
            foreach ($academic as $acad) {
                DB::table('xNonAcademic')->insert([
                    'ControlNo'  => $newControlNo,
                    'NonAcademic'  => $acad->non_academic,
                ]);
            }

            $organization = $applicant->skills ?? collect();
            foreach ($organization as $org) {
                DB::table('xOrganization')->insert([
                    'ControlNo'  => $newControlNo,
                    'Organization'  => $org->organization,
                ]);
            }

            $reference = $applicant->references ?? collect();
            foreach ($reference as $ref) {
                DB::table('xReference')->insert([
                    'ControlNo'  => $newControlNo,
                    'Names'  => $ref->full_name,
                    'Address'     => $ref->address,
                    'TelNo'     => $ref->contact_number,

                ]);
            }



            // ✅ Update plantilla
            JobBatchesRsp::where('id', $submission->job_batches_rsp_id)
                ->update(['status' => 'Hired']);

            // ✅ Update submission
            $submission->update(['status' => 'Hired']);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Applicant hired successfully.',
                'control_no' => $newControlNo,
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
