<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class PdsImport implements ToCollection
{
    protected $controlNo;

    public function __construct()
    {
        // Generate a unique control number for this import
        $this->controlNo = 'PDS-' . Str::random(8) . '-' . time();
    }

    public function collection(Collection $rows)
    {
        // Process Personal Information (Sheet 1, starting around row 2-25)
        $personalInfo = $this->extractPersonalInfo($rows);
        if ($personalInfo) {
            DB::table('nPersonalInfo')->insert($personalInfo);
        }

        // Process Family Information (Sheet 1, starting around row 26-35)
        $familyInfo = $this->extractFamilyInfo($rows);
        if ($familyInfo) {
            DB::table('nFamily')->insert($familyInfo);
        }

        // Process Children (Sheet 1, may start around row 36)
        $children = $this->extractChildren($rows);
        if (count($children) > 0) {
            DB::table('nChildren')->insert($children);
        }

        // Process Education (Sheet 1, rows 45-55 approximately)
        $education = $this->extractEducation($rows);
        if (count($education) > 0) {
            DB::table('nEducation')->insert($education);
        }

        // Process Civil Service (Sheet 2, rows 5-15 approximately)
        $civilService = $this->extractCivilService($rows);
        if (count($civilService) > 0) {
            DB::table('nCivilService')->insert($civilService);
        }

        // Process Work Experience (Sheet 2, rows 20-40 approximately)
        $workExperience = $this->extractWorkExperience($rows);
        if (count($workExperience) > 0) {
            DB::table('nWorkExperience')->insert($workExperience);
        }

        // Process Voluntary Work (Sheet 3, rows 5-15 approximately)
        $voluntaryWork = $this->extractVoluntaryWork($rows);
        if (count($voluntaryWork) > 0) {
            DB::table('nVoluntaryWork')->insert($voluntaryWork);
        }

        // Process Learning and Development (Sheet 3, rows 20-40 approximately)
        $trainings = $this->extractTrainings($rows);
        if (count($trainings) > 0) {
            DB::table('nTrainings')->insert($trainings);
        }

        // Process Other Information (Sheet 3, rows 45-55 approximately)
        $skills = $this->extractSkills($rows);
        if (count($skills) > 0) {
            DB::table('nSkills')->insert($skills);
        }

        $distinctions = $this->extractDistinctions($rows);
        if (count($distinctions) > 0) {
            DB::table('nNonAcademicDistinction')->insert($distinctions);
        }

        $organizations = $this->extractOrganizations($rows);
        if (count($organizations) > 0) {
            DB::table('nMemberOrganization')->insert($organizations);
        }
    }

    // Helper functions to extract specific sections from the Excel file

    private function extractPersonalInfo($rows)
    {
        // These row indexes will need to be adjusted based on your actual Excel structure
        $surname = $rows[2][1] ?? null;
        $firstname = $rows[3][1] ?? null;
        $middlename = $rows[4][1] ?? null;
        $extension = $rows[3][4] ?? null;

        // Skip if essential data is missing
        if (!$surname || !$firstname) {
            return null;
        }

        return [
            'control_no' => $this->controlNo,
            'lastname' => $surname,
            'firstname' => $firstname,
            'middlename' => $middlename,
            'name_extension' => $extension,
            'sex' => $rows[5][1] ?? 'Unknown',
            'civil_status' => $rows[6][1] ?? 'Single',
            'date_of_birth' => $this->parseDate($rows[3][0] ?? null),
            'place_of_birth' => $rows[4][0] ?? null,
            'height' => $this->parseDecimal($rows[7][0] ?? 0),
            'weight' => $this->parseDecimal($rows[8][0] ?? 0),
            'blood_type' => $rows[9][0] ?? null,
            'gsis_no' => $rows[10][0] ?? null,
            'pagibig_no' => $rows[11][0] ?? null,
            'philhealth_no' => $rows[12][0] ?? null,
            'sss_no' => $rows[13][0] ?? null,
            'tin_no' => $rows[14][0] ?? null,
            'citizenship' => $rows[16][0] ?? 'Filipino',
            'citizenship_status' => $rows[16][1] ?? null,

            'residential_house' => $rows[17][1] ?? null,
            'residential_street' => $rows[17][3] ?? null,
            'residential_subdivision' => $rows[18][1] ?? null,
            'residential_barangay' => $rows[18][3] ?? null,
            'residential_city' => $rows[19][1] ?? null,
            'residential_province' => $rows[19][3] ?? null,
            'residential_zip' => $rows[20][0] ?? null,

            'permanent_house' => $rows[18][1] ?? null,
            'permanent_street' => $rows[18][3] ?? null,
            'permanent_subdivision' => $rows[19][1] ?? null,
            'permanent_barangay' => $rows[19][3] ?? null,
            'permanent_city' => $rows[20][1] ?? null,
            'permanent_province' => $rows[20][3] ?? null,
            'permanent_zip' => $rows[21][0] ?? null,

            'telephone_number' => $rows[19][0] ?? null,
            'cellphone_number' => $rows[20][0] ?? null,
            'email_address' => $rows[21][0] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function extractFamilyInfo($rows)
    {
        return [
            'control_no' => $this->controlNo,
            'spouse_name' => $rows[22][1] ?? null,
            'spouse_firstname' => $rows[23][0] ?? null,
            'spouse_middlename' => $rows[23][1] ?? null,
            'spouse_extension' => $rows[23][3] ?? null,
            'spouse_occupation' => $rows[24][0] ?? null,
            'spouse_employer' => $rows[25][0] ?? null,
            'spouse_employer_address' => $rows[26][0] ?? null,
            'spouse_employer_telephone' => $rows[27][0] ?? null,

            'father_name' => $rows[28][0] ?? null,
            'father_firstname' => $rows[29][0] ?? null,
            'father_middlename' => $rows[29][1] ?? null,
            'father_extension' => $rows[29][3] ?? null,

            'mother_name' => $rows[30][1] ?? null,
            'mother_firstname' => $rows[31][0] ?? null,
            'mother_middlename' => $rows[31][1] ?? null,
            'mother_maidenname' => $rows[30][1] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function extractChildren($rows)
    {
        $children = [];

        // Find the section with children (might start around row 23)
        $startRow = 23; // Approximate starting row for children
        $endRow = 40;   // Approximate ending row (adjust as needed)

        for ($i = $startRow; $i < $endRow; $i++) {
            $childName = $rows[$i][2] ?? null;
            $birthDate = $rows[$i][3] ?? null;

            if ($childName && $birthDate) {
                $children[] = [
                    'control_no' => $this->controlNo,
                    'child_name' => $childName,
                    'birth_date' => $this->parseDate($birthDate),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $children;
    }

    private function extractEducation($rows)
    {
        $education = [];
        $levels = ['ELEMENTARY', 'SECONDARY', 'VOCATIONAL', 'COLLEGE', 'GRADUATE STUDIES'];

        // Find education section (approximately row 45-55)
        $startRow = 45;
        $endRow = 55;

        for ($i = $startRow; $i < $endRow; $i++) {
            $level = null;
            foreach ($levels as $educLevel) {
                if (strpos(strtoupper($rows[$i][0] ?? ''), $educLevel) !== false) {
                    $level = $educLevel;
                    break;
                }
            }

            if ($level) {
                $education[] = [
                    'control_no' => $this->controlNo,
                    'level' => $level,
                    'school_name' => $rows[$i][1] ?? null,
                    'degree' => $rows[$i][2] ?? null,
                    'attendance_from' => $this->parseYear($rows[$i][3] ?? null),
                    'attendance_to' => $this->parseYear($rows[$i][4] ?? null),
                    'highest_units' => $rows[$i][5] ?? null,
                    'year_graduated' => $rows[$i][6] ?? null,
                    'scholarship' => $rows[$i][7] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $education;
    }

    private function extractCivilService($rows)
    {
        $civilService = [];

        // Find Civil Service section (Sheet 2, approximately rows 5-15)
        $startRow = 5;
        $endRow = 15;

        for ($i = $startRow; $i < $endRow; $i++) {
            $eligibility = $rows[$i][0] ?? null;

            if ($eligibility && !empty(trim($eligibility))) {
                $civilService[] = [
                    'control_no' => $this->controlNo,
                    'eligibility' => $eligibility,
                    'rating' => $this->parseDecimal($rows[$i][1] ?? 0),
                    'date_of_examination' => $this->parseDate($rows[$i][2] ?? null),
                    'place_of_examination' => $rows[$i][3] ?? null,
                    'license_number' => $rows[$i][4] ?? null,
                    'date_of_validity' => $this->parseDate($rows[$i][5] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $civilService;
    }

    private function extractWorkExperience($rows)
    {
        $workExperience = [];

        // Find Work Experience section (Sheet 2, approximately rows 20-40)
        $startRow = 20;
        $endRow = 40;

        for ($i = $startRow; $i < $endRow; $i++) {
            $dateFrom = $rows[$i][0] ?? null;
            $dateTo = $rows[$i][1] ?? null;
            $position = $rows[$i][2] ?? null;

            if ($dateFrom && $position && !empty(trim($position))) {
                $workExperience[] = [
                    'control_no' => $this->controlNo,
                    'work_date_from' => $this->parseDate($dateFrom),
                    'work_date_to' => $this->parseDate($dateTo),
                    'position_title' => $position,
                    'department' => $rows[$i][3] ?? null,
                    'monthly_salary' => $this->parseDecimal($rows[$i][4] ?? 0),
                    'salary_grade' => $rows[$i][5] ?? null,
                    'status_of_appointment' => $rows[$i][6] ?? null,
                    'government_service' => $rows[$i][7] ?? 'no',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $workExperience;
    }

    private function extractVoluntaryWork($rows)
    {
        $voluntaryWork = [];

        // Find Voluntary Work section (Sheet 3, approximately rows 5-15)
        $startRow = 5;
        $endRow = 15;

        for ($i = $startRow; $i < $endRow; $i++) {
            $organization = $rows[$i][0] ?? null;

            if ($organization && !empty(trim($organization))) {
                $voluntaryWork[] = [
                    'control_no' => $this->controlNo,
                    'organization_name' => $organization,
                    'inclusive_date_from' => $this->parseDate($rows[$i][1] ?? null),
                    'inclusive_date_to' => $this->parseDate($rows[$i][2] ?? null),
                    'number_of_hours' => intval($rows[$i][3] ?? 0),
                    'position' => $rows[$i][4] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $voluntaryWork;
    }

    private function extractTrainings($rows)
    {
        $trainings = [];

        // Find L&D section (Sheet 3, approximately rows 20-40)
        $startRow = 20;
        $endRow = 40;

        for ($i = $startRow; $i < $endRow; $i++) {
            $title = $rows[$i][0] ?? null;

            if ($title && !empty(trim($title))) {
                $trainings[] = [
                    'control_no' => $this->controlNo,
                    'training_title' => $title,
                    'inclusive_date_from' => $this->parseDate($rows[$i][1] ?? null),
                    'inclusive_date_to' => $this->parseDate($rows[$i][2] ?? null),
                    'number_of_hours' => intval($rows[$i][3] ?? 0),
                    'type' => $rows[$i][4] ?? null,
                    'conducted_by' => $rows[$i][5] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $trainings;
    }

    private function extractSkills($rows)
    {
        $skills = [];

        // Find Skills section (Sheet 3, row ~45)
        $skillsRow = 45; // Approximate row for skills
        $skillString = $rows[$skillsRow][0] ?? '';

        if ($skillString) {
            $skillArray = explode(',', $skillString);
            foreach ($skillArray as $skill) {
                $skill = trim($skill);
                if (!empty($skill)) {
                    $skills[] = [
                        'control_no' => $this->controlNo,
                        'skill' => $skill,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $skills;
    }

    private function extractDistinctions($rows)
    {
        $distinctions = [];

        // Find Distinctions section (Sheet 3, row ~47)
        $distinctionsRow = 47; // Approximate row for distinctions

        // Could be multiple columns or a comma-separated list
        $distinctionString = $rows[$distinctionsRow][0] ?? '';
        if ($distinctionString) {
            $distinctionArray = explode(',', $distinctionString);
            foreach ($distinctionArray as $distinction) {
                $distinction = trim($distinction);
                if (!empty($distinction)) {
                    $distinctions[] = [
                        'control_no' => $this->controlNo,
                        'distinction' => $distinction,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $distinctions;
    }

    private function extractOrganizations($rows)
    {
        $organizations = [];

        // Find Organizations section (Sheet 3, row ~49)
        $organizationsRow = 49; // Approximate row for organizations

        // Could be multiple columns or a comma-separated list
        $organizationString = $rows[$organizationsRow][0] ?? '';
        if ($organizationString) {
            $organizationArray = explode(',', $organizationString);
            foreach ($organizationArray as $organization) {
                $organization = trim($organization);
                if (!empty($organization)) {
                    $organizations[] = [
                        'control_no' => $this->controlNo,
                        'organization' => $organization,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $organizations;
    }

    private function parseDate($dateValue)
    {
        if (!$dateValue) return null;

        try {
            if (is_string($dateValue)) {
                // Try to parse the date string
                return Carbon::parse($dateValue)->toDateString();
            } elseif ($dateValue instanceof \DateTime) {
                // If it's already a DateTime object
                return Carbon::instance($dateValue)->toDateString();
            } elseif (is_numeric($dateValue)) {
                // If it's an Excel date serial number
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->toDateString();
            }
        } catch (\Exception $e) {
            // If parsing fails, return null
            return null;
        }

        return null;
    }

    private function parseYear($yearValue)
    {
        if (!$yearValue) return null;

        try {
            if (is_numeric($yearValue) && $yearValue >= 1900 && $yearValue <= 2100) {
                // If it's a valid year number
                return Carbon::createFromDate($yearValue, 1, 1)->toDateString();
            } else {
                // Try to extract a year from a string or date
                return $this->parseDate($yearValue);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDecimal($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }

        if (is_string($value)) {
            // Remove any non-numeric characters except period
            $value = preg_replace('/[^0-9.]/', '', $value);
            return !empty($value) ? floatval($value) : 0;
        }

        return 0;
    }
}
