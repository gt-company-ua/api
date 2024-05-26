<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VzrCashbackRequest;
use App\Models\GreencardCashback;
use App\Models\Order;
use App\Models\VzrCashback;
use App\Services\api\Ingo;

class VzrController extends Controller
{

    public function index()
    {
        $prices = VzrCashback::pluck('amount', 'tariff');

        return view('vzr', compact('prices'));
    }
    public function updateCashback(VzrCashbackRequest $request)
    {
        $data = $request->validated();

        foreach ($data['tariff'] as $key => $amount) {
            VzrCashback::updateOrCreate(
                ['tariff' => $key],
                ['amount' => $amount]
            );
        }

        return back()
            ->with('success','Суммы успешно обновлены');
    }
}
