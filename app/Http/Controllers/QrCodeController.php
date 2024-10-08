<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Svg\Converter;
use Spatie\Browsershot\Browsershot;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Imagick;
use TCPDF;
use FPDF;
use View;
use DB;
use File;
use Auth;

class QrCodeController extends Controller
{
    // public function index()
    // {

    //   $fileId = DB::table('files')
    //     ->join('requestingform','requestingform.research_id','files.id')
    //     ->select('requestingform.*','files.*')
    //     ->where('requestingform.id', 21)
    //     ->first();

    //   $certificateReCount = DB::table('certificates')->count();
    //   $certificateCount = ++$certificateReCount;

    //   $currentYearMonth = date('Ym');

    //   if ($certificateCount >= 1 && $certificateCount <= 9) {
    //     $qrCodeName = $currentYearMonth . 0 . 0 . 0 . $certificateCount;
    //   } else if ($certificateCount >= 10 && $certificateCount <= 99) {
    //     $qrCodeName = $currentYearMonth . 0 . 0 . $certificateCount;
    //   } else if ($certificateCount >= 100 && $certificateCount <= 999) {
    //     $qrCodeName = $currentYearMonth . 0 . $certificateCount;
    //   } else if ($certificateCount >= 1000) {
    //     $qrCodeName = $currentYearMonth . $certificateCount;
    //   }

    //   // $qrCodePath = public_path("uploads/certificate/image/{$qrCodeName}.png");
    //   // QrCode::format('png')->size(300)->generate($qrCodeName, $qrCodePath);

    //   $existingPdfPath = public_path("uploads/certificate/CertificateFormat.pdf");

    //   $pdf = new Fpdi();
    //   $pageCount = $pdf->setSourceFile($existingPdfPath);

    //   for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
    //       $pdf->AddPage();

    //       $templateId = $pdf->importPage($pageNumber);
    //       $pdf->useTemplate($templateId);
     
    //       $pdf->SetFont('Arial', '', 12);
    //       $pdf->SetXY(10, 65); 
    //       $pdf->MultiCell(0, 10, ' This is to certify that the manuscript entitled ', 0, 'C');

    //       $pdf->SetFont('Arial', 'B', 12);
    //       $pdf->SetXY(10, 75); 
    //       $pdf->MultiCell(0, 10, 'trysadbdfbd', 0, 'C');

    //       $pdf->SetFont('Arial', '', 12);
    //       $pdf->SetXY(10, 115); 
    //       $pdf->MultiCell(0, 10, ' authored by ', 0, 'C');

    //       $pdf->SetFont('Arial', 'B', 12);
    //       $pdf->SetXY(10, 130); 
    //       $pdf->MultiCell(0, 10, 'sgadgsd', 0, 'C');
    //       $pdf->SetXY(10, 135); 
    //       $pdf->MultiCell(0, 10, 'sgsadhdfn', 0, 'C');
    //       $pdf->SetXY(10, 140); 
    //       $pdf->MultiCell(0, 10, 'gsadgsfdb', 0, 'C');

    //       $pdf->SetFont('Arial', '', 12);
    //       $pdf->SetXY(10, 150); 
    //       $pdf->MultiCell(0, 5, 'has been subjected to similarity check on ' . 23634 . 
    //       ' using Turnitin with generated similarity index of ' . 4643574 . '%', 0, 'C');
    //       $pdf->SetXY(10, 170); 
    //       $pdf->MultiCell(0, 10, ' Processed by: ', 0, 'C');

    //       $pdf->SetFont('Arial', 'B', 12);
    //       $pdf->SetXY(10, 180); 
    //       $pdf->MultiCell(0, 10, 'sfafagssdf', 0, 'C');

    //       $pdf->SetFont('Arial', 'I', 12);
    //       $pdf->SetXY(10, 185); 
    //       $pdf->MultiCell(0, 10, ' Head of Research & Development Services ', 0, 'C');

    //       $pdf->SetFont('Arial', '', 12);
    //       $pdf->SetXY(10, 200); 
    //       $pdf->MultiCell(0, 10, ' Certified Correct by: ', 0, 'C');

    //       $pdf->SetFont('Arial', 'B', 12);
    //       $pdf->SetXY(10, 210); 
    //       $pdf->MultiCell(0, 10, ' Laarnie D. Macapagal, DMS ', 0, 'C');

    //       $pdf->SetFont('Arial', 'I', 12);
    //       $pdf->SetXY(10, 215); 
    //       $pdf->MultiCell(0, 10, ' Assistant Director of Research & Extension Services ', 0, 'C');

    //       // $pdf->Image($qrCodePath, 18, 230, 20, 0, 'PNG');
    //       // $pdf->SetFont('Arial', 'I', 11);
    //       // $pdf->SetXY(16, 248); 
    //       // $pdf->MultiCell(0, 10, $qrCodeName, 0, 0);
        
    //   }

    //   $pdf->Output('F', public_path("uploads/certificate/pdf/{$qrCodeName}.pdf"));

    //   // return response()->json(['message' => 'PDF saved successfully']);
    // }

