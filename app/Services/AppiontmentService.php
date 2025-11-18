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
use Illuminate\Support\Facades\Mail;


class AppiontmentService


{
    public function Elective(Request $request)
    {
        // ✅ Step 1: Validate all inputs
        $validated = $request->validate([
            'Office' => 'required|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'required|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string',
            'tblStructureDetails_ID' => 'required|string',

            // xService
            'FromDate' => 'required|date',
            'ToDate' => 'required|date',
            'Status' => 'required|string',


            // tempRegAppointmentReorg
            'sepdate' => 'nullable|date',
            'sepcause' => 'nullable|string',
            'vicename' => 'nullable|string',
            'vicecause' => 'nullable|string',
            'Renew' => 'nullable|string',

            // for identifying control number or user
            'controlNo' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Step 2: Convert to object (like    $job)

            $job = (object) $validated;

            // --- Step 1: Update PageNo if PageNo exists ---
            if ($request->has('PageNo') && $job->tblStructureDetails_ID && $job->ItemNo) {
                $exists = DB::table('tblStructureDetails')
                    ->where('PageNo', $job->PageNo)
                    ->where('ItemNo', $job->ItemNo)
                    ->where('ID', '<>', $job->tblStructureDetails_ID)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Duplicate PageNo and ItemNo already exists in plantilla.'
                    ], 422);
                }

                DB::table('tblStructureDetails')
                    // ->where('PositionID', $jobValidated['PositionID'])
                    ->where('ID', $job->tblStructureDetails_ID)
                    ->where('ItemNo', $job->ItemNo)
                    ->update(['PageNo' => $job->PageNo]);
            }

            // ✅ Step 3: Pass values to updatePlantillaStructure
            $this->updatePlantillaStructure(
                $job,

                // $validated['controlNo'],
                // $validated['sepdate'] ?? null,
                // $validated['sepcause'] ?? null,
                // $validated['vicename'] ?? null,
                // $validated['vicecause'] ?? null,

            );

            DB::commit();

