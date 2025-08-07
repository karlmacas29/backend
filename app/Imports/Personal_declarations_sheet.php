<?php

namespace App\Imports;

use App\Models\excel\Personal_declarations;
use App\Models\excel\references;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class Personal_declarations_sheet implements  WithEvents
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    use RegistersEventListeners;

    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer; // Needed for nPersonalInfo_id
    }


    public static function afterSheet(AfterSheet $event)
    {

        DB::beginTransaction();

        try {
            $sheet = $event->sheet->getDelegate();
            $date_file = self::parseDate($sheet->getCell('I15')->getValue());


            //Q34
            $a_third_degree_yes = $sheet->getCell('G5')->getValue(); // linked to checkbox
            $a_third_degree_no = $sheet->getCell('I5')->getValue(); // linked to checkbox

            //a
            function isChecked_a_degree($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // Determine citizenship status
            if (isChecked_a_degree($a_third_degree_yes)) {
                $degree = 'YES';
            } elseif (isChecked_a_degree($a_third_degree_no)) {
                $degree = 'NO';
            } else {
                $degree = null; // fallback
            }

            $b_fourth_degree_yes = $sheet->getCell('G6')->getValue(); // linked to checkbox
            $b_fourth_degree_no= $sheet->getCell('I6')->getValue(); // linked to checkbox

            function isChecked_b_fourth_degree($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // B
            if (isChecked_b_fourth_degree($b_fourth_degree_yes)) {
                $b_fourth_degree= 'YES';
            } elseif (isChecked_b_fourth_degree($b_fourth_degree_no)) {
                $b_fourth_degree  = 'NO';
            } else {
                $b_fourth_degree  = null; // fallback
            }




            // Q35
            $a_found_guilty_yes = $sheet->getCell('G9')->getValue(); // linked to checkbox
            $a_found_guilty_no = $sheet->getCell('I9')->getValue(); // linked to checkbox

            function isChecked_a_found_guilty($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_a_found_guilty($a_found_guilty_yes)) {
                $found_guilty = 'YES';
            } elseif (isChecked_a_found_guilty($a_found_guilty_no)) {
                $found_guilty  = 'NO';
            } else {
                $found_guilty  = null; // fallback
            }





            $b_criminally_charged_yes= $sheet->getCell('G12')->getValue(); // linked to checkbox
            $b_criminally_charged_no = $sheet->getCell('I12')->getValue(); // linked to checkbox

            function isChecked_b_criminally_charged($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_b_criminally_charged($b_criminally_charged_yes)) {
                $_criminally_charged = 'YES';
            } elseif (isChecked_b_criminally_charged($b_criminally_charged_no)) {
                $_criminally_charged  = 'NO';
            } else {
                $_criminally_charged  = null; // fallback
            }


           //Q36
            $convited_answer_yes = $sheet->getCell('G18')->getValue(); // linked to checkbox
            $convited_answer_no = $sheet->getCell('I18')->getValue(); // linked to checkbox

            function isChecked_a_convited_answer($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_a_convited_answer($convited_answer_yes)) {
                $convited_answer = 'YES';
            } elseif (isChecked_a_convited_answer($convited_answer_no)) {
                $convited_answer  = 'NO';
            } else {
                $convited_answer  = null; // fallback
            }


            //Q37
            $service_yes = $sheet->getCell('G18')->getValue(); // linked to checkbox
            $service_no = $sheet->getCell('I18')->getValue(); // linked to checkbox

            function isChecked_service($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_service($service_yes)) {
                $service = 'YES';
            } elseif (isChecked_service($service_no)) {
                $service = 'NO';
            } else {
                $service  = null; // fallback
            }


             // Q38

            //a_candidate
            $a_candidate_yes = $sheet->getCell('G21')->getValue(); // linked to checkbox
            $a_candidate_no = $sheet->getCell('I21')->getValue(); // linked to checkbox

            function isChecked_candidate($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_candidate($a_candidate_yes)) {
                $candidate = 'YES';
            } elseif (isChecked_candidate($a_candidate_no)) {
                $candidate = 'NO';
            } else {
                $candidate  = null; // fallback
            }


            //'b_resigned',
            $b_resigned_yes = $sheet->getCell('G26')->getValue(); // linked to checkbox
            $b_resigned_no = $sheet->getCell('I26')->getValue(); // linked to checkbox

            function isChecked_resigned($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_resigned($b_resigned_yes)) {
                $resigned = 'YES';
            } elseif (isChecked_resigned($b_resigned_no)) {
                $resigned = 'NO';
            } else {
                $resigned  = null; // fallback
            }


            //Q39

            //''39_status',',
            $status_yes = $sheet->getCell('G30')->getValue(); // linked to checkbox
            $status_no = $sheet->getCell('I30')->getValue(); // linked to checkbox
            function isChecked_status($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_status($status_yes)) {
                $status = 'YES';
            } elseif (isChecked_status($status_no)) {
                $status = 'NO';
            } else {
                $status  = null; // fallback
            }



            //Q40

            //'''a_indigenous',',',
            $a_indigenous_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $a_indigenous_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_indigenous($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_indigenous($a_indigenous_yes)) {
                $indigenous = 'YES';
            } elseif (isChecked_indigenous($a_indigenous_no)) {
                $indigenous = 'NO';
            } else {
                $indigenous = null; // fallback
            }



            //'''b_disability',',',
            $b_disability_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $b_disability_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_disability($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_disability($b_disability_yes)) {
                $disability = 'YES';
            } elseif (isChecked_disability($b_disability_no)) {
                $disability = 'NO';
            } else {
                $disability = null; // fallback
            }


            //'c_solo',
            $c_solo_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $c_solo_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_solo($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_solo($c_solo_yes)) {
                $solo= 'YES';
            } elseif (isChecked_solo($c_solo_no)) {
                $solo = 'NO';
            } else {
                $solo = null; // fallback
            }
            $data = [

                'nPersonalInfo_id'   => $event->getConcernable()->importer->getPersonalInfoId(),
                'a_third_degree_answer' =>$degree,
                'b_fourth_degree_answer' => $b_fourth_degree,
                '34_if_yes' => $sheet->getCell('I7')->getValue(), // linked to checkbox

                // Q35
                'a_found_guilty' => $found_guilty,
                'guilty_yes' =>$sheet->getCell('I10')->getValue(),
                'b_criminally_charged' => $_criminally_charged,
                'case_date_filed' => $date_file,
                'case_status ' => $sheet->getCell('I16')->getValue(),

                // Q36
                '36_convited_answer' => $convited_answer,
                '36_if_yes' =>$sheet->getCell('I19')->getValue(),

                // Q37
                '37_service' => $service,
                '37_if_yes' =>$sheet->getCell('I19')->getValue(),

                // Q38
                'a_candidate' =>  $candidate,
                'candidate_yes'  => $sheet->getCell('I25')->getValue(),
                'b_resigned' => $resigned,
                'resigned_yes'  => $sheet->getCell('I28')->getValue(),

                // Q39
                '39_status' => $status,
                '39_if_yes'=> $sheet->getCell('I31')->getValue(),

                // Q40
                'a_indigenous' => $indigenous,
                'indigenous_yes'=> $sheet->getCell('I35')->getValue(),
                'b_disability' => $disability,
                'disability_yes'=> $sheet->getCell('I37')->getValue(),
                'c_solo'=>$solo,
                'solo_parent_yes'=> $sheet->getCell('I39')->getValue(),

            ];

            // Validate personal info data
            $validator = Validator::make($data, [
                // 'lastname' => 'required',
                // 'firstname' => 'required',
                // // Add other validation rules as needed
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Create personal info
            $personal = Personal_declarations::create($data);
            // $event->getConcernable()->importer->setPersonalInfoId($personal->id);
            // $personalInfoId = $personal->id;
            // $event->getConcernable()->importer->setPersonalInfoId($personalInfoId);

            // References import logic (example: rows 41 to 50, skipping row 40)
            // After creating $personal
            for ($row = 43; $row <= 50; $row++) {


                // Adjust columns if needed!
                $full_name = $sheet->getCell('A' . $row)->getValue();
                $address = $sheet->getCell('F' . $row)->getValue();
                $contact_number = $sheet->getCell('I' . $row)->getValue();

                // Debug: Log what's being read
                error_log("Row $row: " . $full_name . ', ' . $address . ', ' . $contact_number);
                error_log('Attempting to insert reference with nPersonalInfo_id = ' . $event->getConcernable()->importer->getPersonalInfoId());
                if (empty($full_name) && empty($address) && empty($contact_number)) {
                    continue;
                }

                references::create([
                    'nPersonalInfo_id' => $event->getConcernable()->importer->getPersonalInfoId(),
                    'full_name'        => $full_name,
                    'address'          => $address,
                    'contact_number'   => $contact_number,
                ]);

            }
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
