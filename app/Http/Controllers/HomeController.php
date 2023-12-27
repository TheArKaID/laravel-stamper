<?php

namespace App\Http\Controllers;

use Elibyy\TCPDF\Facades\TCPDF as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{
    function post(Request $request)
    {
        $data = $request->all();

        // Stamp scale is 1.5, change to 1.
        $stampX = ($data['stampX'] / 1.5);
        $stampY = ($data['stampY'] / 1.5);
        $canvasHeight = $data['canvasHeight'] / 1.5;
        $canvasWidth = $data['canvasWidth'] / 1.5;

        $qrRandCode = rand(1000, 9999);
        $qrImageString = QrCode::format('png')->generate('https://github.com/TheArKaID?' . $qrRandCode);

        $qrPath = 'TheArKa-'.$qrRandCode.'.png';
        Storage::disk('public')->put($qrPath, $qrImageString);
        $qrPath = Storage::disk('public')->path($qrPath);

        // Get stream of uploaded file
        $file = $request->file('pdf-file')->getRealPath();
        $pageCount = PDF::setSourceFile($file);

        // Loop through all pages
        for ($i=1; $i<=$pageCount; $i++) {
            $template = PDF::importPage($i);
            $size = PDF::getTemplateSize($template);

            PDF::AddPage($size['orientation'], array($size['width'], $size['height']));
            PDF::useTemplate($template);

            $widthDiffPercent = ($canvasWidth - $size['width']) / $canvasWidth * 100;
            $heightDiffPercent = ($canvasHeight - $size['height']) / $canvasHeight * 100;

            $realXPosition = $stampX - ($widthDiffPercent * $stampX / 100);
            $realYPosition = $stampY - ($heightDiffPercent * $stampY / 100);

            // Since I only want to put the QR code on the last page
            if ($i == $pageCount) {
                PDF::SetAutoPageBreak(false);
                PDF::Image($qrPath, $realXPosition, $realYPosition, 20.46, 20.46, 'PNG');
            }
        }

        // I: Show to Browser, D: Download, F: Save to File, S: Return as String
        return PDF::Output('TheArKa.pdf', 'I');
    }
}
