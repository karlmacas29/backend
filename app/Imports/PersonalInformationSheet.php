<?php

namespace App\Imports;

use App\Models\nPersonal_info;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class PersonalInformationSheet implements WithEvents
{
    use RegistersEventListeners;

    protected $importer;
    protected $jobBatchId;

    public function __construct($importer, $jobBatchId )
    {

        $this->importer = $importer;
        $this->jobBatchId = $jobBatchId;
    }
    public $images = [];



    public static function afterSheet(AfterSheet $event)
    {


        $sheet = $event->sheet->getDelegate();
        $drawings = $sheet->getParent()->getActiveSheet()->getDrawingCollection();

        $imagePath = null;

        // Loop through drawings and find image at S3
        foreach ($drawings as $drawing) {
            /** @var Drawing $drawing */
            $coordinates = $drawing->getCoordinates(); // e.g., 'S3'
            if (strtoupper($coordinates) === 'S3') {
                $extension = $drawing->getExtension();
                $filename = uniqid('excel_image_') . '.' . $extension;
                $contents = file_get_contents($drawing->getPath());

                // Save to local/public or S3
                Storage::put("public/excel_images/{$filename}", $contents);
                $imagePath = "storage/excel_images/{$filename}";
                break;
            }
        }

        DB::beginTransaction();

        try {

            $sheet = $event->sheet->getDelegate();

            $date_of_birth = self::parseDate($sheet->getCell('D13')->getValue());

            $isMale = $sheet->getCell('D16')->getValue(); // linked to Male checkbox
            $isFemale = $sheet->getCell('E16')->getValue(); // linked to Female checkbox

            $filipino = $sheet->getCell('J13')->getValue(); // linked to checkbox
            $by_birth = $sheet->getCell('J14')->getValue(); // linked to checkbox
            $dual_citizenship = $sheet->getCell('L13')->getValue(); // linked to checkbox
            $by_naturalization = $sheet->getCell('l14')->getValue(); // linked to checkbox

            $single = $sheet->getCell('D17')->getValue(); // linked to checkbox
            $married = $sheet->getCell('E17')->getValue(); // linked to checkbox
            $separated = $sheet->getCell('E18')->getValue(); // linked to checkbox
            $widowed = $sheet->getCell('D18')->getValue(); // linked to checkbox
            $others = $sheet->getCell('D19')->getValue(); // linked to checkbox



            $sex = null;
            if ($isMale === true || $isMale === 'TRUE') {
                $sex = 'Male';
            } elseif ($isFemale === true || $isFemale === 'TRUE') {
                $sex = 'Female';
            } else {
                $sex = 'prefer not to say';
            }

            // Normalize TRUE values (Excel checkboxes return TRUE or false)
            function isChecked($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // Determine citizenship status
            if (isChecked($filipino)) {
                $citizenship_status = 'Filipino';
            } elseif (isChecked($by_birth)) {
                $citizenship_status = 'By Birth';
            } elseif (isChecked($dual_citizenship)) {
                $citizenship_status = 'Dual Citizenship';
            } elseif (isChecked($by_naturalization)) {
                $citizenship_status = 'By Naturalization';
            } else {
                $citizenship_status = 'Unknown/Unspecified'; // fallback
            }



            function isChecked_civil_status($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // Determine citizenship status
            if (isChecked_civil_status($single)) {
                $civil_status = 'Single';
            } elseif (isChecked_civil_status($married)) {
                $civil_status= 'Married';
            } elseif (isChecked_civil_status($separated)) {
                $civil_status= 'Separated';
            } elseif (isChecked_civil_status($widowed)) {
                $civil_status = 'Widowed';
            } elseif (isChecked_civil_status($others)) {
                $civil_status = 'Others';
            } else {
                $civil_status = null; // fallback
            }


            $data = [

                'lastname' => $sheet->getCell('D10')->getValue(),
                'firstname' => $sheet->getCell('D11')->getValue(),
                'middlename' => $sheet->getCell('D12')->getValue(),
                'name_extension' => $sheet->getCell('L11')->getValue(),
                'sex' => $sex,
                'civil_status' => $civil_status,
                'citizenship,' => $citizenship_status,
                'citizenship_status',// this is for dual citizenship only
                'date_of_birth' =>  $date_of_birth,
                'place_of_birth' => $sheet->getCell('D15')->getValue(),
                'height' => $sheet->getCell('D21')->getValue(),
                'weight' => $sheet->getCell('D23')->getValue(),
                'blood_type' => $sheet->getCell('D24')->getValue(),
                'gsis_no' => $sheet->getCell('D26')->getValue(),
                'pagibig_no' => $sheet->getCell('D28')->getValue(),
                'philhealth_no' => $sheet->getCell('D30')->getValue(),
                'sss_no' => $sheet->getCell('D31')->getValue(),
                'tin_no' => $sheet->getCell('D32')->getValue(),
                'image_path' => $imagePath,

                'residential_house' => $sheet->getCell('I17')->getValue(),
                'residential_street' => $sheet->getCell('L17')->getValue(),
                'residential_subdivision' => $sheet->getCell('I19')->getValue(),
                'residential_barangay' => $sheet->getCell('L19')->getValue(),
                'residential_city' => $sheet->getCell('I21')->getValue(),
                'residential_province' => $sheet->getCell('L21')->getValue(),
                // 'residential_region' => $sheet->getCell('I19')->getValue(),
                'residential_zip' => $sheet->getCell('I23')->getValue(),

                'permanent_house' => $sheet->getCell('I24')->getValue(),
                'permanent_street' => $sheet->getCell('L24')->getValue(),
                'permanent_subdivision' => $sheet->getCell('I26')->getValue(),
                'permanent_barangay' => $sheet->getCell('L26')->getValue(),
                'permanent_city' => $sheet->getCell('I28')->getValue(),
                'permanent_province' => $sheet->getCell('L28')->getValue(),
                'permanent_zip' => $sheet->getCell('I30')->getValue(),

                'telephone_number' => $sheet->getCell('I31')->getValue(),
                'cellphone_number' => $sheet->getCell('I32')->getValue(),
                'email_address' => $sheet->getCell('I33')->getValue(),


            ];

            // Validate personal info data
            $validator = Validator::make($data, [
                'lastname' => 'required',
                'firstname' => 'required',
                // Add other validation rules as needed
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Create personal info
            // Create personal info
            $personalInfo = nPersonal_info::create($data);

            // Attach job batch after creating personal info
            $personalInfo->job_batches_rsp()->attach($event->getConcernable()->jobBatchId);
            $event->getConcernable()->importer->setPersonalInfoId($personalInfo->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private static function parseDate($value, $allowYearOnly = false)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                // Excel numeric date
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                $date = \Carbon\Carbon::parse($value);
                return $allowYearOnly ? $date->format('Y') : $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$value}");
        }
    }
}
