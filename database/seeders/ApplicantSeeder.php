<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\excel\nPersonal_info;
use App\Models\JobBatchesRsp;
use App\Models\Submission;

class ApplicantSeeder extends Seeder
{
    public function run()
    {
        nPersonal_info::factory(500)
            ->create()
            ->each(function ($applicant) {

                // Create submission for this applicant
                Submission::create([
                    'nPersonalInfo_id' => $applicant->id,
                    'job_batches_rsp_id' => 1,
                    'education_remark' => null,
                    'experience_remark' => null,
                    'training_remark' => null,
                    'eligibility_remark' => null,
                    'status' => 'pending',
                    'ControlNo' => null,
                ]);


                // Optionally, you can create related models (family, children, skills, etc.)
            $applicant->family()->create(\Database\Factories\FamilyFactory::new()->make()->toArray());
            $applicant->children()->saveMany(\Database\Factories\ChildrenFactory::new()->count(2)->make());
            $applicant->education()->saveMany(\Database\Factories\EducationBackgroundFactory::new()->count(3)->make());
            $applicant->eligibity()->saveMany(\Database\Factories\CivilServiceEligibityFactory::new()->count(2)->make());
            $applicant->work_experience()->saveMany(\Database\Factories\WorkExperienceFactory::new()->count(2)->make());
            $applicant->voluntary_work()->saveMany(\Database\Factories\VoluntaryWorkFactory::new()->count(2)->make());
            $applicant->training()->saveMany(\Database\Factories\LearningDevelopmentFactory::new()->count(2)->make());
            $applicant->personal_declarations()->saveMany(\Database\Factories\PersonalDeclarationsFactory::new()->count(1)->make());
            $applicant->skills()->saveMany(\Database\Factories\SkillNonAcademicFactory::new()->count(3)->make());
            $applicant->references()->saveMany(\Database\Factories\referencesFactory::new()->count(2)->make());
        });
    }
}
