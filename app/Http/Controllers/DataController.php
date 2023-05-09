<?php

namespace App\Http\Controllers;

use App\Http\Requests\Data\InnInfoRequest;
use App\Mail\AssistMe;
use App\Mail\OrderPayment;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DataController extends Controller
{
    use ApiResponser;

    public function innInfo(InnInfoRequest $request): JsonResponse
    {
        $data = $request->validated();

        $parse =  (new OrderService(null))->parseInn($data['search']);

        return $this->sendResponse($parse);
    }

    public function testPdf()
    {
        $pdf = PDF::loadView('mails.orders.pdf-assist', ['number' => '202023125,11', 'name' => 'Иванов иван иваныч', 'duration' => '12 мес.', 'price' => 200.00], [], 'UTF-8');
        $pdf->setOption([
            'defaultFont' => 'DejaVu Serif'
        ]);
        $filePath = '/assist/users_pdf_example.pdf';

        $pdf->save($filePath, 'local');

        if (Storage::disk('local')->exists($filePath)) {
            Log::debug("File exist");
            Mail::to(env('MAIL_OFFICE'))->send(new AssistMe(storage_path($filePath)));
        } else {
            return $this->sendError(['success' => false]);
        }

        return $this->sendResponse(['success' => true]);

    }
}
