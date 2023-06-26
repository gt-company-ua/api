<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Requests\v2\VzrCalculateRequest;
use App\Http\Requests\v2\VzrSaveRequest;
use App\Services\api\Ingo;
use App\Services\VzrService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class VzrController extends Controller
{
    use ApiResponser;

    use ApiResponser;

    public function calculate(VzrCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tariffs = [];

        foreach (VzrService::INGO_TARIFFS as $tariff) {
            $calculate = (new Ingo())->vzrCalculate($data, $tariff);

            if (isset($calculate['data']) && isset($calculate['data']['amount'])) {
                $tariffs[$tariff] = round($calculate['data']['amount'], 2);
            }
        }

        return $this->sendResponse($tariffs);
    }


    public function store(VzrSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new VzrService())->saveOrder($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder, 201);
    }
}
