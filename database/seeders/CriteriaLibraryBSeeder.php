<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\library\CriteriaLibrary;
use App\Models\library\CriteriaLibEducation;
use App\Models\library\CriteriaLibExperience;
use App\Models\library\CriteriaLibTraining;
use App\Models\library\CriteriaLibPerformance;
use App\Models\library\CriteriaLibBehavioral;
use App\Models\library\CriteriaLibOther; // Include the new 'Other' model

class CriteriaLibraryBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ----------------------------------------------------------------------
        // CRITERIA SET 1: SG 18-22 (Matches image exactly - NO 'OTHER' CRITERIA)
        // ----------------------------------------------------------------------

        $criteriaLibrary1 = CriteriaLibrary::updateOrCreate([
            'sg_min' => 18,
            'sg_max' => 22,
        ]);
        $id1 = $criteriaLibrary1->id;

        // 1. Education Criteria (20%)
        $educationData = [
            ['description' => 'Completion of Relevant Graduate Studies', 'weight' => 20, 'percentage' => 20],
            ['description' => 'With More than 30 units of Relevant Graduate Studies', 'weight' => 20, 'percentage' => 18],
            ['description' => 'Completion of Bachelor\'s Degree and/or Minimum Educational Requirement of the Position', 'weight' => 20, 'percentage' => 15],
        ];
        foreach ($educationData as $data) {
            CriteriaLibEducation::updateOrCreate(array_merge($data, ['criteria_library_id' => $id1]));
        }

        // 2. Experience Criteria (30%)
        $experienceData1 = [
            ['description' => '500% and above of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 30],
            ['description' => '400%-499% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 28],
            ['description' => '300%-399% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 26],
            ['description' => '200%-299% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 24],
            ['description' => '101%-199% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 22],
            ['description' => 'Minimum Number of Years of Relevant Experience required by the position', 'weight' => 30, 'percentage' => 20],
        ];
        foreach ($experienceData1 as $data) {
            CriteriaLibExperience::updateOrCreate(array_merge($data, ['criteria_library_id' => $id1]));
        }

        // 3. Training Criteria (10%)
        $trainingData = [
            ['description' => 'FOR POSITIONS THAT REQUIRE TRAINING: More than the Minimum Hours of Related Training required by the position', 'weight' => 10, 'percentage' => 10],
            ['description' => 'FOR POSITIONS THAT REQUIRE TRAINING: Within the Minimum Hours of Related Training required by the position', 'weight' => 10, 'percentage' => 8],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE TRAINING: With Training', 'weight' => 10, 'percentage' => 10],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE TRAINING: Without Training', 'weight' => 10, 'percentage' => 8],
        ];
        foreach ($trainingData as $data) {
            CriteriaLibTraining::updateOrCreate(array_merge($data, ['criteria_library_id' => $id1]));
        }

        // 4. Performance Criteria (10%)
        $performanceData = [
            ['description' => 'Outstanding', 'weight' => 10, 'percentage' => 10],
            ['description' => 'Very Satisfactory (VS)', 'weight' => 10, 'percentage' => 9],
            ['description' => 'Below VS or none', 'weight' => 10, 'percentage' => 8],
        ];
        foreach ($performanceData as $data) {
            CriteriaLibPerformance::updateOrCreate(array_merge($data, ['criteria_library_id' => $id1]));
        }

        // 5. Behavioral Criteria (30%)
        $behavioralData = [
            ['description' => 'Candidates\' response contained all of the target behavior/competencies', 'weight' => 30, 'percentage' => 30],
            ['description' => 'Candidates\' response contained many of the target behavior/competencies', 'weight' => 30, 'percentage' => 25],
            ['description' => 'Candidates\' response contained some of the target behavior/competencies', 'weight' => 30, 'percentage' => 20],
            ['description' => 'Candidates\' response contained very few of the target behavior/competencies', 'weight' => 30, 'percentage' => 15],
        ];
        foreach ($behavioralData as $data) {
            CriteriaLibBehavioral::updateOrCreate(array_merge($data, ['criteria_library_id' => $id1]));
        }

}
}
