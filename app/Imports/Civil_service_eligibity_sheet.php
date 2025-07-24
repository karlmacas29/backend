<?php

namespace App\Imports;

use App\Models\excel\Civil_service_eligibity;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Civil_service_eligibity_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 5; // <-- Start from row 2 (skip header row)
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                try {
                    $date_of_examination = $this->parseDate($row[2]);
                    $date_of_validity = $this->parseDate($row[5]);

                    Civil_service_eligibity::create([
                        'eligibility'          => $row[0],
                        'rating'               => $row[1],
                        'date_of_examination'  => $date_of_examination,
                        'place_of_examination' => $row[3],
                        'license_number'       => $row[4],
                        'date_of_validity'     => $date_of_validity,
                        'nPersonalInfo_id'     => $this->importer->getPersonalInfoId(),
                    ]);
                } catch (\Exception $e) {
                    // Log the error and continue with next row
                    logger()->error("Error importing civil service eligibility row: " . $e->getMessage());
                    continue;
                }
            }
        });
    }

    private function parseDate($value, $allowYearOnly = false)
    {
        if (empty($value) || strtolower(trim($value)) === 'n/a') {
            return null;
        }

        // If it's already a Carbon instance (might happen with some Excel readers)
        if ($value instanceof Carbon) {
            return $value->format('m/d/Y');
        }

        // Handle Excel numeric dates
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('m/d/Y');
            } catch (\Exception $e) {
                throw new \Exception("Invalid Excel date format: {$value}");
            }
        }

        $value = trim($value);

        // List of possible date formats to try
        $formats = [
            'F d Y',     // June 11 2002
            'F j Y',     // June 1 2002 (no leading zero)
            'm/d/Y',     // 06/11/2002
            'm-d-Y',     // 06-11-2002
            'd/m/Y',     // 11/06/2002
            'd-m-Y',     // 11-06-2002
            'Y/m/d',     // 2002/11/06
            'Y-m-d',     // 2002-11-06
            'F Y',       // June 2002 (month and year only)
            'Y',         // 2002 (year only) - if allowed
            'm/d/y',     // 06/11/02 (2-digit year)
            'd.m.Y',     // 11.06.2002 (European format)
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                // For month/year or year-only formats, set to first day of month/year
                if (in_array($format, ['F Y', 'Y'])) {
                    if ($format === 'F Y') {
                        $date->day(1);
                    } else {
                        $date->month(1)->day(1);
                    }
                }

                // Validate the date (Carbon will throw exception if invalid)
                if ($date->isValid()) {
                    return $date->format('m/d/Y');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's loose parsing if no format matched
        try {
            $date = Carbon::parse($value);
            if ($date->isValid()) {
                return $date->format('m/d/Y');
            }
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$value}. Could not parse as any known format.");
        }

        // If we get here and year-only is allowed, try to extract just the year
        if ($allowYearOnly) {
            if (preg_match('/\b\d{4}\b/', $value, $matches)) {
                return Carbon::createFromDate($matches[0], 1, 1)->format('Y');
            }
        }

        throw new \Exception("Unrecognized date format: {$value}");
    }
}
