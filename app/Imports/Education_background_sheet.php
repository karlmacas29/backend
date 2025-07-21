<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Education_background;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class Education_background_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 4; // Start reading from row 3
    }

    public function collection(Collection $rows)
    {
        $saved = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            try {
                // $attendance_from = $this->parseDate($row[3]);
                // $attendance_to   = $this->parseDate($row[4]);
                // $year_graduated  = $this->parseDate($row[6], true); // allow year only

                Education_background::create([
                    'level'             => $row[0] ?? null,
                    'school_name'       => $row[1] ?? null,
                    'degree'            => $row[2] ?? null,
                    'attendance_from'   => $row[3] ?? null,
                    'attendance_to'     => $row[4] ?? null,
                    'highest_units'     => $row[5] ?? null,
                    'year_graduated'    => $row[6] ?? null,
                    'scholarship'       => $row[7] ?? null,
                    'nPersonalInfo_id'  => $this->importer->getPersonalInfoId(),
                ]);

                $saved++;
            } catch (\Exception $e) {
                Log::error('Failed to save row: ' . $e->getMessage(), ['row' => $row]);
                $skipped++;
            }
        }

        Log::info("Education import completed: {$saved} saved, {$skipped} skipped.");
    }

    /**
     * Parse date helper.
     */
    // private function parseDate($value, $allowYearOnly = false)
    // {
    //     if (empty($value)) {
    //         return null;
    //     }

    //     try {
    //         if (is_numeric($value)) {
    //             // Excel numeric date
    //             return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
    //         } else {
    //             $date = \Carbon\Carbon::parse($value);
    //             return $allowYearOnly ? $date->format('Y') : $date->format('Y-m-d');
    //         }
    //     } catch (\Exception $e) {
    //         throw new \Exception("Invalid date format: {$value}");
    //     }
    // }
}
