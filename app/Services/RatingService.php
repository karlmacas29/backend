<?php

namespace App\Services;

class RatingService
{
    public $education;
    public $experience;
    public $training;
    public $performance;
    public $bei;

    public function __construct($education = 0, $experience = 0, $training = 0, $performance = 0, $bei = 0)
    {
        $this->education   = $education;
        $this->experience  = $experience;
        $this->training    = $training;
        $this->performance = $performance;
        $this->bei         = $bei;
    }

    /**
     * Compute the final score for an applicant.
     *
     * @param array $scores Array of rating arrays.
     * @return array
     */
    public static function computeFinalScore(array $scores)
    {
        $count = count($scores);

        if ($count === 0) {
            return [
                'education'   => "0.00",
                'experience'  => "0.00",
                'training'    => "0.00",
                'performance' => "0.00",
                'bei'         => "0.00",
                'total_qs'    => "0.00",
                'grand_total' => "0.00",
            ];
        }

        // Dynamically divide based on number of results
        $education   = array_sum(array_column($scores, 'education')) / $count;
        $experience  = array_sum(array_column($scores, 'experience')) / $count;
        $training    = array_sum(array_column($scores, 'training')) / $count;
        $performance = array_sum(array_column($scores, 'performance')) / $count;
        $bei         = array_sum(array_column($scores, 'bei')) / $count;

        $total_qs    = $education + $experience + $training + $performance;
        $grand_total = $total_qs + $bei;

        return [
            'education'   => number_format($education, 2, '.', ''),
            'experience'  => number_format($experience, 2, '.', ''),
            'training'    => number_format($training, 2, '.', ''),
            'performance' => number_format($performance, 2, '.', ''),
            'bei'         => number_format($bei, 2, '.', ''),
            'total_qs'    => number_format($total_qs, 2, '.', ''),
            'grand_total' => number_format($grand_total, 2, '.', ''),
        ];
    }
    
    public static function addRanking(array $applicants)
    {
        // Sort applicants by grand_total (descending)
        uasort($applicants, function ($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });

        $rank = 1;
        $previousScore = null;
        $sameRankCount = 0;

        foreach ($applicants as $id => &$data) {
            if ($previousScore !== null && $data['grand_total'] == $previousScore) {
                // Same score = same rank
                $data['rank'] = $rank;
                $sameRankCount++;
            } else {
                // New score, adjust rank considering ties
                $rank += $sameRankCount;
                $sameRankCount = 1;
                $data['rank'] = $rank;
            }
            $previousScore = $data['grand_total'];
        }

        return $applicants;
    }
}
