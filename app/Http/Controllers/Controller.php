<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function deleteImage($image)
    {
        if(File::exists($image))
        {
            File::delete($image);
        }
        return true;
    }

    protected function getPdfFileName($fileName)
    {
        $filePath = 'pdf_documents/' . $fileName . '/' . date('Y') . '/' . date('m') . '/';
        $folderPath = public_path('/') . $filePath;
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        return  $filePath .  date('H') . '-' . date('i') . '-' . date('s') . '-' . time() . '.pdf';
    }

    protected function getExcelFileName($fileName)
    {
        $filePath = 'excel_documents/' . $fileName . '/' . date('Y') . '/' . date('m') . '/';
        $folderPath = $filePath;
        // if (!file_exists($folderPath)) {
        //     mkdir($folderPath, 0777, true);
        // }
        return  $filePath .  date('H') . '-' . date('i') . '-' . date('s') . '-' . time() . '.xlsx';
    }
}
