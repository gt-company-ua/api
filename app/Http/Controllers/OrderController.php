<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponser;

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show(string $uuid): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        return $this->sendResponse($order);
    }


    public function update(Request $request, $id)
    {
        //
    }

    public function liqPayStatus(Request $request)
    {
        //
    }

    public function liqPayResult(Request $request)
    {
        //
    }
}
