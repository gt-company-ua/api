<?php

namespace App\Http\Controllers;

use App\Exceptions\FileUploadException;
use App\Http\Requests\OsagoCalculateRequest;
use App\Http\Requests\OsagoSaveRequest;
use App\Models\OsagoTariff;
use App\Services\OsagoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class OsagoController extends Controller
{
    use ApiResponser;

    public function store(OsagoSaveRequest $request): JsonResponse
    {
        try {
            $saveOrder = (new OsagoService())->saveOrder($request);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($saveOrder);
    }


    public function calculate(OsagoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tariffs = (new OsagoService())->calculate($data);

        return $this->sendResponse($tariffs);
    }

    public function tariffs(): JsonResponse
    {
        $tariffs = OsagoTariff::orderBy('franchise', 'DESC')->get();

        return $this->sendResponse($tariffs);
    }
}
