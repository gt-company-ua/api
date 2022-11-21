<?php

namespace App\Http\Controllers\handbooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Handbooks\TransportFilterRequest;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    use ApiResponser;

    public function categories(TransportFilterRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $categories = TransportCategory::orderBy('ordering');

        if ( ! empty($filter['calc_type'])) {
            $categories->where('show_' . $filter['calc_type'], true);
        }

        return $this->sendResponse($categories->get());
    }

    public function powers(int $categoryId): JsonResponse
    {
        $powers = TransportPower::where('transport_category_id', $categoryId)
            ->orderBy('ordering')->get();

        return $this->sendResponse($powers);
    }
}
