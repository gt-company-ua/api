<?php

namespace App\Http\Controllers\handbooks;

use App\Http\Controllers\Controller;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    use ApiResponser;

    public function categories(): JsonResponse
    {
        $categories = TransportCategory::orderBy('ordering')->get();

        return $this->sendResponse($categories);
    }

    public function powers(int $categoryId): JsonResponse
    {
        $powers = TransportPower::where('transport_category_id', $categoryId)
            ->orderBy('ordering')->get();

        return $this->sendResponse($powers);
    }
}
