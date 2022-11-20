<?php

namespace App\Http\Controllers;

use App\Http\Requests\GreenCardCalculateRequest;
use App\Http\Requests\GreenCardSaveRequest;
use App\Services\GreenCardService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GreenCardController extends Controller
{
    use ApiResponser;

    public function calculate(GreenCardCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $price = (new GreenCardService())->calculate($data);

        return $this->sendResponse(['price' => $price]);
    }

    public function store(GreenCardSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new GreenCardService())->saveOrder($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder, 201);
    }


    public function update(Request $request, $id)
    {
        //
    }

}
