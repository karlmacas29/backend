<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\EmailApi;
use App\Models\Schedule;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    //
    public function sendEmailInterview(Request $request)
    {
        $validated = $request->validate([
            'applicants' => 'required|array',
            'applicants.*.submission_id' => 'required|exists:submission,id',
            'applicants.*.job_batches_rsp' => 'required|exists:job_batches_rsp,id',
            'date_interview' => 'required|date',
            'time_interview' => 'required|string',
            'venue_interview' => 'required|string',
            'batch_name' => 'required|string',
        ]);

        $date = Carbon::parse($validated['date_interview'])->format('F d, Y');
        $time = Carbon::parse($validated['time_interview'])->format('g:i A'); // <-- changed
        $venue = $validated['venue_interview'];
        $batchName = $validated['batch_name'];

        $count = 0;

        foreach ($validated['applicants'] as $app) {

            $submission = Submission::with('nPersonalInfo')->find($app['submission_id']);
            if (!$submission) continue;

            $job = JobBatchesRsp::find($app['job_batches_rsp']);
            if (!$job) continue;

            $position = $job->Position ?? 'the applied position';
            $office = $job->Office ?? 'the corresponding office';
            $SalaryGrade = $job->SalaryGrade ?? 'the corresponding SG';

            // Get applicant info
            if ($submission->nPersonalInfo_id) {
                $firstname = $submission->nPersonalInfo->firstname;
                $lastname  = $submission->nPersonalInfo->lastname;
                $email     = $submission->nPersonalInfo->email_address ?? null;
            } else if ($submission->ControlNo) {
                $employee = DB::table('xPersonalAddt')
                    ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
                    ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
                    ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd')
                    ->first();

                if (!$employee) continue;

                $firstname = $employee->Firstname;
                $lastname = $employee->Surname;
                $email = $employee->EmailAdd;
            } else {
                continue;
            }

            $fullname = trim("$firstname $lastname");
            if (!$email) continue;

            try {
                Mail::to($email)->queue(new EmailApi(

                    "Interview Invitation",
                    'mail-template.interview',
                    [
                        'mailSubject' => "Interview Invitation",
                        'fullname' => $fullname,
                        'date' => $date,
                        'time' => $time,
                        'venue' => $venue,
                        'position' => $position,
                        'SalaryGrade' => $SalaryGrade,
                        'office' => $office,
                    ]
                ));

                Schedule::create([
                    'submission_id' => $submission->id,
                    'batch_name' => $batchName,
                    'full_name' => $fullname,
                    'date_interview' => $validated['date_interview'],
                    'time_interview' => $time,
                    'venue_interview' => $venue,
                ]);

                $count++;
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email to {$fullname} ({$email}): {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Interview invitations successfully sent to {$count} applicant(s).",
        ]);
    }

 

    public function sendEmailApplicantBatch(Request $request)
    {
        $validated = $request->validate([
            'job_batches_rsp_id' => 'required|exists:job_batches_rsp,id',
        ]);

        $jobId = $validated['job_batches_rsp_id'];

        // Get ONLY Unqualified applicants
        $submissions = Submission::where('job_batches_rsp_id', $jobId)
            ->with('nPersonalInfo')
            ->where('status', 'Unqualified')
            ->get();

        if ($submissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Unqualified applicants found for this job post.'
            ], 404);
        }

        // Get job details
        $job = \App\Models\JobBatchesRsp::with('criteria:id,job_batches_rsp_id,Education,Eligibility,Training,Experience')
            ->find($jobId);

        $position = $job->Position ?? 'the applied position';
        $office = $job->Office ?? 'the corresponding office';

        // QS of the job post
        $education_qs = $job->criteria->Education ?? 'N/A';
        $eligibility_qs = $job->criteria->Eligibility ?? 'N/A';
        $training_qs = $job->criteria->Training ?? 'N/A';
        $experience_qs = $job->criteria->Experience ?? 'N/A';

        $count = 0;

        foreach ($submissions as $submission) {
            $applicant = $submission->nPersonalInfo;

            // Check internal/external records
            $externalApplicant = DB::table('xPersonalAddt')
                ->join('xPersonal', 'xPersonalAddt.ControlNo', '=', 'xPersonal.ControlNo')
                ->where('xPersonalAddt.ControlNo', $submission->ControlNo)
                ->select('xPersonalAddt.*', 'xPersonal.Firstname', 'xPersonal.Surname', 'xPersonalAddt.EmailAdd', 'xPersonalAddt.Rstreet', 'xPersonalAddt.Rbarangay', 'xPersonalAddt.Rcity', 'xPersonalAddt.Rprovince')
                ->first();

            $activeApplicant = $applicant ?? $externalApplicant;

            if (!$activeApplicant) {
                Log::warning("⚠️ No applicant record found for submission ID: {$submission->id}");
                continue;
            }

            // Email
            $email = $applicant->email_address ?? $externalApplicant->EmailAdd ?? null;

            // Fullname
            $fullname = $applicant
                ? trim("{$applicant->firstname} {$applicant->lastname}")
                : trim("{$externalApplicant->Firstname} {$externalApplicant->Surname}");

            if (empty($email)) {
                Log::warning("⚠️ Applicant {$fullname} has no email address.");
                continue;
            }

            // ✅ FETCH ACTUAL QUALIFICATION RECORDS
            $educationRecords = $submission->getEducationRecords();
            $experienceRecords = $submission->getExperienceRecords();
            $trainingRecords = $submission->getTrainingRecords();
            $eligibilityRecords = $submission->getEligibilityRecords();

            // ✅ FORMAT QUALIFICATIONS FOR EMAIL
            $educationText = $this->formatEducationForEmail($educationRecords);
            $experienceText = $this->formatExperienceForEmail($experienceRecords);
            $trainingText = $this->formatTrainingForEmail($trainingRecords);
            $eligibilityText = $this->formatEligibilityForEmail($eligibilityRecords);

            $template = 'mail-template.unqualified';

            try {
                Mail::to($email)->queue(
                    new EmailApi(
                        "Application - Unqualified",
                        $template,
                        [
                            'fullname' => $fullname,
                            'lastname' => $applicant->lastname ?? $externalApplicant->Surname ?? 'N/A',
                            'street' => $applicant->residential_street ?? $externalApplicant->Rstreet ?? 'N/A',
                            'barangay' => $applicant->residential_barangay ?? $externalApplicant->Rbarangay ?? 'N/A',
                            'city' => $applicant->residential_city ?? $externalApplicant->Rcity ?? 'N/A',
                            'province' => $applicant->residential_province ?? $externalApplicant->Rprovince ?? 'N/A',
                            'position' => $position,
                            'office' => $office,

                            // ✅ FORMATTED QUALIFICATION TEXT (matching blade variable names)
                            'education_qualification' => $educationText,
                            'experience_qualification' => $experienceText,
                            'training_qualification' => $trainingText,
                            'eligibility_qualification' => $eligibilityText,

                            // Remarks
                            'education_remark' => $submission->education_remark ?? 'N/A',
                            'experience_remark' => $submission->experience_remark ?? 'N/A',
                            'training_remark' => $submission->training_remark ?? 'N/A',
                            'eligibility_remark' => $submission->eligibility_remark ?? 'N/A',

                            // QS of job post
                            'education_qs' => $education_qs,
                            'eligibility_qs' => $eligibility_qs,
                            'training_qs' => $training_qs,
                            'experience_qs' => $experience_qs,

                            'date' => now()->format('F d, Y'),
                        ]
                    )
                );

                Log::info("📧 Queued UNQUALIFIED email for {$fullname} ({$email}).");
                $count++;
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email for {$fullname}: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Unqualified email notifications sent to {$count} applicant(s)."
        ]);
    }

    // ✅ Helper method to format education
    private function formatEducationForEmail($educationRecords)
    {
        if ($educationRecords->isEmpty()) {
            return 'No education records found.';
        }

        $formatted = [];
        foreach ($educationRecords as $edu) {
            $degree = $edu->degree ?? 'N/A';
            $school = $edu->school_name ?? 'N/A';
            $year = $edu->year_graduated ?? 'N/A';
            $formatted[] = "• {$degree} at {$school} ({$year})";
        }

        return implode('<br>', $formatted);
    }

    // ✅ Helper method to format experience
    private function formatExperienceForEmail($experienceRecords)
    {
        if ($experienceRecords->isEmpty()) {
            return 'No work experience found.';
        }

        $formatted = [];
        foreach ($experienceRecords as $exp) {
            $position = $exp->position_title ?? 'N/A';
            $department = $exp->department ?? 'N/A';
            $dateFrom = $exp->work_date_from ?? 'N/A';
            $dateTo = $exp->work_date_to ?? 'N/A';
            $formatted[] = "• {$position} at {$department} ({$dateFrom} - {$dateTo})";
        }

        return implode('<br>', $formatted);
    }

    // ✅ Helper method to format training
    private function formatTrainingForEmail($trainingRecords)
    {
        if ($trainingRecords->isEmpty()) {
            return 'No training/seminar records found.';
        }

        $formatted = [];
        foreach ($trainingRecords as $training) {
            $title = $training->training_title ?? 'N/A';
            $hours = $training->number_of_hours ?? 'N/A';
            $formatted[] = "• {$title} ({$hours} hours)";
        }

        return implode('<br>', $formatted);
    }

    // ✅ Helper method to format eligibility
    private function formatEligibilityForEmail($eligibilityRecords)
    {
        if ($eligibilityRecords->isEmpty()) {
            return 'No eligibility records found.';
        }

        $formatted = [];
        foreach ($eligibilityRecords as $eligibility) {
            $name = $eligibility->eligibility ?? 'N/A';
            $rating = $eligibility->rating ? " - Rating: {$eligibility->rating}" : '';
            $formatted[] = "• {$name}{$rating}";
        }

        return implode('<br>', $formatted);
    }

}
