<?php

namespace App\Http\Controllers;

use App\Http\Requests\Data\InnInfoRequest;
use App\Mail\AssistMe;
use App\Mail\OrderPayment;
use App\Models\Order;
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

    public function test($oderID)
    {
        $order = Order::find($oderID);
        Mail::to(env('MAIL_TEST'))->send(new AssistMe($order));

        return $this->sendSuccess();
    }
}
