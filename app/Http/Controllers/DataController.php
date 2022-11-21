<?php

namespace App\Http\Controllers;

use App\Http\Requests\Data\InnInfoRequest;
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
}
