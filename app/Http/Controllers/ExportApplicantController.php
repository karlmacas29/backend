<?php

namespace App\Http\Controllers;

use App\Models\JobBatchesRsp;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportApplicantController extends Controller
{
    /**
     * Fetch all applicants (internal + external) from the full job post history
     */
    public function history_applicant_all($job_post_id)
    {
        // Step 1: Fetch the base job post
        $job_post = JobBatchesRsp::with(['previousJob', 'nextJob'])->findOrFail($job_post_id);

        //  Step 2: Get full history (oldest → latest)
        $history = $this->getFullJobHistory($job_post);
        $job_ids = collect($history)->pluck('id');

        // Step 3: Fetch submissions for all job IDs in history
        $submissions = Submission::select('id', 'nPersonalInfo_id', 'ControlNo', 'job_batches_rsp_id', 'status')
            ->whereIn('job_batches_rsp_id', $job_ids)
            ->with([
                'nPersonalInfo:id,firstname,lastname', // internal applicants
                'xPersonal:ControlNo,Firstname,Surname', // external applicants
            ])
            ->get();

        // Step 4: Get applicants already stored for current job
        $existing = Submission::where('job_batches_rsp_id', $job_post_id)
            ->get(['nPersonalInfo_id', 'ControlNo']);

        $existingInternal = $existing->pluck('nPersonalInfo_id')->filter()->toArray();
        $existingExternal = $existing->pluck('ControlNo')->filter()->toArray();

        // Step 5: Combine internal and external applicants, excluding existing
        $applicants = $submissions->map(function ($item) use ($existingInternal, $existingExternal) {
            if ($item->nPersonalInfo_id && $item->nPersonalInfo && !in_array($item->nPersonalInfo_id, $existingInternal)) {
                // Internal applicant
                return [
                    'nPersonalInfo_id' => $item->nPersonalInfo_id,
                    'type' => 'internal',
                    'firstname' => $item->nPersonalInfo->firstname,
                    'lastname' => $item->nPersonalInfo->lastname,
                    'status' => $item->status,
                ];
            } elseif ($item->ControlNo && $item->xPersonal && !in_array($item->ControlNo, $existingExternal)) {
                // External applicant
                return [
                    'ControlNo' => $item->ControlNo,
                    'type' => 'external',
                    'firstname' => $item->xPersonal->Firstname,
                    'lastname' => $item->xPersonal->Surname,
                    'status' => $item->status,
                ];
            }
            return null;
        })->filter()->values();

        return response()->json($applicants);
    }


    /**
     * Helper: get full repost chain (oldest → latest)
     */
    private function getFullJobHistory($job)
    {
        $history = [];

        // Move to oldest
        $current = $job;
        while ($current->previousJob) {
            $current = $current->previousJob;
        }

        // Collect all including latest
        while ($current) {
            $history[] = $current;
            $current = $current->nextJob ?? null;
        }

        return $history;
    }



    public function storeMultiple(Request $request)
    {
        /**
         * job_batches_rsp_id => Job Post ID
         * applicants => array that can contain either:
         *    { "id": <nPersonalInfo_id> } OR { "ControlNo": <ControlNo> }
         */

        $validated = $request->validate([
            'job_batches_rsp_id' => 'required|integer|exists:job_batches_rsp,id',
            'applicants' => 'required|array|min:1',
            'applicants.*.id' => 'nullable|exists:nPersonalInfo,id',
            'applicants.*.ControlNo' => 'nullable|exists:xPersonal,ControlNo',
        ]);

        $jobPostId = $validated['job_batches_rsp_id'];

        // ✅ Build insert data
        $insertData = collect($validated['applicants'])->map(function ($applicant) use ($jobPostId) {
            return [
                'job_batches_rsp_id' => $jobPostId,
                'nPersonalInfo_id' => $applicant['id'] ?? null,
                'ControlNo' => $applicant['ControlNo'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })
            // Filter out invalid entries (both id and ControlNo are null)
            ->filter(fn($item) => $item['nPersonalInfo_id'] || $item['ControlNo'])
            ->toArray();

        // ✅ Insert all applicants
        DB::table('submission')->insert($insertData);

        return response()->json([
            'message' => 'Applicants stored successfully!',
            'count' => count($insertData),
        ], 201);
    }
}
