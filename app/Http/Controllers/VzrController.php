<?php

namespace App\Http\Controllers;

use App\Http\Requests\VzrCalculateRequest;
use App\Http\Requests\VzrSaveRequest;
use App\Services\VzrService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VzrController extends Controller
{
    use ApiResponser;

    public function calculate(VzrCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $calculate = (new VzrService())->calculate($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($calculate);
    }


    public function store(VzrSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new VzrService())->saveOrder($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder);
    }


    public function update(Request $request, $id)
    {
        //
    }
}
