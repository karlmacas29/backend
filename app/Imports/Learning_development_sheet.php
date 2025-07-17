<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Learning_development;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Learning_development_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 6; // or your actual start row
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Skip row if all relevant columns are empty
                if (
                    empty($row[0]) && empty($row[1]) && empty($row[2]) &&
                    empty($row[3]) && empty($row[4]) && empty($row[5])
                ) {
                    continue;
                }

                try {

                    $inclusive_date_from = $this->parseDate($row[1]);
                    $inclusive_date_to   = $this->parseDate($row[2]);
                    
                    $trainingData = [
                        'nPersonalInfo_id'     => $this->importer->getPersonalInfoId(),
                        'training_title'       => $row[0] ?? null,
                        'inclusive_date_from'  =>  $inclusive_date_from  ?? null,
                        'inclusive_date_to'    =>  $inclusive_date_to ?? null,
                        'number_of_hours'      => $row[3] ?? null,
                        'type'                 => $row[4] ?? null,
                        'conducted_by'         => $row[5] ?? null,
                    ];

                    $validator = Validator::make($trainingData, [

                    ]);

                    if ($validator->fails()) {
                        throw new ValidationException($validator);
                    }

                    Learning_development::create($trainingData);
                } catch (\Exception $e) {
                    logger()->error("Training row skipped: " . $e->getMessage());
                    continue;
                }
            }
        });
    }

    private function parseDate($value)
    {
        if (empty($value) || strtolower(trim($value)) === 'n/a') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('m/d/Y');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('m/d/Y');
            } catch (\Exception $e) {
                throw new \Exception("Invalid Excel numeric date: {$value}");
            }
        }

        $formats = [
            'm/d/Y',
            'm-d-Y',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'Y-m-d',
            'F d Y',
            'F Y',
            'm/d/y',
            'd.m.Y',
            'Y'
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim($value));
                if ($format === 'Y') {
                    $date->month(1)->day(1);
                } elseif ($format === 'F Y') {
                    $date->day(1);
                }

                if ($date->isValid()) {
                    return $date->format('m/d/Y');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $date = Carbon::parse($value);
            if ($date->isValid()) {
                return $date->format('m/d/Y');
            }
        } catch (\Exception $e) {
            throw new \Exception("Unrecognized date format: {$value}");
        }

        return null;
    }
}
