<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\EmailApi;
use App\Models\Schedule;
use App\Models\Submission;
use Doctrine\DBAL\Schema\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScheduleController extends Controller
{
    //
    public function applicantList()
    {
        $applicants = Submission::with([
            'nPersonalInfo:id,firstname,lastname',
            'xpersonal:ControlNo,Surname,Firstname',
            'job_batch_rsp:id,Position'
        ])->where('status','Qualified')
            ->whereDoesntHave('schedules')
            ->get()
            ->map(function ($item) {

                // 👇 Determine Name Source
                if ($item->nPersonalInfo_id) {
                    // Outside applicant
                    $firstname = optional($item->nPersonalInfo)->firstname;
                    $lastname  = optional($item->nPersonalInfo)->lastname;
                } else {
                    // Employee → get from xpersonal
                    $firstname = optional($item->xpersonal)->Firstname;
                    $lastname  = optional($item->xpersonal)->Surname;
                }

                return [
                    "submission_id"         => $item->id,
                    "nPersonalInfo_id"      => $item->nPersonalInfo_id,
                    "ControlNo"             => $item->ControlNo,
                    "job_batches_rsp_id"    => $item->job_batches_rsp_id,

                    // Final selected fullname
                    "firstname"             => $firstname,
                    "lastname"              => $lastname,

                    "job_batch_rsp"         => [
                        "job_batches_rsp_id" => $item->job_batch_rsp->id ?? null,
                        "Position"           => $item->job_batch_rsp->Position ?? null,
                    ],
                ];
            });

        return response()->json($applicants);
    }


    public function fetchApplicantHaveSchedule()
    {
        $schedules = Schedule::select('batch_name', 'venue_interview', 'date_interview', 'time_interview')
            ->get()
            ->groupBy(function ($item) {
                return $item->batch_name . '|' . $item->venue_interview . '|' . $item->date_interview . '|' . $item->time_interview;
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'batch_name' => $first->batch_name,
                    'venue_interview' => $first->venue_interview,
                    'date_interview' => $first->date_interview,
                    'time_interview' => $first->time_interview,
                    'applicant_no' => $group->count(),
                ];
            })
            ->values();

        return response()->json($schedules);
    }


    public function getApplicantInterview($date, $time)
    {
        $applicants = Schedule::with(['submission.job_batch_rsp'])
            ->where('date_interview', $date)
            ->where('time_interview', $time)
            ->get()
            ->map(function ($schedule) {
                return [
                    'batch_name'      => $schedule->batch_name,
                    'venue_interview' => $schedule->venue_interview,
                    'date_interview'  => $schedule->date_interview,
                    'time_interview'  => $schedule->time_interview,
                    'position'        => $schedule->submission->job_batch_rsp->Position ?? null,
                    'applicant_name'  => $schedule->full_name,
                ];
            });

        return response()->json($applicants);
    }
}
