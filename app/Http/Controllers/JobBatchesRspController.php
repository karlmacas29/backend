<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use App\Models\rating_score;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\OnCriteriaJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;


class JobBatchesRspController extends Controller
{
    public function index() //  this function fetching the only didnit meet the end_date
    {
        // Only fetch jobs where end_post is today or later (still active)
        $today = Carbon::today();
        $activeJobs = JobBatchesRsp::whereDate('end_date', '>=', $today)
            ->orderBy('post_date', 'asc') // Optional: you can change this to 'created_at' if preferred
            ->get();

        return response()->json($activeJobs);
    }

    // public function job_post()
    // {
    //     $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date','Office', 'PositionID','ItemNo','status')
    //         ->withCount([
    //             'applicants as total_applicants',
    //             'applicants as qualified_count' => function ($query) {
    //                 $query->where('status', 'qualified');
    //             },
    //             'applicants as unqualified_count' => function ($query) {
    //                 $query->where('status', 'unqualified');
    //             },
    //             'applicants as pending_count' => function ($query) {
    //                 $query->where('status', 'pending');
    //             },
    //         ])
    //         ->get();

    //     return response()->json($jobPosts);
    // }


    public function job_post()
    {
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status')
            ->withCount([
                'submissions as total_applicants',
                'submissions as qualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['qualified']);
                },
                'submissions as unqualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['unqualified']);
                },
                'submissions as pending_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['pending']);
                },