    public function index()
{
    $fileId = DB::table('files')
        ->join('requestingform', 'requestingform.research_id', 'files.id')
        ->select('requestingform.*', 'files.*')
        ->where('requestingform.id', 21)
        ->first();

    $certificateReCount = DB::table('certificates')->count();
    $certificateCount = ++$certificateReCount;

    $currentYearMonth = date('Ym');

    if ($certificateCount >= 1 && $certificateCount <= 9) {
        $qrCodeName = $currentYearMonth . 0 . 0 . 0 . $certificateCount;
    } else if ($certificateCount >= 10 && $certificateCount <= 99) {
        $qrCodeName = $currentYearMonth . 0 . 0 . $certificateCount;
    } else if ($certificateCount >= 100 && $certificateCount <= 999) {
        $qrCodeName = $currentYearMonth . 0 . $certificateCount;
    } else if ($certificateCount >= 1000) {
        $qrCodeName = $currentYearMonth . $certificateCount;
    }

    // Generate the QR code image
    // $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$qrCodeName}";

    $certificate = 'https://reachtupt.online/certificate/' . $qrCodeName;
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$certificate}";
    $qrCodePath = public_path("uploads/certificate/image/{$qrCodeName}.png");
    file_put_contents($qrCodePath, file_get_contents($qrCodeUrl));

    $existingPdfPath = public_path("uploads/certificate/CertificateFormat.pdf");

    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($existingPdfPath);

    for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
        $pdf->AddPage();

        $templateId = $pdf->importPage($pageNumber);
        $pdf->useTemplate($templateId);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 65); 
        $pdf->MultiCell(0, 10, ' This is to certify that the manuscript entitled ', 0, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 75); 
        $pdf->MultiCell(0, 10, 'trysadbdfbd', 0, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 115); 
        $pdf->MultiCell(0, 10, ' authored by ', 0, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 130); 
        $pdf->MultiCell(0, 10, 'sgadgsd', 0, 'C');
        $pdf->SetXY(10, 135); 
        $pdf->MultiCell(0, 10, 'sgsadhdfn', 0, 'C');
        $pdf->SetXY(10, 140); 
        $pdf->MultiCell(0, 10, 'gsadgsfdb', 0, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 150); 
        $pdf->MultiCell(0, 5, 'has been subjected to similarity check on ' . 23634 . 
        ' using Turnitin with generated similarity index of ' . 4643574 . '%', 0, 'C');
        $pdf->SetXY(10, 170); 
        $pdf->MultiCell(0, 10, ' Processed by: ', 0, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 180); 
        $pdf->MultiCell(0, 10, 'sfafagssdf', 0, 'C');

        $pdf->SetFont('Arial', 'I', 12);
        $pdf->SetXY(10, 185); 
        $pdf->MultiCell(0, 10, ' Head of Research & Development Services ', 0, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(10, 200); 
        $pdf->MultiCell(0, 10, ' Certified Correct by: ', 0, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 210); 
        $pdf->MultiCell(0, 10, ' Laarnie D. Macapagal, DMS ', 0, 'C');

        $pdf->SetFont('Arial', 'I', 12);
        $pdf->SetXY(10, 215); 
        $pdf->MultiCell(0, 10, ' Assistant Director of Research & Extension Services ', 0, 'C');

        // Insert the QR code image into the PDF
        $pdf->Image($qrCodePath, 18, 230, 20, 0, 'PNG');
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetXY(16, 248); 
        $pdf->MultiCell(0, 10, $qrCodeName, 0, 0);
    }

    $pdf->Output('F', public_path("uploads/certificate/pdf/{$qrCodeName}.pdf"));

    // return response()->json(['message' => 'PDF saved successfully']);
}


    public function landingPage($control_id)
    {
        $admin = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();
    
        $student = DB::table('students')
            ->join('users','users.id','students.user_id')
            ->select('students.*','users.*')
            ->where('user_id',Auth::id())
            ->first();
        
        $staff = DB::table('staff')
            ->join('users','users.id','staff.user_id')
            ->select('staff.*','users.*')
            ->where('user_id',Auth::id())
            ->first();
    
        $faculty = DB::table('faculty')
            ->join('users','users.id','faculty.user_id')
            ->select('faculty.*','users.*')
            ->where('user_id',Auth::id())
            ->first();

        $certificate = DB::table('requestingform')
            ->join('certificates', 'certificates.id', 'requestingform.certificate_id')
            ->join('files', 'files.id', 'requestingform.research_id')
            ->select('requestingform.*', 'certificates.*','files.*')
            ->where('certificates.control_id', $control_id)
            ->first();
    
        return View::make('certificate.landingPage',compact('admin','student','staff','faculty','certificate'));
    }

    public function generateQRCodeAndSave()
    {
        // Generate QR code content
        $qrCodeContent = 'Your QR code content here';

        // Create a QR code writer
        $renderer = new Png();
        $renderer->setHeight(200);
        $renderer->setWidth(200);
        $writer = new Writer($renderer);

        // Generate QR code image
        $imageData = $writer->writeString($qrCodeContent);

        // Save QR code image to storage
        $imageName = 'qrcode_' . time() . '.png';
        Storage::put('public/qrcodes/' . $imageName, $imageData);

        // Return the image path or do further processing
        return 'public/qrcodes/' . $imageName;
    }

}
