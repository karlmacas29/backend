<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\library\CriteriaLibrary;
use App\Models\library\CriteriaLibTraining;
use App\Models\library\CriteriaLibEducation;
use App\Models\library\CriteriaLibBehavioral;
use App\Models\library\CriteriaLibExperience;
use App\Models\library\CriteriaLibPerformance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CriteriaLibraryASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 1. Create/Update the main Criteria Library record (SG 23-25)
        // This is the model that holds the overall salary grade range.
        $criteriaLibrary = CriteriaLibrary::updateOrCreate(
            [
                'sg_min' => 23,
                'sg_max' => 25,
            ],
            // Data to update/create (same as finding attributes here)
            [
                'sg_min' => 23,
                'sg_max' => 25,
            ]
        );

        $criteriaLibraryId = $criteriaLibrary->id;

        // --- Data from the provided scoring system ---

        // 2. Education Criteria (Total Weight: 20)
        $educationData = [
            ['description' => 'Completion of Relevant Graduate Studies', 'weight' => 20, 'percentage' => 20],
            ['description' => 'With More than 30 units of Relevant Graduate Studies', 'weight' => 20, 'percentage' => 18],
            ['description' => 'Completion of Bachelor\'s Degree and/or Minimum Educational Requirement of the Position', 'weight' => 20, 'percentage' => 15],
        ];

        foreach ($educationData as $data) {
            CriteriaLibEducation::updateOrCreate(
                [
                    // FIND: Use the library ID and description for a unique composite key
                    'criteria_library_id' => $criteriaLibraryId,
                    'description' => $data['description']
                ],
                // CREATE/UPDATE: The rest of the data
                $data
            );
        }

        // 3. Experience Criteria (Total Weight: 30 based on data)
        $experienceData = [
            ['description' => '500% and above of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 30],
            ['description' => '400%-499% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 28],
            ['description' => '300%-399% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 26],
            ['description' => '200%-299% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 24],
            ['description' => '101%-199% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 22],
            ['description' => 'Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 20],
        ];

        foreach ($experienceData as $data) {
            CriteriaLibExperience::updateOrCreate(
                [
                    // FIND: Use the library ID and description for a unique composite key
                    'criteria_library_id' => $criteriaLibraryId,
                    'description' => $data['description']
                ],
                // CREATE/UPDATE: The rest of the data
                $data
            );
        }

        // 4. Training Criteria (Total Weight: 10)
        $trainingData = [
            ['description' => 'FOR POSITIONS THAT REQUIRE TRAINING: More than the Minimum Hours of Related Training required by the position', 'weight' => 10, 'percentage' => 10],
            ['description' => 'FOR POSITIONS THAT REQUIRE TRAINING: Within the Minimum Hours of Related Training required by the position', 'weight' => 10, 'percentage' => 8],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE TRAINING: With Training', 'weight' => 10, 'percentage' => 10],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE TRAINING: Without Training', 'weight' => 10, 'percentage' => 8],
        ];

        foreach ($trainingData as $data) {
            CriteriaLibTraining::updateOrCreate(
                [
                    // FIND: Use the library ID and description for a unique composite key
                    'criteria_library_id' => $criteriaLibraryId,
                    'description' => $data['description']
                ],
                // CREATE/UPDATE: The rest of the data
                $data
            );
        }

        // 5. Performance Criteria (Total Weight: 10)
        $performanceData = [
            ['description' => 'Outstanding', 'weight' => 10, 'percentage' => 10],
            ['description' => 'Very Satisfactory (VS)', 'weight' => 10, 'percentage' => 9],
            ['description' => 'Below VS or none', 'weight' => 10, 'percentage' => 8],
        ];

        foreach ($performanceData as $data) {
            CriteriaLibPerformance::updateOrCreate(
                [
                    // FIND: Use the library ID and description for a unique composite key
                    'criteria_library_id' => $criteriaLibraryId,
                    'description' => $data['description']
                ],
                // CREATE/UPDATE: The rest of the data
                $data
            );
        }

        // 6. Behavioral Criteria (Total Weight: 30)
        $behavioralData = [
            ['description' => 'Candidates\' response contained all of the target behavior/competencies', 'weight' => 30, 'percentage' => 30],
            ['description' => 'Candidates\' response contained many of the target behavior/competencies', 'weight' => 30, 'percentage' => 25],
            ['description' => 'Candidates\' response contained some of the target behavior/competencies', 'weight' => 30, 'percentage' => 20],
            ['description' => 'Candidates\' response contained very few of the target behavior/competencies', 'weight' => 30, 'percentage' => 15],
        ];

        foreach ($behavioralData as $data) {
            CriteriaLibBehavioral::updateOrCreate(
                [
                    // FIND: Use the library ID and description for a unique composite key
                    'criteria_library_id' => $criteriaLibraryId,
                    'description' => $data['description']
                ],
                // CREATE/UPDATE: The rest of the data
                $data
            );
        }
    }
}
