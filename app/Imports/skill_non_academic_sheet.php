<?php

namespace App\Imports;

use App\Models\excel\skill_non_academic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class skill_non_academic_sheet implements ToCollection, WithStartRow
{
    protected $importer;

    public function __construct($importer)
    {
        $this->importer = $importer; // Needed for nPersonalInfo_id
    }

    public function startRow(): int
    {
        return 3; // or whatever your starting row is
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Skip row if all columns are empty
                if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                    continue;
                }

                $data = [
                    'nPersonalInfo_id' => $this->importer->getPersonalInfoId(),
                    'skill'            => $row[0] ?? null,
                    'non_academic'     => $row[1] ?? null,
                    'organization'     => $row[2] ?? null,
                ];

                $validator = Validator::make($data, [
                    // 'skill'        => 'required|string',
                    // 'non_academic' => 'nullable|string',
                    // 'organization' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    // Skip invalid row
                    continue;
                }

                skill_non_academic::create($data);
            }
        });
    }
}
