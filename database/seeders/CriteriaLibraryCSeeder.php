<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\library\CriteriaLibrary;
use App\Models\library\CriteriaLibEducation;
use App\Models\library\CriteriaLibExperience;
use App\Models\library\CriteriaLibTraining;
use App\Models\library\CriteriaLibPerformance;
use App\Models\library\CriteriaLibBehavioral;
use App\Models\library\CriteriaLibOther;

class CriteriaLibraryCSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ----------------------------------------------------------------------
        // CRITERIA SET 1: SG 3-17 (New data from the latest image)
        // Weight breakdown: Education(20), Experience(20), Training(15), Performance(15), Behavioral(30) = 100%
        // ----------------------------------------------------------------------
        $criteriaLibrary = CriteriaLibrary::updateOrCreate(
            ['sg_min' => 3, 'sg_max' => 17],
        );
        $id = $criteriaLibrary->id;

        // 1.1. Education Criteria (20%)
        $educationData = [
            ['description' => 'Completion of Bachelor\'s Degree and/or Minimum Educational Requirement of the Position', 'weight' => 20, 'percentage' => 20],
        ];
        foreach ($educationData as $data) {
            CriteriaLibEducation::updateOrCreate(
                ['criteria_library_id' => $id, 'description' => $data['description']],
                $data
            );
        }

        // 1.2. Experience Criteria (20%)
        $experienceData = [
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: 500% and above of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 20],
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: 400%-499% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 19],
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: 300%-399% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 18],
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: 200%-299% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 17],
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: 101%-199% of the Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 16],
            ['description' => 'FOR POSITIONS THAT REQUIRE EXPERIENCE: Minimum Number of Years of Relevant Experience required by the position', 'weight' => 20, 'percentage' => 15],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE EXPERIENCE: With Experience (with Acting Capacity covered with Office Order or Certification from the Dept. Head)', 'weight' => 20, 'percentage' => 20],
            ['description' => 'FOR POSITIONS THAT DO NOT REQUIRE EXPERIENCE: Without Experience', 'weight' => 20, 'percentage' => 18],
        ];
        foreach ($experienceData as $data) {
            CriteriaLibExperience::updateOrCreate(
                ['criteria_library_id' => $id, 'description' => $data['description']],
                $data
            );
        }

        // 1.3. Training Criteria (15%)
        $trainingData = [
            ['description' => 'Within the Minimum Hours of Related Training to the position or the minimum requirement of the position', 'weight' => 15, 'percentage' => 15],
        ];
        foreach ($trainingData as $data) {
            CriteriaLibTraining::updateOrCreate(
                ['criteria_library_id' => $id, 'description' => $data['description']],
                $data
            );
        }

        // 1.4. Performance Criteria (15%)
        $performanceData = [
            ['description' => 'Outstanding', 'weight' => 15, 'percentage' => 15],
            ['description' => 'Very Satisfactory (VS)', 'weight' => 15, 'percentage' => 14],
            ['description' => 'Below VS or none', 'weight' => 15, 'percentage' => 13],
        ];
        foreach ($performanceData as $data) {
            CriteriaLibPerformance::updateOrCreate(
                ['criteria_library_id' => $id, 'description' => $data['description']],
                $data
            );
        }

        // 1.5. Behavioral Criteria (30%) - Data reused from previous set
        $behavioralData = [
            ['description' => 'Candidates\' response contained all of the target behavior/competencies', 'weight' => 30, 'percentage' => 30],
            ['description' => 'Candidates\' response contained many of the target behavior/competencies', 'weight' => 30, 'percentage' => 25],
            ['description' => 'Candidates\' response contained some of the target behavior/competencies', 'weight' => 30, 'percentage' => 20],
            ['description' => 'Candidates\' response contained very few of the target behavior/competencies', 'weight' => 30, 'percentage' => 15],
        ];
        foreach ($behavioralData as $data) {
            CriteriaLibBehavioral::updateOrCreate(
                ['criteria_library_id' => $id, 'description' => $data['description']],
                $data
            );
        }



    }

}
