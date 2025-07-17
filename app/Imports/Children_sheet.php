<?php

namespace App\Imports;

use Carbon\Carbon;

use App\Models\Children;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class Children_sheet implements ToCollection, WithStartRow
{

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

     protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer;
    }

    public function startRow(): int
    {
        return 3; // <-- Start from row 2 (skip header row)
    }


    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Skip if both name and date are empty
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                $childName = $row[0] ?? null;
                $rawDate = $row[1] ?? null;
                $formattedDate = null;

                // Optional: skip if name is missing (can be adjusted based on your requirements)
                if (empty($childName)) {
                    continue;
                }

                if ($rawDate) {
                    try {
                        if (is_numeric($rawDate)) {
                            $formattedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                        } else {
                            $formattedDate = \Carbon\Carbon::parse($rawDate)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        throw new \Exception("Invalid date format in row: {$row[0]}");
                    }
                }

                Children::create([
                    'child_name'        => $childName,
                    'birth_date'        => $formattedDate,
                    'nPersonalInfo_id'  => $this->importer->getPersonalInfoId(),
                ]);
            }
        });
    }
}
