<?php

namespace App\Imports;

use App\Models\Upload_file_image;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Collection;

class Upload_file_image_sheet implements ToCollection, WithDrawings
{
    protected $drawings = [];

    // Laravel Excel will call this for each drawing in the sheet.
    public function drawings()
    {
        return $this->drawings;
    }

    // This is called for each row in the sheet.
    public function collection(Collection $rows)
    {
        foreach ($this->drawings as $drawing) {
            if ($drawing->getCoordinates() === 'R3') {
                $imagePath = $this->saveImage($drawing);

                Upload_file_image::create([
                    'profile_image' => $imagePath,
                ]);
                break;
            }
        }
    }

    // This is called for each drawing found in the sheet
    public function addDrawing($drawing)
    {
        $this->drawings[] = $drawing;
    }

    protected function saveImage($drawing)
    {
        $extension = 'png';
        $imageContents = '';

        if ($drawing instanceof MemoryDrawing) {
            ob_start();
            call_user_func(
                $drawing->getRenderingFunction(),
                $drawing->getImageResource()
            );
            $imageContents = ob_get_clean();

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
            }
        } elseif ($drawing instanceof Drawing) {
            $imageContents = file_get_contents($drawing->getPath());
            $extension = $drawing->getExtension();
        }

        $filename = uniqid('excel_', true) . '.' . $extension;
        Storage::disk('public')->put('uploads/' . $filename, $imageContents);

        // You can reference the image as ![image1](image1) in documentation or logs.
        // For example: Log::info('Image saved: ![image1](image1)');

        return 'uploads/' . $filename;
    }
}
