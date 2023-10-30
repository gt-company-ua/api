<?php

namespace App\Http\Controllers;

use App\Http\Requests\Data\InnInfoRequest;
use App\Http\Requests\Data\SearchUserByHashRequest;
use App\Http\Requests\Data\SearchUserByPhoneRequest;
use App\Http\Requests\Data\SendSmsRequest;
use App\Models\SmsConfirm;
use App\Services\api\Bitrix;
use App\Services\api\Turbosms;
use App\Services\OrderService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

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

        $checkCode = !empty($data['code']);

        if ($checkCode) {
            $code = SmsConfirm::where('phone', $data['phone'])->where('code', $data['code'])->where('status', 'sent')->first();

            if (is_null($code)) {
                return $this->sendError('Неправильний код, спробуйте ще раз');
            }

            $code->status = 'used';
            $code->save();
        }

        $search = (new Bitrix())->getContact($data['phone']);

        if ($checkCode) {
            return $this->sendResponse($search);
        }

        if (count($search) === 0) {
            return $this->sendResponse(['status' => false], 404);
        }

        return $this->sendSuccess();
    }

    public function searchUserByHash(SearchUserByHashRequest $request): JsonResponse
    {
        $data = $request->validated();

        $encodeHash = base64_decode($data['hash']);
        $hash = substr($encodeHash, -4) . substr($encodeHash, 0, strlen($encodeHash) - 4);

        $phone = base64_decode($hash);

        $search = (new Bitrix())->getContact($phone);

        if (count($search) === 0) {
            return $this->sendResponse(['status' => false], 404);
        }

        return $this->sendResponse($search);
    }

    public function sendUserSms(SendSmsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $code = mt_rand(1000, 9999);

        $send = (new Turbosms())->messageSend($data['phone'], $code);
        if (count($send) === 0) {
            return $this->sendError('Не вдалося надіслати повідомлення, перевірте номер і спробуйте ще раз.');
        }

        $responseCode = $send['response_code'] ?? null;
        $responseStatus = $send['response_status'] ?? null;
        $externalId = null;

        if (isset($send['response_result'])) {
            foreach ($send['response_result'] as $result) {
                if ($result['phone'] === $data['phone']) {
                    $responseCode = $result['response_code'] ?? null;
                    $responseStatus = $result['response_status'] ?? null;
                    $externalId = $result['message_id'] ?? null;
                    break;
                }
            }
        }

        SmsConfirm::create([
            'phone' => $data['phone'],
            'code' => $code,
            'external_id' => $externalId,
            'response_status' => $responseStatus,
            'response_code' => $responseCode,
            'status' => 'sent'
        ]);

        return $this->sendSuccess();
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
