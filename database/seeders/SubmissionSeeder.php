<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\excel\nPersonal_info;
use App\Models\excel\Children;
use App\Models\excel\Civil_service_eligibity;
use App\Models\excel\Education_background;
use App\Models\excel\Learning_development;
use App\Models\excel\nFamily;
use App\Models\excel\Personal_declarations;
use App\Models\excel\references;
use App\Models\excel\skill_non_academic;
use App\Models\excel\Voluntary_work;
use App\Models\excel\Work_experience;
use App\Models\Submission;

class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $personalInfos = nPersonal_info::factory()->count(20)->create();

        foreach ($personalInfos as $personalInfo) {
            Children::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Civil_service_eligibity::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Education_background::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Learning_development::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            nFamily::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Personal_declarations::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            references::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            skill_non_academic::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Voluntary_work::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);
            Work_experience::factory()->create(['nPersonalInfo_id' => $personalInfo->id]);

            Submission::create([
                'nPersonalInfo_id' => $personalInfo->id,
                'job_batches_rsp_id' => 40062,
                'education_remark' => 'Education remark for ' . $personalInfo->firstname,
                'experience_remark' => 'Experience remark for ' . $personalInfo->firstname,
                'training_remark' => 'Training remark for ' . $personalInfo->firstname,
                'eligibility_remark' => 'Eligibility remark for ' . $personalInfo->firstname,
                'status' => 'pending',
            ]);
        }

        $this->command->info('✅ SubmissionSeeder completed successfully!');
    }
}
