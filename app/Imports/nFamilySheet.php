<?php

namespace App\Imports;

use App\Models\nFamily;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class nFamilySheet implements WithEvents
{
    use RegistersEventListeners;

    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public static function afterSheet(AfterSheet $event)
    {
        DB::beginTransaction();

        try {
            $sheet = $event->sheet->getDelegate();

            $familyData = [
                'spouse_name' => $sheet->getCell('D2')->getValue(),
                'spouse_firstname' => $sheet->getCell('D3')->getValue(),
                'spouse_occupation' => $sheet->getCell('D5')->getValue(),
                'spouse_employer' => $sheet->getCell('D6')->getValue(),
                'spouse_extension' => $sheet->getCell('I3')->getValue(),
                'spouse_middlename' => $sheet->getCell('D4')->getValue(),
                'spouse_employer_address' => $sheet->getCell('D7')->getValue(),
                'spouse_employer_telephone' => $sheet->getCell('D8')->getValue(),

                'father_name' => $sheet->getCell('D6')->getValue(),
                'father_last' => $sheet->getCell('D9')->getValue(),
                'father_firstname' => $sheet->getCell('D10')->getValue(),
                'father_extension' => $sheet->getCell('I10')->getValue(),

                'mother_name' => $sheet->getCell('D13')->getValue(),
                'mother_lastname' => $sheet->getCell('D12')->getValue(),
                'mother_firstname' => $sheet->getCell('D14')->getValue(),
                'mother_middlename' => $sheet->getCell('D15')->getValue(),
                'mother_maidenname' => $sheet->getCell('D12')->getValue(),
                'nPersonalInfo_id' => $event->getConcernable()->importer->getPersonalInfoId(),

            ];

            // Validate family data
            $validator = Validator::make($familyData, [
                'spouse_name' => 'required',
                'spouse_firstname' => 'required',
                'nPersonalInfo_id' => 'required|exists:nPersonalInfo,id'
            ], [
                'spouse_name.required' => 'Spouse last name is required',
                'spouse_firstname.required' => 'Spouse first name is required',
                'nPersonalInfo_id.required' => 'Personal Info reference is required',
                'nPersonalInfo_id.exists' => 'Referenced Personal Info does not exist'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            nFamily::create($familyData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
