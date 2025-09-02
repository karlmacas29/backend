<?php

namespace App\Swagger;


/**
 * @OA\Get(
 *     path="/api/scores/{applicant}",
 *     tags={"Jobpost Result"},
 *     summary="Get applicant's score history and final computed score with rank",
 *     description="Fetch all scores belonging to a specific applicant, including raw history and computed final score with ranking per job post.",
 *     @OA\Parameter(
 *         name="applicant",
 *         in="path",
 *         required=true,
 *         description="Applicant ID",
 *         @OA\Schema(type="integer", example=34)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Applicant history and final scores",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="applicant_id", type="integer", example=34),
 *             @OA\Property(property="firstname", type="string", example="Deniel"),
 *             @OA\Property(property="lastname", type="string", example="Tomenio"),
 *             @OA\Property(
 *                 property="history",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=21),
 *                     @OA\Property(property="job_batches_rsp_id", type="integer", example=40061),
 *                     @OA\Property(property="education", type="string", example="21"),
 *                     @OA\Property(property="experience", type="string", example="12.00"),
 *                     @OA\Property(property="training", type="string", example="12.00"),
 *                     @OA\Property(property="performance", type="string", example="10.00"),
 *                     @OA\Property(property="bei", type="string", example="12.00"),
 *                     @OA\Property(property="total_qs", type="string", example="55.00"),
 *                     @OA\Property(property="grand_total", type="string", example="67.00"),
 *                     @OA\Property(property="ranking", type="string", example="3")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="final_scores",
 *                 type="object",
 *                 @OA\AdditionalProperties(
 *                     type="object",
 *                     @OA\Property(property="firstname", type="string", example="Deniel"),
 *                     @OA\Property(property="lastname", type="string", example="Tomenio"),
 *                     @OA\Property(property="education", type="string", example="23.00"),
 *                     @OA\Property(property="experience", type="string", example="18.50"),
 *                     @OA\Property(property="training", type="string", example="13.50"),
 *                     @OA\Property(property="performance", type="string", example="9.00"),
 *                     @OA\Property(property="bei", type="string", example="18.00"),
 *                     @OA\Property(property="total_qs", type="string", example="64.00"),
 *                     @OA\Property(property="grand_total", type="string", example="82.00"),
 *                     @OA\Property(property="nPersonalInfo_id", type="string", example="34"),
 *                     @OA\Property(property="job_batches_rsp_id", type="string", example="40061"),
 *                     @OA\Property(property="rank", type="integer", example=1)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No scores found for this applicant",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No scores found for this applicant")
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/show/{jobpostId}",
 *     tags={"Scores"},
 *     summary="Fetch all applicants' final scores with rank for a job post",
 *     description="Returns applicants' scores, computed final score, and rank for a specific job post.",
 *     @OA\Parameter(
 *         name="jobpostId",
 *         in="path",
 *         required=true,
 *         description="The ID of the job post",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Applicants' ranked scores",
 *         @OA\JsonContent(
 *             type="object",
 *             additionalProperties=@OA\Property(
 *                 type="object",
 *                 @OA\Property(property="firstname", type="string", example="Juan"),
 *                 @OA\Property(property="lastname", type="string", example="Dela Cruz"),
 *                 @OA\Property(property="final_score", type="number", format="float", example=85.75),
 *                 @OA\Property(property="rank", type="integer", example=1),
 *                 @OA\Property(property="nPersonalInfo_id", type="integer", example=12),
 *                 @OA\Property(property="job_batches_rsp_id", type="integer", example=3),
 *                 @OA\Property(property="education", type="number", format="float", example=20),
 *                 @OA\Property(property="experience", type="number", format="float", example=25),
 *                 @OA\Property(property="training", type="number", format="float", example=15),
 *                 @OA\Property(property="performance", type="number", format="float", example=20),
 *                 @OA\Property(property="bei", type="number", format="float", example=5)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Job post not found")
 * )
 *
 */


class  RaterSwagger {}
