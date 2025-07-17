<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Voluntary_work;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Voluntary_work_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 5; // Adjust if needed
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty($row[1]) && empty($row[2])) {
                    continue;
                }

                try {
                    $inclusive_date_from = $this->parseDate($row[1]);
                    $inclusive_date_to   = $this->parseDate($row[2]);


                    $voluntaryData = [
                        'nPersonalInfo_id'   => $this->importer->getPersonalInfoId(),
                        'organization_name'  => $row[0] ?? null,
                        'inclusive_date_from' => $inclusive_date_from ?? null,
                        'inclusive_date_to'  => $inclusive_date_to ?? null,
                        'number_of_hours'    => $row[3] ?? null,
                        'position'           => $row[4] ?? null,
                    ];

                    // Validation
                    $validator = Validator::make($voluntaryData, [
                        // 'organization_name'  => 'required|string',
                        // 'inclusive_date_from' => 'required|string',
                        // 'inclusive_date_to'  => 'required|string',
                        // 'position'           => 'required|string',
                    ]);

                    if ($validator->fails()) {
                        throw new ValidationException($validator);
                    }

                    // Insert only if validation passes
                    Voluntary_work::create($voluntaryData);
                } catch (\Exception $e) {
                    logger()->error("Voluntary work row skipped: " . $e->getMessage());
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
