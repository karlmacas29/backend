<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class xPDSController extends Controller
{
    public function getPersonalDataSheet(Request $request)
    {
        // Validate the request
        $request->validate([
            'controlno' => 'required|string'
        ]);

        $controlNo = $request->input('controlno');

        try {
            // Fetch data from all related tables
            $data = [
                'controlno' => $controlNo,
                'User' => $this->getCombinedUserData($controlNo),
                'Education' => $this->getEducationData($controlNo),
                'Eligibility' => $this->getEligibilityData($controlNo),
                'Experience' => $this->getExperienceData($controlNo),
                'Voluntary' => $this->getVoluntaryData($controlNo),
                'Training' => $this->getTrainingData($controlNo),
                'Skills' => $this->getSkillsData($controlNo),
                'Academic' => $this->getAcademicData($controlNo),
                'Organization' => $this->getOrganizationData($controlNo),
                'Reference' => $this->getReferenceData($controlNo),
            ];

            return Response::json($data, 200);

        } catch (\Exception $e) {
            return Response::json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // private function getCombinedUserData($controlNo)
    // {
    //     // Get base personal data
    //     $personalData = $this->getUserData($controlNo);

    //     // If no personal data found, return empty array
    //     if (empty($personalData)) {
    //         return [];
    //     }

    //     // Get additional data from other tables
    //     $pwdData = $this->getPWDData($controlNo);
    //     $personalAddtData = $this->getPersonalAddtData($controlNo);
    //     $personalDiversityData = $this->getPersonalDiversityData($controlNo);
    //     $childrenData = $this->getChildrenData($controlNo);

    //     // Convert first user record to array if it's an object
    //     $userArray = is_object($personalData[0]) ? json_decode(json_encode($personalData[0]), true) : $personalData[0];

    //     // Merge all additional data into the user array
    //     if (!empty($pwdData)) {
    //         $userArray = array_merge($userArray, $this->convertToArray($pwdData));
    //     }

    //     if (!empty($personalAddtData)) {
    //         $userArray = array_merge($userArray, $this->convertToArray($personalAddtData));
    //     }

    //     if (!empty($personalDiversityData)) {
    //         $userArray = array_merge($userArray, $this->convertToArray($personalDiversityData));
    //     }

    //     // Add children data
    //     $userArray['children'] = $childrenData;

    //     return [$userArray];
    // }

    private function getCombinedUserData($controlNo)
    {
        // Fetch all user-related data in one query using joins
        $user = DB::connection('sqlsrv')
            ->table('xPersonal')
            ->leftJoin('xPWD', 'xPersonal.ControlNo', '=', 'xPWD.ControlNo')
            ->leftJoin('xPersonalAddt', 'xPersonal.ControlNo', '=', 'xPersonalAddt.ControlNo')
            ->leftJoin('xPersonalDiversity', 'xPersonal.ControlNo', '=', 'xPersonalDiversity.ControlNo')
            ->where('xPersonal.ControlNo', $controlNo)
            ->select([
                'xPersonal.*',
                'xPWD.*',
                'xPersonalAddt.*',
                'xPersonalDiversity.*'
            ])
            ->first();

        if (!$user) {
            return [];
        }

        $userArray = (array)$user;

        // Children are multiple records, so fetch separately
        $children = DB::connection('sqlsrv')
            ->table('xChildren')
            ->where('ControlNo', $controlNo)
            ->select('ChildName', 'BirthDate', 'PMID')
            ->get()
            ->toArray();

        $userArray['children'] = $children;

        return [$userArray];
    }


    private function getUserData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xPersonal')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getPWDData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xPWD')
            ->where('ControlNo', $controlNo)
            ->first();

        return $result;
    }

    private function getPersonalAddtData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xPersonalAddt')
            ->where('ControlNo', $controlNo)
            ->first();

        return $result;
    }

    private function getPersonalDiversityData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xPersonalDiversity')
            ->where('ControlNo', $controlNo)
            ->first();

        return $result;
    }

    private function getChildrenData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xChildren')
            ->where('ControlNo', $controlNo)
            ->select('ChildName', 'BirthDate', 'PMID')
            ->get();

        return $this->convertToArray($result);
    }

    private function getEducationData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xEducation')
            ->where('ControlNo', $controlNo)
            ->orderBy('Orders')
            ->get();

        return $this->convertToArray($result);
    }

    private function getEligibilityData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xCivilService')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getExperienceData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xExperience')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getVoluntaryData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xNGO')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getTrainingData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xTrainings')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getSkillsData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xSkills')
            ->where('ControlNo', $controlNo)
            ->select('ID', 'ControlNo', 'Skills')
            ->get();

        return $this->convertToArray($result);
    }

    private function getAcademicData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xNonAcademic')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getOrganizationData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xOrganization')
            ->where('ControlNo', $controlNo)
            ->get();

        return $this->convertToArray($result);
    }

    private function getReferenceData($controlNo)
    {
        $result = DB::connection('sqlsrv')
            ->table('xReference')
            ->where('ControlNo', $controlNo)
            ->select('ControlNo', 'Names', 'Address', 'TelNo', 'PMID')
            ->get();

        return $this->convertToArray($result);
    }

    /**
     * Convert Laravel Collection or stdClass to array
     */
    private function convertToArray($data)
    {
        return json_decode(json_encode($data), true);
    }
}
