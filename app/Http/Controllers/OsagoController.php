<?php

namespace App\Http\Controllers;

use App\Http\Requests\OsagoCalculateRequest;
use App\Models\OsagoTariff;
use App\Services\OsagoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OsagoController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {
        //
    }


    public function calculate(OsagoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tariffs = (new OsagoService())->calculate($data);

        return $this->sendResponse($tariffs);
    }

    public function tariffs()
    {
        $tariffs = OsagoTariff::orderBy('franchise', 'DESC')->get();

        return $this->sendResponse($tariffs);
    }
}
