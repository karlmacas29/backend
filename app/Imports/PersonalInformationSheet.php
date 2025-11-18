<?php

namespace App\Imports;

use App\Models\excel\nPersonal_info;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Storage;

class PersonalInformationSheet implements WithEvents
{
    use RegistersEventListeners;

    protected $importer;
    protected $jobBatchId;
    protected $fileName;

    public function __construct($importer, $jobBatchId, $fileName)
    {
        $this->importer = $importer;
        $this->jobBatchId = $jobBatchId;
        $this->fileName = $fileName;
    }

    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        DB::beginTransaction();

        $drawings = $sheet->getDrawingCollection();
        $imagePath = null;

        try {
            foreach ($drawings as $drawing) {
                $coord = strtoupper((string)$drawing->getCoordinates());
                error_log("Found drawing at $coord (" . get_class($drawing) . ")");
                if ($coord === 'R3') {
                    $imagePath = self::extractAndSaveImage($drawing);
                    error_log("Matched R3, saved to: $imagePath");
                    break;
                }
            }



            // Rest of your data extraction code
            $date_of_birth = self::parseDate($sheet->getCell('D13')->getValue());

            $isMale = $sheet->getCell('D16')->getValue();
            $isFemale = $sheet->getCell('E16')->getValue();

            $filipino = $sheet->getCell('J13')->getValue();
            $by_birth = $sheet->getCell('J14')->getValue();
            $dual_citizenship = $sheet->getCell('L13')->getValue();
            $by_naturalization = $sheet->getCell('l14')->getValue();

            $single = $sheet->getCell('D17')->getValue();
            $married = $sheet->getCell('E17')->getValue();
            $separated = $sheet->getCell('E18')->getValue();
            $widowed = $sheet->getCell('D18')->getValue();
            $others = $sheet->getCell('D19')->getValue();

            // Determine sex
            $sex = null;
            if ($isMale === true || $isMale === 'TRUE') {
                $sex = 'Male';
            } elseif ($isFemale === true || $isFemale === 'TRUE') {
                $sex = 'Female';
            } else {
                $sex = 'prefer not to say';
            }

            // Determine citizenship status
            $citizenship_status = self::determineCitizenshipStatus($filipino, $by_birth, $dual_citizenship, $by_naturalization);

            // Determine civil status
            $civil_status = self::determineCivilStatus($single, $married, $separated, $widowed, $others);

            $data = [
                'lastname' => $sheet->getCell('D10')->getValue(),
                'firstname' => $sheet->getCell('D11')->getValue(),
                'middlename' => $sheet->getCell('D12')->getValue(),
                'name_extension' => $sheet->getCell('L11')->getValue(),
                'sex' => $sex,
                'civil_status' => $civil_status,
                'citizenship' => $citizenship_status, // Fixed typo (removed comma)
                'date_of_birth' => $date_of_birth,
                'place_of_birth' => $sheet->getCell('D15')->getValue(),
                'height' => $sheet->getCell('D21')->getValue(),
                'weight' => $sheet->getCell('D23')->getValue(),
                'blood_type' => $sheet->getCell('D24')->getValue(),
                'gsis_no' => $sheet->getCell('D26')->getValue(),
                'pagibig_no' => $sheet->getCell('D28')->getValue(),
                'philhealth_no' => $sheet->getCell('D30')->getValue(),
                'sss_no' => $sheet->getCell('D31')->getValue(),
                'tin_no' => $sheet->getCell('D32')->getValue(),
                'image_path' => $imagePath, // This will contain the saved image path

                'residential_house' => $sheet->getCell('I17')->getValue(),
                'residential_street' => $sheet->getCell('L17')->getValue(),
                'residential_subdivision' => $sheet->getCell('I19')->getValue(),
                'residential_barangay' => $sheet->getCell('L19')->getValue(),
                'residential_city' => $sheet->getCell('I21')->getValue(),
                'residential_province' => $sheet->getCell('L21')->getValue(),
                'residential_zip' => $sheet->getCell('I23')->getValue(),

                'permanent_house' => $sheet->getCell('I24')->getValue(),
                'permanent_street' => $sheet->getCell('L24')->getValue(),
                'permanent_subdivision' => $sheet->getCell('I26')->getValue(),
                'permanent_barangay' => $sheet->getCell('L26')->getValue(),
                'permanent_city' => $sheet->getCell('I28')->getValue(),
                'permanent_province' => $sheet->getCell('L28')->getValue(),
                'permanent_zip' => $sheet->getCell('I30')->getValue(),
                'excel_file' => $event->getConcernable()->importer->getFileName(),

                'telephone_number' => $sheet->getCell('I31')->getValue(),
                'cellphone_number' => $sheet->getCell('I32')->getValue(),
                'email_address' => $sheet->getCell('I33')->getValue(),
            ];

            // Validate personal info data
            $validator = Validator::make(
                $data,
                [
                    'lastname' => 'required',
                    'firstname' => 'required',
                    'email_address' => 'required|email', // ✅ Removed unique validation
                    'cellphone_number' => 'required',
                    'sex' => 'required',
                    'date_of_birth' => 'required',
                    'civil_status' => 'required',
                ],
                [
                    'lastname.required' => 'Lastname is required in Personal Information sheet.',
                    'firstname.required' => 'Firstname is required in Personal Information sheet.',
                    'email_address.required' => 'Email address is required in Personal Information sheet.',
                    'email_address.email' => 'Invalid email format in Personal Information sheet.',
                    'cellphone_number.required' => 'Cellphone number is required in Personal Information sheet.',
                    'sex.required' => 'Sex field is required in Personal Information sheet.',
                    'date_of_birth.required' => 'Date of Birth is required in Personal Information sheet.',
                    // 'date_of_birth.date' => 'Invalid Date of Birth format in Personal Information sheet.',
                    'civil_status.required' => 'Civil Status is required in Personal Information sheet.',
                ]
            );

            if ($validator->fails()) {
                // Collect readable validation errors
                $errors = $validator->errors()->all();

                // Combine into one readable message
                $errorMessage = 'Personal Information validation failed: ' . implode(' ', $errors);

                throw new \Exception($errorMessage);
            }


            // Create personal info
            $personalInfo = nPersonal_info::create($data);

            // Attach job batch after creating personal info
            $personalInfo->job_batches_rsp()->attach($event->getConcernable()->jobBatchId, [
                'status' => 'pending'
            ]);
            $event->getConcernable()->importer->setPersonalInfoId($personalInfo->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Extract and save image from drawing object
     */ private static function extractAndSaveImage($drawing)
    {
        $imageContents = null;
        $extension = null;

        error_log('Extracting image from: ' . get_class($drawing));

        try {
            if ($drawing instanceof MemoryDrawing) {
                error_log('Processing MemoryDrawing');

                // Handle MemoryDrawing (copied/pasted images)
                $resource = $drawing->getImageResource();
                if (!$resource) {
                    error_log('No image resource found in MemoryDrawing');
                    return null;
                }

                ob_start();
                $renderingFunction = $drawing->getRenderingFunction();
                error_log('Rendering function: ' . $renderingFunction);

                if ($renderingFunction && is_callable($renderingFunction)) {
                    call_user_func($renderingFunction, $resource);
                    $imageContents = ob_get_contents();
                }
                ob_end_clean();

                // Determine extension based on mime type
                switch ($drawing->getMimeType()) {
                    case MemoryDrawing::MIMETYPE_PNG:
                        $extension = 'png';
                        break;
                    case MemoryDrawing::MIMETYPE_JPEG:
                        $extension = 'jpg';
                        break;
                    case MemoryDrawing::MIMETYPE_GIF:
                        $extension = 'gif';
                        break;
                    default:
                        $extension = 'png';
                }

                error_log('MemoryDrawing processed - size: ' . strlen($imageContents) . ' bytes, extension: ' . $extension);
            } elseif ($drawing instanceof Drawing) {
                error_log('Processing Drawing (file-based)');

                $path = $drawing->getPath();
                error_log('Drawing path: ' . $path);

                if (!$path) {
                    error_log('No path found in Drawing object');
                    return null;
                }

                if ($drawing->getIsURL()) {
                    error_log('Processing URL-based image');
                    $imageContents = @file_get_contents($path);
                    if ($imageContents === false) {
                        error_log('Failed to get contents from URL: ' . $path);
                        return null;
                    }

                    // Create temp file to determine mime type
                    $tempFile = tempnam(sys_get_temp_dir(), 'drawing_');
                    file_put_contents($tempFile, $imageContents);
                    $mimeType = mime_content_type($tempFile);
                    unlink($tempFile);

                    switch ($mimeType) {
                        case 'image/jpeg':
                            $extension = 'jpg';
                            break;
                        case 'image/png':
                            $extension = 'png';
                            break;
                        case 'image/gif':
                            $extension = 'gif';
                            break;
                        default:
                            $extension = 'jpg';
                    }
                } else {
                    error_log('Processing file-based image');

                    // For Excel embedded images, the path is usually a zip entry
                    if (file_exists($path)) {
                        $imageContents = file_get_contents($path);
                    } else {
                        // Try to read as zip entry (common for Excel embedded images)
                        $imageContents = @file_get_contents($path);
                    }

                    if ($imageContents === false) {
                        error_log('Failed to read file: ' . $path);
                        return null;
                    }

                    $extension = $drawing->getExtension() ?: 'jpg';
                }

                error_log('Drawing processed - size: ' . strlen($imageContents) . ' bytes, extension: ' . $extension);
            }

            // Save the image if we successfully extracted content
            if ($imageContents && strlen($imageContents) > 0 && $extension) {
                $fileName = 'personal_info_' . uniqid() . '_' . time() . '.' . $extension;
                $imagePath = 'images/' . $fileName;

                // Ensure the directory exists
                if (!Storage::disk('public')->exists('images')) {
                    Storage::disk('public')->makeDirectory('images');
                }

                // Save to storage
                $saved = Storage::disk('public')->put($imagePath, $imageContents);

                if ($saved) {
                    error_log('Image saved successfully to: ' . $imagePath);
                    return $imagePath;
                } else {
                    error_log('Failed to save image to storage');
                }
            } else {
                error_log('Invalid image data - Contents: ' . (strlen($imageContents ?? '') . ' bytes, Extension: ' . ($extension ?: 'none')));
            }
        } catch (\Exception $e) {
            error_log('Exception in extractAndSaveImage: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Helper function to check if checkbox is checked
     */
    private static function isChecked($value)
    {
        return $value === true || strtoupper($value) === 'TRUE';
    }

    /**
     * Determine citizenship status based on checkbox values
     */
    private static function determineCitizenshipStatus($filipino, $by_birth, $dual_citizenship, $by_naturalization)
    {
        if (self::isChecked($filipino)) {
            return 'Filipino';
        } elseif (self::isChecked($by_birth)) {
            return 'By Birth';
        } elseif (self::isChecked($dual_citizenship)) {
            return 'Dual Citizenship';
        } elseif (self::isChecked($by_naturalization)) {
            return 'By Naturalization';
        }

        return 'Unknown/Unspecified';
    }

    /**
     * Determine civil status based on checkbox values
     */
    private static function determineCivilStatus($single, $married, $separated, $widowed, $others)
    {
        if (self::isChecked($single)) {
            return 'Single';
        } elseif (self::isChecked($married)) {
            return 'Married';
        } elseif (self::isChecked($separated)) {
            return 'Separated';
        } elseif (self::isChecked($widowed)) {
            return 'Widowed';
        } elseif (self::isChecked($others)) {
            return 'Others';
        }

        return null;
    }

    /**
     * Parse date from various formats
     */
    private static function parseDate($value, $allowYearOnly = false)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                // Excel numeric date
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } else {
                $date = \Carbon\Carbon::parse($value);
                return $allowYearOnly ? $date->format('Y') : $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$value}");
        }
    }
}
