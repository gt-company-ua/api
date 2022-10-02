<?php

namespace App\Http\Controllers;

use App\Http\Requests\KaskoCalculateRequest;
use App\Http\Requests\KaskoSaveRequest;
use App\Services\KaskoService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

class KaskoController extends Controller
{
    use ApiResponser;

    public function store(KaskoSaveRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = (new KaskoService())->saveOrder($data);

        return $this->sendResponse($order, 201);
    }

    public function calculate(KaskoCalculateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $calculate = (new KaskoService())->calculate($data);

        return $this->sendResponse(['sum' => $calculate]);
    }
}
