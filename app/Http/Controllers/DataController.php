<?php

namespace App\Http\Controllers;

use App\Http\Requests\Data\InnInfoRequest;
use App\Http\Requests\Data\SearchUserByPhoneRequest;
use App\Mail\AssistMe;
use App\Mail\OrderPayment;
use App\Models\Order;
use App\Services\api\Bitrix;
use App\Services\api\Ingo;
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

    public function searchUserByPhone(SearchUserByPhoneRequest $request): JsonResponse
    {
        $data = $request->validated();

        $search = (new Bitrix())->getContact($data['phone']);

        return $this->sendResponse($search);
    }

    public function test($oderID)
    {
//        $order = Order::find($oderID);
//        (new OrderService($order))->saveVzr1C();
//        $files = (new Ingo())->greenCardPrintForm($order);
//        if (count($files) > 0) {
//            Mail::to('nostrag@gmail.com')->send(new OrderPayment($files));
//        }
//
//        return $this->sendResponse(['files' => $files]);
    }
}
