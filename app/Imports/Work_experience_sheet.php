<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\excel\Work_experience;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Work_experience_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 6;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                try {
                    $work_date_from = $this->parseDate($row[0]);
                    $work_date_to   = $this->parseDate($row[1]);

                    $workData = [
                        'nPersonalInfo_id'      => $this->importer->getPersonalInfoId(),
                        'work_date_from'        => $work_date_from ?? null,
                        'work_date_to'          => $work_date_to ?? null,
                        'position_title'        => $row[2] ?? null,
                        'department'            => $row[3] ?? null,
                        'monthly_salary'        => $row[4] ?? null,
                        'salary_grade'          => $row[5] ?? null,
                        'status_of_appointment' => $row[6] ?? null,
                        'government_service'    => $row[7] ?? null,
                    ];

                    // ✅ Validate BEFORE insert
                    $validator = Validator::make($workData, [
                        // 'position_title'     => 'required',
                    ], [
                        // 'position_title.required'     => 'The position title is required.',
                    ]);

                    if ($validator->fails()) {
                        throw new ValidationException($validator);
                    }

                    // ✅ Only insert if validation passes
                    Work_experience::create($workData);
                } catch (\Exception $e) {
                    logger()->error("Error importing work experience row: " . $e->getMessage());
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