            return response()->json([
                'message' => 'Plantilla structure updated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ElectiveService Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update plantilla structure',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


  private function updatePlantillaStructure($job)
    {

        $tblStructureDetails_ID = $job->tblStructureDetails_ID;
        $itemNo = $job->ItemNo;
        $pageNo = $job->PageNo;

        // Move old records to history, then delete old records
        $oldRecords = DB::table('tempRegAppointmentReorg')->where('ControlNo', $job->controlNo)->get();
        foreach ($oldRecords as $row) {
            TempRegHistory::create((array) $row);
        }
        DB::table('tempRegAppointmentReorg')->where('ControlNo', $job->controlNo)->delete();


        $designation = DB::table('yDesignation')
            ->select('Codes', 'Descriptions', 'Status')
            ->where('Descriptions', $job->Position ?? null)
            ->where('Status', $job->Status ?? null)
            ->first();


        $StartCode = DB::table('yStatus')
            ->select('Codes', 'Descriptions',)
            ->where('Descriptions', $job->Status ?? null)

            ->first();


        $office = DB::table('yOffice')
            ->where('Descriptions', $job->Office ?? null)
            ->orWhere('Codes', $job->Office ?? null)
            ->first();

        $officeCode = $office->Codes ?? '00000';

        $salary = DB::table('tblSalarySchedule')
            ->where('Grade', $job->SalaryGrade ?? null)
            ->where('Steps', 1 ?? null)  // NEED TO ADJUST IF THIS  IS ALWAYS 1 OF CAN BE CHANGE ANYTIME IF THEY WILL BE SELECT AND ELECTIVE
            ->first();

        // Convert & assign dates correctly for elective
        $fromDate = $job->FromDate ? Carbon::parse($job->FromDate)->startOfDay() : null;
        $toDate = $job->ToDate ? Carbon::parse($job->ToDate)->endOfDay() : null;


        $rateMon  = $salary->Salary ?? 0;
        $rateDay  = $rateMon > 0 ? $rateMon / 22 : 0;
        $rateYear = $rateMon * 12;

        $Division = DB::table('yDivision')
            ->where('Descriptions', $job->Division ?? null)
            ->orWhere('Codes', $job->Division ?? null)
            ->first();

        $DivCode = $Division->Codes ?? '00000';


        $Section = DB::table('ySection')
            ->where('Descriptions', $job->Section ?? null)
            ->orWhere('Codes', $job->Section ?? null)
            ->first();

        $SecCode = $Section->Codes ?? '00000';

        $Unit = DB::table('yUnit')
            ->where('Descriptions', $job->Unit ?? null)
            ->orWhere('Codes', $job->Unit ?? null)
            ->first();

        $UnitCode =    $Unit->Codes ?? '00000';

        DB::table('xService')
            ->where('ControlNo', $job->controlNo)
            ->where('ToDate', '>', $fromDate)
            ->update(['ToDate' => Carbon::parse($fromDate)->subDay()]);

        DB::table('xService')->insert([
            'ControlNo'    => $job->controlNo, // 1
            'FromDate'     => $fromDate ?? null,// 1
            'ToDate'       =>   $toDate   ?? null, // 1
            'DesigCode'    => $designation->Codes ?? '00000', // 1
            'Designation'  => $designation->Descriptions ?? $job->Position, // 1
            'StatCode'     => $StartCode->Codes ?? null, //1
            'Status'       => $job->Status ?? null, // 1
            'OffCode'      => $officeCode ?? '00000', // 1
            'Office'       => $job->Office ?? 'NONE',  // 1
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
            'Divisions'    => $job->Division ?? null, // 1
            'SecCode'      => $SecCode ?? null, // 1
            'Sections'     => $job->Section ?? null, // 1
            'PlantCode'    => $job->PlantCode ?? null, // 1
            'Renew'        => $job->Renew ?? null, // 1
            'Grades'       => $job->SalaryGrade ?? null, // 1
            'Steps'        => 1,
            'Charges'      => '',
        ]);

        $structure = DB::table('tblStructureDetails')
            ->where('ID', $job->tblStructureDetails_ID)
            ->where('PageNo', $job->PageNo)
            ->where('ItemNo', $job->ItemNo)
            ->first();

        // $nextId = DB::table('tempRegAppointmentReorg')->max('ID') + 1;

        DB::table('tempRegAppointmentReorg')->insert([
            // 'ID'            => $nextId,
            'ControlNo'     => $job->controlNo,//1
            'DesigCode'     => $designation->Codes ?? null, //1
            'NewDesignation' => $designation->Descriptions ?? $job->Position, //1
            'Designation'   => $designation->Descriptions ?? $job->Position, //1
            'SG'            => $job->SalaryGrade ?? null, //1
            'Step'          => 1,
            'Status'        => $job->Status ?? null, // ✅ fixed
            'OffCode'       => $officeCode, //1
            'NewOffice'     => $job->Office,
            'Office'        => $job->Office,
            'MRate'         => $rateMon, //1
            'Official'      => 0,
            'Renew'         => $job->Renew ?? null, //1
            'ItemNo'        => $itemNo,
            'Pages'         => $pageNo,
            'StructureID'   => $structure->StructureID ?? null,
            'DivCode' => $DivCode ?? null, // 1
            'SecCode' => $SecCode ?? null, // 1
            // 'groupcode',//
            // 'group',//
            'unitcode' =>     $UnitCode ?? null,
            'unit' => $job->Unit ?? null,
            'sepdate' => $job->sepdate ?? null  ,
            'sepcause' => $job->sepcause ?? null,
            'vicename' => $job->vicename ?? null,
            'vicecause' => $job->vicecause ?? null

        ]);

        // DB::table('posting_date')->insert([
        //     'ControlNo'     => $job->controlNo, //1
        //     'post_date' =>$job->post_date,
        //     'end_date' => $job->end_date,
        //     'job_batches_rsp_id' =>$job->id
        // ]);
    }
}
