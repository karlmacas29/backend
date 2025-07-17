<?php

namespace App\Imports;

use App\Models\nPersonal_info;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Sheet;

class PersonalInformationSheet implements WithEvents
{
    use RegistersEventListeners;

    protected $importer;

    public function __construct($importer){

        $this->importer = $importer;
    }

    public static function afterSheet(AfterSheet $event)
    {

        DB::beginTransaction();

    try{


        $sheet = $event->sheet->getDelegate();
        $data =[


        'lastname' => $sheet->getCell('D10')->getValue(),
        'firstname' => $sheet->getCell('D11')->getValue(),
        'middlename' => $sheet->getCell('D12')->getValue(),
        'name_extension' =>$sheet->getCell('L11')->getValue(),
        'date_of_birth' => $sheet->getCell('D13')->getValue(),
        'place_of_birth' => $sheet->getCell('D15')->getValue(),
        'height' => $sheet->getCell('D22')->getValue(),
        'weight' => $sheet->getCell('D24')->getValue(),
        'blood_type' => $sheet->getCell('D25')->getValue(),
        'gsis_no' => $sheet->getCell('D27')->getValue(),
        'pagibig_no' => $sheet->getCell('D29')->getValue(),
        'philhealth' => $sheet->getCell('D31')->getValue(),
        'sss_no' => $sheet->getCell('D32')->getValue(),
        'tin_no' => $sheet->getCell('D33')->getValue(),
        'image_path' => $sheet->getCell('S3')->getValue(),
        'residential_house' => $sheet->getCell('I17')->getValue(),
        'residential_street' => $sheet->getCell('L17')->getValue(),
        'residential_subdivision' => $sheet->getCell('I19')->getValue(),
        'residential_barangay' => $sheet->getCell('L19')->getValue(),
        'residential_city' => $sheet->getCell('I22')->getValue(),
        'residential_province' => $sheet->getCell('L22')->getValue(),
        // 'residential_region' => $sheet->getCell('I19')->getValue(),
        'residential_zip' => $sheet->getCell('I24')->getValue(),

        'permanent_house' => $sheet->getCell('I25')->getValue(),
        'permanent_street' => $sheet->getCell('L25')->getValue(),
        'permanent_subdivision' => $sheet->getCell('I27')->getValue(),
        'permanent_barangay' => $sheet->getCell('L27')->getValue(),
        'permanent_city' => $sheet->getCell('J29')->getValue(),
        'permanent_province' => $sheet->getCell('M29')->getValue(),

        'telephone' => $sheet->getCell('I32')->getValue(),
        'cellphone_number' => $sheet->getCell('I33')->getValue(),
        'email_address' => $sheet->getCell('I34')->getValue(),

        $telephone  = $sheet->getCell('I32')->getValue(), // Adjust cell
        $date_of_birth  = $sheet->getCell('D13')->getValue(), // Adjust cell
        $email_address  = $sheet->getCell('I34')->getValue(), // Adjust cell
        $cellphone_number  = $sheet->getCell('I33')->getValue(), // Adjust cell


        // $citizenship = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $citizenship_status = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $religion = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_house = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_street = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_subdivision = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_barangay = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_city = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_province = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_region = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $residential_zip = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_region = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_house = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_street = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_subdivision = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_barangay = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_city  = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_province  = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $permanent_zip  = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $name_extension = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $sex = $sheet->getCell('D10')->getValue(); // Adjust cell
        // $civil_status = $sheet->getCell('D10')->getValue(); // Adjust cell
        $height  = $sheet->getCell('D22')->getValue(), // Adjust cell
        $weight  = $sheet->getCell('D24')->getValue(), // Adjust cell
        $blood_type = $sheet->getCell('D25')->getValue(), // Adjust cell
        $telephone  = $sheet->getCell('I32')->getValue(), // Adjust cell
        $date_of_birth  = $sheet->getCell('D13')->getValue(), // Adjust cell

        $email_address  = $sheet->getCell('I34')->getValue(), // Adjust cell
        $cellphone_number  = $sheet->getCell('I33')->getValue(), // Adjust cell

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
            $personalInfo = nPersonal_info::create($data);
            $event->getConcernable()->importer->setPersonalInfoId($personalInfo->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