            'submissions as hired_count' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['Hired']);
            },
            ])
            ->get();

        foreach ($jobPosts as $job) {
            $originalStatus = $job->status;
            
            if ($job->hired_count >= 1) {
                $newStatus = 'Occupied';
            } elseif ($job->qualified_count > 0 || $job->unqualified_count > 0) {
                // Some applicants already assessed
                $newStatus = $job->pending_count > 0 ? 'pending' : 'assessed';
            } else {
                // No assessments yet
                $newStatus = 'not started';
            }
            if ($originalStatus !== $newStatus) {
                $job->status = $newStatus;
                $job->save();
            }
        }

        // Reload with updated status + counts
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status')
            ->withCount([
                'submissions as total_applicants',
                'submissions as qualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['qualified']);
                },
                'submissions as unqualified_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['unqualified']);
                },
                'submissions as pending_count' => function ($query) {
                    $query->whereRaw('LOWER(status) = ?', ['pending']);
                },

            'submissions as hired_count' => function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['Hired']);
            },

            ])
            ->get();

        return response()->json($jobPosts);
    }



    // public function job_post()
    // {
    //     $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status')
    //         ->withCount([
    //             'submissions as total_applicants',
    //             'submissions as qualified_count' => function ($query) {
    //                 $query->whereRaw('LOWER(status) = ?', ['qualified']);
    //             },
    //             'submissions as unqualified_count' => function ($query) {
    //                 $query->whereRaw('LOWER(status) = ?', ['unqualified']);
    //             },
    //             'submissions as pending_count' => function ($query) {
    //                 $query->whereRaw('LOWER(status) = ?', ['pending']);
    //             },
    //         ])
    //         ->get();

    //     foreach ($jobPosts as $job) {
    //         $originalStatus = $job->status;

    //         if ($job->pending_count > 0) {
    //             // ✅ Pending has priority
    //             $newStatus = 'pending';
    //         } elseif ($job->qualified_count > 0 || $job->unqualified_count > 0) {
    //             // ✅ Some applicants already assessed
    //             $newStatus = 'assessed';
    //         } else {
    //             // ✅ No applicants at all
    //             $newStatus = 'not started';
    //         }

    //         if ($originalStatus !== $newStatus) {
    //             $job->update(['status' => $newStatus]);
    //         }
    //     }

    //     return response()->json($jobPosts);
    // }

    public function job_list()

    {
        // Get all job posts with criteria and assigned raters
        $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position', 'PositionID', 'ItemNo') // Include only user id and name
            ->get();

        // Add 'status' and 'assigned_raters' to each job
        $jobsWithDetails = $jobs->map(function ($job) {
            $job->status = $job->criteriaRatings->isNotEmpty() ? 'created' : 'no criteria';
            $job->assigned_raters = $job->users; // Include users as assigned raters
            unset($job->users); // Optionally remove the original 'users' relation if not needed directly
            return $job;
        });

        return response()->json($jobsWithDetails);
    }

    // public function job_list()
    // {
    //     // Get all job posts with criteria and assigned raters
    //     $jobs = JobBatchesRsp::with([
    //         'criteriaRatings:id,job_batches_rsp_id,status', // only fetch needed fields
    //         'users:id,name'
    //     ])
    //         ->select('id', 'office', 'isOpen', 'Position')
    //         ->get();

    //     $jobsWithDetails = $jobs->map(function ($job) {
    //         // Use the first criteria (if there), or set status to "no criteria"
    //         if ($job->criteriaRatings->isNotEmpty()) {
    //             // If you allow only one criteria per job, just use first
    //             $criteria = $job->criteriaRatings->first();
    //             $job->status = $criteria->status ?? 'created';
    //         } else {
    //             $job->status = 'no criteria';
    //         }
    //         $job->assigned_raters = $job->users; // keep assigned raters
    //         unset($job->users); // optional: remove users relation from output
    //         unset($job->criteriaRatings); // optional: hide raw criteriaRatings
    //         return $job;
    //     });

    //     return response()->json($jobsWithDetails);
    // }

    // public function job_list()
    // {
    //     // Get all job posts with criteria and assigned raters
    //     $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position') // Include only user id and name
    //         ->get();

    //     // Add 'status' and 'assigned_raters' to each job
    //     $jobsWithDetails = $jobs->map(function ($job) {
    //         $job->status = $job->criteriaRatings->isNotEmpty() ? 'created' : 'no criteria';
    //         $job->assigned_raters = $job->users; // Include users as assigned raters
    //         unset($job->users); // Optionally remove the original 'users' relation if not needed directly
    //         return $job;
    //     });

    //     return response()->json($jobsWithDetails);
    // }

    public function office()
    {
        // Only fetch jobs where end_post is today or later (still active)
       $data = JobBatchesRsp::select('Office','Position','SalaryGrade','ItemNo','id')->get();
       return response()->json($data);
    }


    // Create
    // Create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Office' => 'nullable|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'PageNo' => 'nullable|string',
            'ItemNo' => 'nullable|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string', // Changed to string
        ]);

        // No default for post_date or end_date; must be set explicitly if required
        $jobBatch = JobBatchesRsp::create($validated);

        return response()->json($jobBatch, 201);
    }

    // Read single by PositionID and ItemNo
    // public function show($PositionID, $ItemNo)
    // {
    //     // Ensure you have `use Illuminate\Support\Facades\DB;` at the top of your file.
    //     $jobBatches = \Illuminate\Support\Facades\DB::select('SELECT * FROM job_batches_rsp WHERE PositionID = ? AND ItemNo = ?', [$PositionID, $ItemNo]);

    //     if (empty($jobBatches)) {
    //         return response()->json(['error' => 'No matching record found'], 404);
    //     }

    //     // DB::select returns an array of objects, so we take the first one.
    //     return response()->json($jobBatches[0]);
    // }

    public function show($positionId, $itemNo): JsonResponse
    {
        $jobBatch = JobBatchesRsp::where('PositionID', $positionId)
            ->where('ItemNo', $itemNo)
            ->first();

        if (!$jobBatch) {
            return response()->json(['error' => 'No matching record found'], 404);
        }

        return response()->json($jobBatch);
    }

    // public function show($positionId, $itemNo): JsonResponse
    // {
    //     $jobBatch = JobBatchesRsp::where('PositionID', $positionId)
    //         ->where('ItemNo', $itemNo)
    //         ->first();

    //     if (!$jobBatch) {
    //         return response()->json(['error' => 'No matching job batch found'], 404);
    //     }

    //     $criteria = OnCriteriaJob::where('PositionID', $positionId)
    //         ->where('ItemNo', $itemNo)
    //         ->get(); // use `get()` if multiple criteria per job batch

    //     return response()->json([
    //         'job_batch' => $jobBatch,
    //         'criteria' => $criteria
    //     ]);
    // }

    // Update only post_date and end_date
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'post_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $jobPost = JobBatchesRsp::findOrFail($id);
        $jobPost->update([
            'post_date' => $validated['post_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json([
            'message' => 'Dates updated successfully.',
            'data' => $jobPost,
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $jobBatch = JobBatchesRsp::findOrFail($id);

    //     $validated = $request->validate([
    //         'Office' => 'nullable|string',
    //         'Office2' => 'nullable|string',
    //         'Group' => 'nullable|string',
    //         'Division' => 'nullable|string',
    //         'Section' => 'nullable|string',
    //         'Unit' => 'nullable|string',
    //         'Position' => 'sometimes|required|string',
    //         'PositionID' => 'nullable|integer',
    //         'isOpen' => 'boolean',
    //         'post_date' => 'nullable|date',
    //         'end_date' => 'nullable|date',
    //         'PageNo' => 'nullable|string',
    //         'ItemNo' => 'nullable|string',
    //         'SalaryGrade' => 'nullable|string',
    //         'salaryMin' => 'nullable|string',
    //         'salaryMax' => 'nullable|string',
    //         'level' => 'nullable|string', // Changed to string
    //     ]);

    //     $jobBatch->update($validated);

    //     return response()->json($jobBatch);
    // }

    // Delete
    public function destroy($id)
    {
        $jobBatch = JobBatchesRsp::findOrFail($id);
        $jobBatch->delete();

        return response()->json([
            'message' => 'deleted successfully',
            'jobBatch' => $jobBatch,
        ]);
    }

    public function get_applicant($id)
    {
        // All submissions for this job post
        $qualifiedApplicants = Submission::where('job_batches_rsp_id', $id)
            ->with([
                'nPersonalInfo.education',
                'nPersonalInfo.work_experience',
                'nPersonalInfo.training',
                'nPersonalInfo.eligibity',
                'nPersonalInfo.family',
                'nPersonalInfo.children',
                'nPersonalInfo.personal_declarations',
                'nPersonalInfo.skills',
                'nPersonalInfo.voluntary_work',
                'nPersonalInfo.reference',
            ])
            ->get();

        // Count all applicants for this job post
        $totalApplicants = $qualifiedApplicants->count();

        // Count applicants with qualified OR unqualified status
        $progressCount = $qualifiedApplicants->whereIn('status', ['qualified', 'unqualified'])->count();

        $applicants = $qualifiedApplicants->map(function ($submission) use ($id) {
            $info = $submission->nPersonalInfo;

            // ✅ If no nPersonalInfo_id, fetch from Employee DB (via controlno)
            if (!$info && $submission->ControlNo) {
                $xPDS = new \App\Http\Controllers\xPDSController();
                $employeeData = $xPDS->getPersonalDataSheet(new \Illuminate\Http\Request([
                    'controlno' => $submission->ControlNo
                ]));

                $employeeJson = $employeeData->getData(true); // decode JSON response
                $info = [
                    'controlno' => $submission->ControlNo,
                    'firstname' => $employeeJson['User'][0]['Firstname'] ?? '',
                    'lastname' => $employeeJson['User'][0]['Surname'] ?? '',
                    'middlename' => $employeeJson['User'][0]['MIddlename'] ?? '',

                    'name_extension' => $employeeJson['User'][0]['NameExtension'] ?? null,

                    'image_path' => $employeeJson['User'][0]['Pics'] ?? $employeeJson['User'][0]['image_path'] ?? null,

                    'date_of_birth' => $employeeJson['User'][0]['BirthDate'] ?? 'N/A',
                    'place_of_birth' => $employeeJson['User'][0]['BirthPlace'] ??  'N/A',
                    'sex' => $employeeJson['User'][0]['Sex'] ??  'N/A',
                    'civil_status' => $employeeJson['User'][0]['CivilStatus'] ?? 'N/A',
                    'height' => $employeeJson['User'][0]['Heights'] ??  'N/A',
                    'weight' => $employeeJson['User'][0]['Weights'] ??  'N/A',
                    'blood_type' => $employeeJson['User'][0]['BloodType'] ??  'N/A',
                    'telephone_number' => $employeeJson['User'][0]['TelNo'] ??  'N/A',
                    'email_address' => $employeeJson['User'][0]['EmailAdd'] ??  'N/A',
                    'cellphone_number' => $employeeJson['User'][0]['CellphoneNo'] ??  'N/A',
                    'tin_no' => $employeeJson['User'][0]['TINNo'] ?? 'N/A',
                    'gsis_no' => $employeeJson['User'][0]['GSISNo'] ??  'N/A',
                    'pagibig_no' => $employeeJson['User'][0]['PAGIBIGNo'] ?? 'N/A',
                    'sss_no' => $employeeJson['User'][0]['SSSNo'] ??  'N/A',
                    'philhealth_no' => $employeeJson['User'][0]['PHEALTHNo'] ??  'N/A',
                    'agency_employee_no' => $employeeJson['User'][0]['ControlNo'] ?? 'N/A',
                    'citizenship' => $employeeJson['User'][0]['Citizenship'] ?? 'N/A',
                    'religion' => $employeeJson['User'][0]['Religion'] ??  'N/A',
                    // 'controlno' => $employeeJson['User'][0]['ControlNo'] ??  'N/A',


                    // Residential address
                    'residential_house' => $employeeJson['User'][0]['Rhouse'] ?? null,
                    'residential_street' => $employeeJson['User'][0]['Rstreet'] ?? null,
                    'residential_subdivision' => $employeeJson['User'][0]['Rsubdivision']  ?? null,
                    'residential_barangay' => $employeeJson['User'][0]['Rbarangay']  ?? null,
                    'residential_city' => $employeeJson['User'][0]['Rcity'] ?? null,
                    'residential_province' => $employeeJson['User'][0]['Rprovince'] ?? null,
                    'residential_region' => $employeeJson['User'][0]['Rregion'] ?? null,
                    'residential_zip' => $employeeJson['User'][0]['Rzip']  ?? null,

                    // Permanent address
                    'permanent_region' => $employeeJson['User'][0]['Pregion'] ?? null,
                    'permanent_house' => $employeeJson['User'][0]['Phouse']  ?? null,
                    'permanent_street' => $employeeJson['User'][0]['Pstreet']  ?? null,
                    'permanent_subdivision' => $employeeJson['User'][0]['Psubdivision'] ?? null,
                    'permanent_barangay' => $employeeJson['User'][0]['Pbarangay']  ?? null,
                    'permanent_city' => $employeeJson['User'][0]['Pcity'] ?? null,
                    'permanent_province' => $employeeJson['User'][0]['Pprovince']  ?? null,
                    'permanent_zip' => $employeeJson['User'][0]['Pzip'] ?? null,


                    // 'nPersonalInfo' => $employeeJson['User'] ?? [],
                    'children' => collect($employeeJson['User'][0]['children'] ?? [])->map(function ($child) {
                        return [
                            'child_name' => $child['ChildName'] ?? $child['child_name'] ?? null,
                            'birth_date' => $child['BirthDate'] ?? $child['birth_date'] ?? null,
                        ];
                    })->toArray(),

                    'education' => $employeeJson['Education'] ?? [],
                    'eligibity' => $employeeJson['Eligibility'] ?? [],
                    'work_experience' => $employeeJson['Experience'] ?? [],
                    'training' => $employeeJson['Training'] ?? [],
                    'voluntary_work' => $employeeJson['Voluntary'] ?? [],
                    'Academic' => $employeeJson['Academic'] ?? [],
                    'Organization' => $employeeJson['Organization'] ?? [],
                    'skills' => $employeeJson['Skills'] ?? [],
                    'reference' => $employeeJson['Reference'] ?? [],
                    'personal_declarations' => [[

                    ]],
                    'family' => [[
                        'father_extension' => $employeeJson['User'][0]['FatherExtension'] ??   'N/A',
                        'father_firstname' => $employeeJson['User'][0]['FatherFirstname'] ??  'N/A',
                        'father_lastname' => $employeeJson['User'][0]['FatherName'] ??   'N/A',
                        'father_middlename' => $employeeJson['User'][0]['FatherMiddlename'] ?? 'N/A',


                        'mother_firstname' => $employeeJson['User'][0]['MotherFirstname'] ?? 'N/A',
                        'mother_lastname' => $employeeJson['User'][0]['MotherName'] ?? 'N/A',
                        'mother_maidenname' => $employeeJson['User'][0]['MotherMaidenname'] ?? 'N/A',
                        'mother_middlename' => $employeeJson['User'][0]['MotherMiddlename'] ?? 'N/A',


                        'spouse_employer' => $employeeJson['User'][0]['SpouseEmployer'] ?? 'N/A',
                        'spouse_employer_address' => $employeeJson['User'][0]['SpouseEmpAddress'] ?? 'N/A',
                        'spouse_employer_telephone' => $employeeJson['User'][0]['SpouseEmpTel'] ?? 'N/A',
                        'spouse_extension' => $employeeJson['User'][0]['SpouseExtension'] ?? 'N/A',
                        'spouse_firstname' => $employeeJson['User'][0]['SpouseFirstname'] ?? 'N/A',
                        'spouse_middlename' => $employeeJson['User'][0]['MaidenName'] ?? 'N/A',
                        'spouse_name' => $employeeJson['User'][0]['SpouseName'] ?? 'N/A',
                        'spouse_occupation' => $employeeJson['User'][0]['Occupation'] ?? 'N/A',
                    ]],

                    'image_path' => null,
                    'created_at' => null,
                ];
            }

            // 🔄 Standardize Education Data here
            $educationData = collect($info['education'] ?? [])->map(function ($edu) {
                if (isset($edu['Education'])) {
                    $dates = explode('-', $edu['DateAttend'] ?? '');
                    $from = isset($dates[0]) && trim($dates[0]) !== '' ? trim($dates[0]) : 'N/A';
                    $to   = isset($dates[1]) && trim($dates[1]) !== '' ? trim($dates[1]) : 'N/A';
                    return [
                        'level'           => !empty($edu['Education']) ? $edu['Education'] : 'N/A',
                        'school_name'     => !empty($edu['School']) ? $edu['School'] : 'N/A',
                        'degree'          => !empty($edu['Degree']) ? $edu['Degree'] : 'N/A',
                        'attendance_from' => $from,
                        'attendance_to'   => $to,
                        'year_graduated'  => $to,
                        'highest_units'   => !empty($edu['NumUnits']) ? $edu['NumUnits'] : 'N/A',
                        'scholarship'     => !empty($edu['Honors']) ? $edu['Honors'] : 'N/A',
                    ];
                }

                return [
                    'level'           => !empty($edu['level']) ? $edu['level'] : 'N/A',
                    'school_name'     => !empty($edu['school_name']) ? $edu['school_name'] : 'N/A',
                    'degree'          => !empty($edu['degree']) ? $edu['degree'] : 'N/A',
                    'highest_units'   => !empty($edu['highest_units']) ? $edu['highest_units'] : 'N/A',
                    'attendance_from' => !empty($edu['attendance_from']) ? $edu['attendance_from'] : 'N/A',
                    'attendance_to'   => !empty($edu['attendance_to']) ? $edu['attendance_to'] : 'N/A',
                    'year_graduated'  => !empty($edu['year_graduated']) ? $edu['year_graduated'] : 'N/A',
                    'scholarship'  => !empty($edu['scholarship']) ? $edu['scholarship'] : 'N/A',
                ];
            });

            // 🔄 Standardize Education Data here
            $eligibityData = collect($info['eligibity'] ?? [])->map(function ($eli) {
                return [
                    'eligibility'         => !empty($eli['CivilServe'] ?? $eli['eligibility'] ?? null)
                        ? ($eli['CivilServe'] ?? $eli['eligibility'])
                        : 'N/A',
                    'rating'              => !empty($eli['Rates'] ?? $eli['rating'] ?? null)
                        ? ($eli['Rates'] ?? $eli['rating'])
                        : 'N/A',
                    'date_of_examination' => !empty($eli['Dates'] ?? $eli['date_of_examination'] ?? null)
                        ? ($eli['Dates'] ?? $eli['date_of_examination'])
                        : 'N/A',
                    'place_of_examination' => !empty($eli['Place'] ?? $eli['place_of_examination'] ?? null)
                        ? ($eli['Place'] ?? $eli['place_of_examination'])
                        : 'N/A',
                    'license_number'      => !empty($eli['LNumber'] ?? $eli['license_number'] ?? null)
                        ? ($eli['LNumber'] ?? $eli['license_number'])
                        : 'N/A',
                    'date_of_validity'    => !empty($eli['LDate'] ?? $eli['date_of_validity'] ?? null)
                        ? ($eli['LDate'] ?? $eli['date_of_validity'])
                        : 'N/A',
                ];
            });

            $trainingData = collect($info['training'] ?? [])->map(function ($train) {
                return [
                    'training_title'         => $train['Training'] ?? $train['training_title'] ?? null,
                    'inclusive_date_from'              => $train['DateFrom'] ?? $train['inclusive_date_from'] ?? null,
                    'inclusive_date_to' => $train['DateTo'] ?? $train['inclusive_date_to'] ?? null,
                    'number_of_hours' => $train['NumHours'] ?? $train['number_of_hours'] ?? null,
                    'type'      => $train['LNumber'] ?? $train['type'] ?? null,
                    'conducted_by'    => $train['Conductor'] ?? $train['conducted_by'] ?? null,
                ];
            });

            $experienceData = collect($info['work_experience'] ?? [])->map(function ($exp) {
                return [
                    'work_date_from'         => $exp['WFrom'] ?? $exp['work_date_from'] ?? null,
                    'work_date_to'              => $exp['WTo'] ?? $exp['work_date_to'] ?? null,
                    'position_title' => $exp['WPosition'] ?? $exp['position_title'] ?? null,
                    'department' => $exp['WCompany'] ?? $exp['department'] ?? null,
                    'monthly_salary'      => $exp['WSalary'] ?? $exp['monthly_salary'] ?? null,
                    'salary_grade'      => $exp['WGrade'] ?? $exp['salary_grade'] ?? null,
                    'status_of_appointment'    => $exp['Status'] ?? $exp['status_of_appointment'] ?? null,
                    'government_service'    => $exp['WGov'] ?? $exp['government_service'] ?? null,

                ];
            });

            $voluntaryData = collect($info['voluntary_work'] ?? [])->map(function ($vol) {
                return [
                    'inclusive_date_from'         => $vol['DateFrom'] ?? $vol['inclusive_date_from'] ?? null,
                    'inclusive_date_to'              => $vol['DateTo'] ?? $vol['inclusive_date_to'] ?? null,
                    'number_of_hours' => $vol['NoHours'] ?? $vol['number_of_hours'] ?? null,
                    'organization_name' => $vol['OrgName'] ?? $vol['organization_name'] ?? null,
                    'position'      => $vol['OrgPosition'] ?? $vol['position'] ?? null,
                ];
            });

            $referenceData = collect($info['reference'] ?? [])->map(function ($ref) {
                return [
                    'address'         => $ref['Address'] ?? $ref['address'] ?? null,
                    'contact_number'              => $ref['TelNo'] ?? $ref['contact_number'] ?? null,
                    'full_name' => $ref['Names'] ?? $ref['full_name'] ?? null,
                ];
            });

            $skillData = collect([]);

            // 1. From skills
            $skillData = $skillData->merge(
                collect($info['skills'] ?? [])->map(function ($skill) use ($submission) {
                    return [
                        'id' => $skill['id'] ?? null,
                        'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                        'skill' => $skill['Skills'] ?? $skill['skill'] ?? null,
                        'non_academic' => $skill['NonAcademic'] ?? $skill['non_academic'] ?? 'NA',
                        'organization' => $skill['Organization'] ?? $skill['organization'] ?? 'NA',

                    ];
                })
            );

            // 2. From Academic (map into "non_academic")
            $skillData = $skillData->merge(
                collect($info['Academic'] ?? [])->map(function ($acad) use ($submission) {
                    return [
                        'id' => $acad['ID'] ?? null,
                        'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                        'skill' => null,
                        'non_academic' => $acad['Academic'] ?? $acad['non_academic'] ?? null,
                        'organization' => 'NA',

                    ];
                })
            );

            // 3. From Organization
            $skillData = $skillData->merge(
                collect($info['Organization'] ?? [])->map(function ($org) use ($submission) {
                    return [
                        'id' => $org['ID'] ?? null,
                        'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                        'skill' => null,
                        'non_academic' => 'NA',
                        'organization' => $org['Organization'] ?? $org['organization'] ?? null,

                    ];
                })
            );

            $childrenData = collect($info['children'] ?? [])->map(function ($children) {
                return [
                    'child_name'      => $children['ChildName'] ?? $children['child_name'] ?? null,
                    'birth_date'     => $children['BirthDate'] ?? $children['birth_date'] ?? null,

                ];
            });


            // Fetch ranking from rating_score
            $rating = rating_score::where('nPersonalInfo_id', $submission->nPersonalInfo_id)
                ->where('job_batches_rsp_id', $id)
                ->first();

            // Generate image URL
            $imageUrl = null;
            if ($info && isset($info['image_path']) && $info['image_path']) {
                if (Storage::disk('public')->exists($info['image_path'])) {
                    $baseUrl = config('app.url');
                    $imageUrl = $baseUrl . '/storage/' . $info['image_path'];
                }
            }

            return [
                'id' => $submission->id,
                'nPersonalInfo_id' => $submission->nPersonalInfo_id,
                'ControlNo' => $submission->ControlNo,
                'job_batches_rsp_id' => $submission->job_batches_rsp_id,
                'status' => $submission->status,
                'education_remark' => $submission->education_remark,
                'experience_remark' => $submission->experience_remark,
                'training_remark' => $submission->training_remark,
                'eligibility_remark' => $submission->eligibility_remark,
                'firstname' => $info['firstname'] ?? '',
                'lastname' => $info['lastname'] ?? '',
                'name_extension' => $info['name_extension'] ?? '',
                'image_path' => $info['image_path'] ?? null,
                'image_url' => $imageUrl,
                'application_date' => $info['application_date']
                    ?? ($info instanceof \App\Models\excel\nPersonal_info
                        ? optional($info->created_at)->toDateString()
                        : (!empty($info['created_at'])
                            ? \Carbon\Carbon::parse($info['created_at'])->toDateString()
                            : ($submission->created_at
                                ? $submission->created_at->toDateString()
                                : null))),
                'nPersonalInfo' => $info ?? [],
                'education' => $educationData,
                'work_experience' => $experienceData,
                'training' => $trainingData,
                // 'eligibity' => $info['eligibity'] ?? [],
                'eligibity' => $eligibityData,
                'family' => $info['family'] ?? [],
                'children' => $childrenData,
                'personal_declarations' => $info['personal_declarations'] ?? [],
                'reference' => $referenceData,
                'voluntary_work' => $voluntaryData,
                'ranking' => $rating->ranking ?? null,
                'skills' => $skillData,
                'Academic' => $info['Academic'] ?? [],
                'Organization' => $info['Organization'] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'progress' => $progressCount . '/' . $totalApplicants,
            'progress_count' => $progressCount,
            'total_applicants' => $totalApplicants,
            'applicants' => $applicants,
        ]);
    }

    public function count($jobpostId)
    {
        $count = Submission::where('job_batches_rsp_id', $jobpostId)->count();

        return response()->json($count);
    }
}
