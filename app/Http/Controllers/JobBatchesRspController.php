<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use App\Models\rating_score;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\OnCriteriaJob;
use App\Models\OnFundedPlantilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\vwplantillastructure;
use Illuminate\Support\Facades\Storage;


class JobBatchesRspController extends Controller
{

    public function Unoccupied(Request $request, $JobPostingId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Unoccupied',
        ]);

        $jobPost = JobBatchesRsp::findOrFail($JobPostingId);
        $jobPost->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $jobPost,
        ]);
    }

    public function index() //  this function fetching the only didnit meet the end_date
    {
        // Only fetch jobs where end_post is today or later (still active)
        $today = Carbon::today();
        $activeJobs = JobBatchesRsp::whereDate('end_date', '>=', $today)
            ->orderBy('post_date', 'asc')
            ->whereNotIn('status', ['unoccupied', 'occupied', 'republished'])
            ->get();

        return response()->json($activeJobs);
    }


    public function job_post()
    {
        // 🔹 Fetch job posts EXCLUDING republished ones
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status','end_date', 'tblStructureDetails_ID')
            ->whereRaw('LOWER(status) != ?', ['republished']) // ✅ exclude republished
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
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->get();

        foreach ($jobPosts as $job) {
            $originalStatus = strtolower($job->status);
            $newStatus = $originalStatus;

            // Skip manual statuses (do not override)
            $manualStatuses = ['unoccupied', 'occupied', 'closed', 'republished'];
            if (in_array($originalStatus, $manualStatuses)) {
                continue;
            }

            // ✅ Check if all raters have completed their rating
            $allRatersComplete = \App\Models\Job_batches_user::where('job_batches_rsp_id', $job->id)
                ->exists() &&
                !\App\Models\Job_batches_user::where('job_batches_rsp_id', $job->id)
                    ->where('status', '!=', 'complete')
                    ->exists();

            if ($allRatersComplete) {
                $newStatus = 'rated';
            } elseif ($job->hired_count >= 1) {
                $newStatus = 'occupied';
            } elseif ($job->qualified_count > 0 || $job->unqualified_count > 0) {
                $newStatus = $job->pending_count > 0 ? 'pending' : 'assessed';
            } else {
                $newStatus = 'not started';
            }

            // ✅ Update only if changed
            if ($originalStatus !== $newStatus) {
                $job->status = $newStatus;
                $job->save();
            }
        }

        // 🔄 Reload updated list (still excluding republished)
        $jobPosts = JobBatchesRsp::select('id', 'Position', 'post_date', 'Office', 'PositionID', 'ItemNo', 'status' ,'end_date' ,'tblStructureDetails_ID')
            ->whereRaw('LOWER(status) != ?', ['republished']) // ✅ exclude republished again
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
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->get();

        return response()->json($jobPosts);
    }



    public function job_list()

    {
        // Get all job posts with criteria and assigned raters
        $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])->select('id','office','isOpen','Position', 'PositionID', 'ItemNo')
        ->where('status', '!=', 'occupied') //
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
    //     // Get all job posts with criteria, assigned raters, and status
    //     $jobs = JobBatchesRsp::with(['criteriaRatings', 'users:id,name'])
    //         ->select('id', 'office', 'isOpen', 'Position', 'PositionID', 'ItemNo', 'status') // include actual status
    //         ->where('status', '!=', 'occupied')
    //         ->get();

    //     // Add criteria status and assigned raters to each job
    //     $jobsWithDetails = $jobs->map(function ($job) {
    //         // Add a computed field for criteria presence
    //         $job->criteria_status = $job->criteriaRatings->isNotEmpty() ? 'created' : 'no criteria';

    //         // Include users as assigned raters
    //         $job->assigned_raters = $job->users;

    //         // Clean up relations if desired
    //         unset($job->users, $job->criteriaRatings);

    //         return $job;
    //     });

    //     return response()->json($jobsWithDetails);
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
            $trainingImages = [];
            $educationImages = [];
            $eligibilityImages = [];
            $experienceImages = [];

            if ($info && isset($info['id'])) {
                $baseFolder = storage_path('app/public/applicant_files/' . $submission->nPersonalInfo_id);

                $folders = [
                    'training' => $baseFolder . '/document/training',
                    'education' => $baseFolder . '/document/education',
                    'eligibility' => $baseFolder . '/document/eligibility',
                    'experience' => $baseFolder . '/document/experience',
                ];

                foreach ($folders as $type => $path) {
                    if (is_dir($path)) {
                        $files = collect(scandir($path))
                            ->filter(fn($file) => !in_array($file, ['.', '..']))
                            ->map(fn($file) => asset('storage/applicant_files/' . $info['id'] . '/document/' . $type . '/' . $file))
                            ->values()
                            ->toArray();

                        if ($type === 'training') $trainingImages = $files;
                        if ($type === 'education') $educationImages = $files;
                        if ($type === 'eligibility') $eligibilityImages = $files;
                        if ($type === 'experience') $experienceImages = $files;
                    }
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
                'training_images' => $trainingImages,
                'education_images' => $educationImages,
                'eligibility_images' => $eligibilityImages,
                'experience_images' => $experienceImages,
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



    // public function job_post_view($job_post_id)
    // {
    //     // ✅ Fetch job post with relations
    //     $job_post = JobBatchesRsp::with(['criteria', 'plantilla'])->findOrFail($job_post_id);

    //     // ✅ Get complete history (both previous and next reposts)
    //     $history = $this->getFullJobHistory($job_post);

    //     // ✅ Convert to array and clean up nested relations
    //     $job_post_array = $job_post->toArray();
    //     unset($job_post_array['previous_job'], $job_post_array['next_job']);

    //     // ✅ Return structured response
    //     return response()->json(array_merge($job_post_array, [
    //         'history' => $history
    //     ]));
    // }
    public function job_post_view($job_post_id)
    {
        // ✅ Fetch job post with relations
        $job_post = JobBatchesRsp::with(['criteria', 'plantilla'])
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
                    $query->whereRaw('LOWER(status) = ?', ['hired']);
                },
            ])
            ->findOrFail($job_post_id);

        // ✅ Check if all raters completed their rating
        $allRatersComplete = \App\Models\Job_batches_user::where('job_batches_rsp_id', $job_post->id)
            ->exists() &&
            !\App\Models\Job_batches_user::where('job_batches_rsp_id', $job_post->id)
                ->where('status', '!=', 'complete')
                ->exists();

        $originalStatus = strtolower($job_post->status);
        $newStatus = $originalStatus;

        // ✅ Skip manual statuses
        $manualStatuses = ['unoccupied', 'occupied', 'closed', 'republished'];
        if (!in_array($originalStatus, $manualStatuses)) {
            if ($allRatersComplete) {
                $newStatus = 'rated';
            } elseif ($job_post->hired_count >= 1) {
                $newStatus = 'occupied';
            } elseif ($job_post->qualified_count > 0 || $job_post->unqualified_count > 0) {
                // ✅ If there’s at least one qualified or unqualified applicant
                $newStatus = $job_post->pending_count > 0 ? 'pending' : 'assessed';
            } else {
                $newStatus = 'not started';
            }

            // ✅ Update only if status changed
            if ($originalStatus !== $newStatus) {
                $job_post->status = $newStatus;
                $job_post->save();
            }
        }

        // ✅ Get complete history (both previous and next reposts)
        $history = $this->getFullJobHistory($job_post);

        // ✅ Convert to array and clean up nested relations
        $job_post_array = $job_post->toArray();
        unset($job_post_array['previous_job'], $job_post_array['next_job']);

        // ✅ Return structured response
        return response()->json(array_merge($job_post_array, [
            'history' => $history,
        ]));
    }


    private function getFullJobHistory($job)
    {
        $history = [];

        // 1️⃣ Go backwards (older reposts)
        $current = $job;
        while ($current->previousJob) {
            $current = $current->previousJob;
        }

        // 2️⃣ From the oldest, go forward (to latest reposts)
        while ($current) {
            $history[] = [
                'id' => $current->id,
                'post_date' => $current->post_date,
                'end_date' => $current->end_date,
            ];

            $current = $current->nextJob ?? null; // move forward
        }

        return $history; // always ordered oldest → latest
    }


    public function store(Request $request)
    {
        // Validate basic fields for job batch
        $jobValidated = $request->validate([
            'Office' => 'nullable|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'required|nullable|date',
            'end_date' => 'required|nullable|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string',
            'tblStructureDetails_ID' => 'nullable|string',
        ]);

        // Validate criteria if present
        $criteriaValidated = $request->only(['Education', 'Eligibility', 'Training', 'Experience']);

        // Validate file if present
        if ($request->hasFile('fileUpload')) {
            $fileValidated = $request->validate([
                'fileUpload' => 'required|mimes:pdf|max:5120',
            ]);
        }

        // --- Step 1: Update PageNo if PageNo exists ---
        if ($request->has('PageNo') && $jobValidated['tblStructureDetails_ID'] && $jobValidated['ItemNo']) {
            $exists = DB::table('tblStructureDetails')
                ->where('PageNo', $jobValidated['PageNo'])
                ->where('ItemNo', $jobValidated['ItemNo'])
                ->where('ID', '<>', $jobValidated['tblStructureDetails_ID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate PageNo and ItemNo already exists in plantilla.'
                ], 422);
            }

            DB::table('tblStructureDetails')
                // ->where('PositionID', $jobValidated['PositionID'])
                ->where('ID', $jobValidated['tblStructureDetails_ID'])
                // ->where('ItemNo', $jobValidated['ItemNo'])
                ->update(['PageNo' => $jobValidated['PageNo']]);
        }

        // --- Step2: Create Job Post if new ---
        $jobBatch = JobBatchesRsp::create($jobValidated);

        // Create criteria if exists
        if (!empty($criteriaValidated)) {
            $criteria = OnCriteriaJob::create([
                'job_batches_rsp_id' => $jobBatch->id,
                'PositionID' => $jobValidated['PositionID'] ?? null,
                'ItemNo' => $jobValidated['ItemNo'] ?? null,
                'Education' => $criteriaValidated['Education'] ?? null,
                'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
                'Training' => $criteriaValidated['Training'] ?? null,
                'Experience' => $criteriaValidated['Experience'] ?? null,
            ]);
        }

        // Handle plantilla and file upload
        $plantilla = new OnFundedPlantilla();
        $plantilla->job_batches_rsp_id = $jobBatch->id;
        $plantilla->PositionID = $jobValidated['PositionID'] ?? null;
        $plantilla->ItemNo = $jobValidated['ItemNo'] ?? null;

        if ($request->hasFile('fileUpload')) {
            $file = $request->file('fileUpload');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Job post processed successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria ?? null,
            'plantilla' => $plantilla
        ], 201);
    }

    public function job_post_update(Request $request, $jobBatchId)
    {
        // 1️⃣ Validate job batch fields
        $jobValidated = $request->validate([

            'post_date' => 'required|nullable|date',
            'end_date' => 'required|nullable|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'PositionID' => 'required|string',
            'tblStructureDetails_ID' => 'required|string',
        ]);

        // 2️⃣ Validate criteria if present
        $criteriaValidated = $request->only(['Education', 'Eligibility', 'Training', 'Experience']);

        // 3️⃣ Validate file if present
        if ($request->hasFile('fileUpload')) {
            $fileValidated = $request->validate([
                'fileUpload' => 'required|mimes:pdf|max:5120',
            ]);
        }

        // 4️⃣ Check for duplicate PageNo + ItemNo
        if ($request->has('PageNo') && $jobValidated['tblStructureDetails_ID'] && $jobValidated['ItemNo']) {
            $exists = DB::table('tblStructureDetails')
                ->where('PageNo', $jobValidated['PageNo'])
                ->where('ItemNo', $jobValidated['ItemNo'])
                ->where('ID', '<>', $jobValidated['tblStructureDetails_ID'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate PageNo and ItemNo already exists in plantilla Please try again.'
                ], 422);
            }

            // Update PageNo in tblStructureDetails
            DB::table('tblStructureDetails')
                ->where('ID', $jobValidated['tblStructureDetails_ID'])
                // ->where('ItemNo', $jobValidated['ItemNo'])
                ->update(['PageNo' => $jobValidated['PageNo']]);
        }

        // 5️⃣ Update Job Batch
        $jobBatch = JobBatchesRsp::findOrFail($jobBatchId);
        $jobBatch->update($jobValidated);

        // 6️⃣ Update criteria if exists
        if (!empty($criteriaValidated)) {
            $criteria = OnCriteriaJob::updateOrCreate(
                ['job_batches_rsp_id' => $jobBatch->id],
                [
                    'PositionID' => $jobValidated['PositionID'] ?? null,
                    'ItemNo' => $jobValidated['ItemNo'] ?? null,
                    'Education' => $criteriaValidated['Education'] ?? null,
                    'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
                    'Training' => $criteriaValidated['Training'] ?? null,
                    'Experience' => $criteriaValidated['Experience'] ?? null,
                ]
            );
        }

        // 7️⃣ Update plantilla and file if exists
        $plantilla = OnFundedPlantilla::firstOrNew(['job_batches_rsp_id' => $jobBatch->id]);
        $plantilla->PositionID = $jobValidated['PositionID'] ?? null;
        $plantilla->ItemNo = $jobValidated['ItemNo'] ?? null;

        if ($request->hasFile('fileUpload')) {
            $file = $request->file('fileUpload');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
            $plantilla->fileUpload = $filePath;
        }

        $plantilla->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Job post updated successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria ?? null,
            'plantilla' => $plantilla
        ], 200);
    }

    // public function republished(Request $request)
    // {
    //     // ✅ Step 1: Validate Job Batch fields
    //     $jobValidated = $request->validate([
    //         'Office' => 'required|string',
    //         'Office2' => 'nullable|string',
    //         'Group' => 'nullable|string',
    //         'Division' => 'nullable|string',
    //         'Section' => 'nullable|string',
    //         'Unit' => 'nullable|string',
    //         'Position' => 'required|string',
    //         'PositionID' => 'nullable|integer',
    //         'isOpen' => 'boolean',
    //         'post_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'PageNo' => 'required|string',
    //         'ItemNo' => 'required|string',
    //         'SalaryGrade' => 'nullable|string',
    //         'salaryMin' => 'nullable|string',
    //         'salaryMax' => 'nullable|string',
    //         'level' => 'nullable|string',
    //         'tblStructureDetails_ID' => 'required|string',
    //         'old_job_id' => 'required|integer',
    //     ]);

    //     // ✅ Step 2: Require criteria fields
    //     $criteriaValidated = $request->validate([
    //         'Education' => 'nullable|string',
    //         'Eligibility' => 'nullable|string',
    //         'Training' => 'nullable|string',
    //         'Experience' => 'nullable|string',
    //     ]);

    //     // ✅ Step 3: Mark old job as Republished
    //     JobBatchesRsp::where('id', $jobValidated['old_job_id'])
    //         ->update(['status' => 'Republished']);

    //     // ✅ Step 4: Validate file if uploaded
    //     if ($request->hasFile('fileUpload')) {
    //         $request->validate([
    //             'fileUpload' => 'required|mimes:pdf|max:5120',
    //         ]);
    //     }

    //     // ✅ Step 5: Create new Job Post
    //     $jobBatch = JobBatchesRsp::create($jobValidated);

    //     // ✅ Step 6: Create new Criteria (required)
    //     $criteria = OnCriteriaJob::create([
    //         'job_batches_rsp_id' => $jobBatch->id,
    //         'PositionID' => $jobValidated['PositionID'] ?? null,
    //         'ItemNo' => $jobValidated['ItemNo'] ?? null,
    //         'Education' => $criteriaValidated['Education'] ?? null,
    //         'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
    //         'Training' => $criteriaValidated['Training'] ?? null,
    //         'Experience' => $criteriaValidated['Experience'] ?? null,
    //     ]);
    //     $plantillaValidated = $request->validate([
    //         'fileUpload' => 'required|mimes:pdf|max:5120', // 👈 file required here

    //     ]);

    //     // ✅ Step 7: Handle plantilla and file upload
    //     $plantilla = new OnFundedPlantilla();
    //     $plantilla->job_batches_rsp_id = $jobBatch->id;
    //     $plantilla->PositionID = $jobValidated['PositionID'] ?? null;
    //     $plantilla->ItemNo = $jobValidated['ItemNo'] ?? null;

    //     if ($request->hasFile('fileUpload')) {
    //         $file = $request->file('fileUpload');
    //         $fileName = time() . '_' . $file->getClientOriginalName();
    //         $filePath = $file->storeAs('plantilla_files', $fileName, 'public');
    //         $plantilla->fileUpload = $filePath;
    //     }

    //     $plantilla->save();

    //     // ✅ Step 8: Response
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Job post republished successfully',
    //         'job_post' => $jobBatch,
    //         'criteria' => $criteria,
    //         'plantilla' => $plantilla
    //     ], 201);
    // }
    public function republished(Request $request)
    {
        // ✅ Step 1: Validate Job Batch fields
        $jobValidated = $request->validate([
            'Office' => 'required|string',
            'Office2' => 'nullable|string',
            'Group' => 'nullable|string',
            'Division' => 'nullable|string',
            'Section' => 'nullable|string',
            'Unit' => 'nullable|string',
            'Position' => 'required|string',
            'PositionID' => 'nullable|integer',
            'isOpen' => 'boolean',
            'post_date' => 'required|date',
            'end_date' => 'required|date',
            'PageNo' => 'required|string',
            'ItemNo' => 'required|string',
            'SalaryGrade' => 'nullable|string',
            'salaryMin' => 'nullable|string',
            'salaryMax' => 'nullable|string',
            'level' => 'nullable|string',
            'tblStructureDetails_ID' => 'required|string',
            'old_job_id' => 'required|integer',
        ]);

        // ✅ Step 2: Validate criteria fields
        $criteriaValidated = $request->validate([
            'Education' => 'nullable|string',
            'Eligibility' => 'nullable|string',
            'Training' => 'nullable|string',
            'Experience' => 'nullable|string',
        ]);

        // ✅ Step 3: Validate file (required even if not in JobBatchesRsp)
        $fileValidated = $request->validate([
            'fileUpload' => 'required|mimes:pdf|max:5120',
        ]);

        // ✅ Step 4: Mark old job as Republished
        JobBatchesRsp::where('id', $jobValidated['old_job_id'])
            ->update(['status' => 'Republished']);

        // ✅ Step 5: Create new Job Post
        $jobBatch = JobBatchesRsp::create($jobValidated);

        // ✅ Step 6: Create new Criteria
        $criteria = OnCriteriaJob::create([
            'job_batches_rsp_id' => $jobBatch->id,
            'PositionID' => $jobValidated['PositionID'] ?? null,
            'ItemNo' => $jobValidated['ItemNo'] ?? null,
            'Education' => $criteriaValidated['Education'] ?? null,
            'Eligibility' => $criteriaValidated['Eligibility'] ?? null,
            'Training' => $criteriaValidated['Training'] ?? null,
            'Experience' => $criteriaValidated['Experience'] ?? null,
        ]);

        // ✅ Step 7: Handle plantilla and file upload
        $file = $request->file('fileUpload');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('plantilla_files', $fileName, 'public');

        $plantilla = OnFundedPlantilla::create([
            'job_batches_rsp_id' => $jobBatch->id,
            'PositionID' => $jobValidated['PositionID'] ?? null,
            'ItemNo' => $jobValidated['ItemNo'] ?? null,
            'fileUpload' => $filePath,
        ]);

        // ✅ Step 8: Return response
        return response()->json([
            'status' => 'success',
            'message' => 'Job post republished successfully',
            'job_post' => $jobBatch,
            'criteria' => $criteria,
            'plantilla' => $plantilla
        ], 201);
    }
}

