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
            $question_34a_yes = $sheet->getCell('G5')->getValue(); // linked to checkbox
            $question_34a_no = $sheet->getCell('I5')->getValue(); // linked to checkbox

            //a
            function isChecked_a_degree($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // Determine citizenship status
            if (isChecked_a_degree($question_34a_yes)) {
                $degree = 'YES';
            } elseif (isChecked_a_degree($question_34a_no)) {
                $degree = 'NO';
            } else {
                $degree = null; // fallback
            }

            $question_34b_yes = $sheet->getCell('G6')->getValue(); // linked to checkbox
            $question_34b_no= $sheet->getCell('I6')->getValue(); // linked to checkbox

            function isChecked_b_fourth_degree($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // B
            if (isChecked_b_fourth_degree($question_34b_yes)) {
                $b_fourth_degree= 'YES';
            } elseif (isChecked_b_fourth_degree($question_34b_no)) {
                $b_fourth_degree  = 'NO';
            } else {
                $b_fourth_degree  = null; // fallback
            }




            // Q35
            $question_35a_yes = $sheet->getCell('G9')->getValue(); // linked to checkbox
            $question_35a_no = $sheet->getCell('I9')->getValue(); // linked to checkbox

            function isChecked_a_found_guilty($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_a_found_guilty($question_35a_yes)) {
                $found_guilty = 'YES';
            } elseif (isChecked_a_found_guilty($question_35a_no)) {
                $found_guilty  = 'NO';
            } else {
                $found_guilty  = null; // fallback
            }





            $question_35b_yes= $sheet->getCell('G12')->getValue(); // linked to checkbox
            $question_35b_no = $sheet->getCell('I12')->getValue(); // linked to checkbox

            function isChecked_b_criminally_charged($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_b_criminally_charged($question_35b_yes)) {
                $_criminally_charged = 'YES';
            } elseif (isChecked_b_criminally_charged($question_35b_no)) {
                $_criminally_charged  = 'NO';
            } else {
                $_criminally_charged  = null; // fallback
            }


           //Q36
            $question_36_yes = $sheet->getCell('G18')->getValue(); // linked to checkbox
            $question_36_no = $sheet->getCell('I18')->getValue(); // linked to checkbox

            function isChecked_a_convited_answer($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_a_convited_answer($question_36_yes)) {
                $convited_answer = 'YES';
            } elseif (isChecked_a_convited_answer($question_36_no)) {
                $convited_answer  = 'NO';
            } else {
                $convited_answer  = null; // fallback
            }


            //Q37
            $question_37_yes = $sheet->getCell('G18')->getValue(); // linked to checkbox
            $question_37_no = $sheet->getCell('I18')->getValue(); // linked to checkbox

            function isChecked_service($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_service($question_37_yes)) {
                $service = 'YES';
            } elseif (isChecked_service($question_37_no)) {
                $service = 'NO';
            } else {
                $service  = null; // fallback
            }


             // Q38

            //a_candidate
            $question_38a_yes = $sheet->getCell('G21')->getValue(); // linked to checkbox
            $question_38a_no = $sheet->getCell('I21')->getValue(); // linked to checkbox

            function isChecked_candidate($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_candidate($question_38a_yes)) {
                $candidate = 'YES';
            } elseif (isChecked_candidate($question_38a_no)) {
                $candidate = 'NO';
            } else {
                $candidate  = null; // fallback
            }


            //'b_resigned',
            $question_38b_yes = $sheet->getCell('G26')->getValue(); // linked to checkbox
            $question_38b_no = $sheet->getCell('I26')->getValue(); // linked to checkbox

            function isChecked_resigned($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_resigned($question_38b_yes)) {
                $resigned = 'YES';
            } elseif (isChecked_resigned($question_38b_no)) {
                $resigned = 'NO';
            } else {
                $resigned  = null; // fallback
            }


            //Q39

            //''39_status',',
            $question_39_yes = $sheet->getCell('G30')->getValue(); // linked to checkbox
            $question_39_no = $sheet->getCell('I30')->getValue(); // linked to checkbox
            function isChecked_status($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_status($question_39_yes)) {
                $status = 'YES';
            } elseif (isChecked_status($question_39_no)) {
                $status = 'NO';
            } else {
                $status  = null; // fallback
            }



            //Q40

            //'''a_indigenous',',',
            $question_40a_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $question_40a_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_indigenous($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_indigenous($question_40a_yes)) {
                $indigenous = 'YES';
            } elseif (isChecked_indigenous($question_40a_no)) {
                $indigenous = 'NO';
            } else {
                $indigenous = null; // fallback
            }



            //'''b_disability',',',
            $question_40b_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $question_40b_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_disability($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_disability($question_40b_yes)) {
                $disability = 'YES';
            } elseif (isChecked_disability($question_40b_no)) {
                $disability = 'NO';
            } else {
                $disability = null; // fallback
            }


            //'c_solo',
            $question_40c_yes = $sheet->getCell('G34')->getValue(); // linked to checkbox
            $question_40c_no = $sheet->getCell('I34')->getValue(); // linked to checkbox
            function isChecked_solo($value)
            {
                return $value === true || strtoupper($value) === 'TRUE';
            }

            // a
            if (isChecked_solo($question_40c_yes)) {
                $solo= 'YES';
            } elseif (isChecked_solo($question_40c_no)) {
                $solo = 'NO';
            } else {
                $solo = null; // fallback
            }
            $data = [

                'nPersonalInfo_id'   => $event->getConcernable()->importer->getPersonalInfoId(),

                'question_34a' =>$degree,
                'question_34b' => $b_fourth_degree,
                'response_34' => $sheet->getCell('I7')->getValue(), // linked to checkbox

                // Q35
                'question_35a' => $found_guilty,
                'response_35a' =>$sheet->getCell('I10')->getValue(),
                'question_35b' => $_criminally_charged,
                'response_35b_date' => $date_file,
                'response_35b_status ' => $sheet->getCell('I16')->getValue(),

                // Q36
                'question_36' => $convited_answer,
                'response_36' =>$sheet->getCell('I19')->getValue(),

                // Q37
                'question_37' => $service,
                'response_37' =>$sheet->getCell('I19')->getValue(),

                // Q38
                'question_38a' =>  $candidate,
                'response_38a'  => $sheet->getCell('I25')->getValue(),
                'question_38b' => $resigned,
                'response_38b'  => $sheet->getCell('I28')->getValue(),

                // Q39
                'question_39' => $status,
                'response_39'=> $sheet->getCell('I31')->getValue(),

                // Q40
                'question_40a' => $indigenous,
                'response_40a'=> $sheet->getCell('I35')->getValue(),

                'question_40b' => $disability,
                'response_40b'=> $sheet->getCell('I37')->getValue(),

                'question_40c'=>$solo,
                'response_40c'=> $sheet->getCell('I39')->getValue(),

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
                // error_log("Row $row: " . $full_name . ', ' . $address . ', ' . $contact_number);
                // error_log('Attempting to insert reference with nPersonalInfo_id = ' . $event->getConcernable()->importer->getPersonalInfoId());
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
