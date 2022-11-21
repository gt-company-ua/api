<?php

namespace App\Http\Controllers\Handbooks;

use App\Http\Controllers\Controller;
use App\Models\VzrRange;
use App\Models\VzrRangeDay;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VzrRangeController extends Controller
{
    use ApiResponser;

    public function ranges(): JsonResponse
    {
        $vzrRanges = VzrRange::where('active', true)->orderBy('name')->get();

        return $this->sendResponse($vzrRanges);
    }

    public function days(int $vzrRangeId): JsonResponse
    {
        $vzrRangeDays = VzrRangeDay::where('vzr_range_id', $vzrRangeId)
            ->orderBy('days')->get();

        return $this->sendResponse($vzrRangeDays);
    }
}
