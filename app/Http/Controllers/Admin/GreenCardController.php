<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GreenCardCashbackRequest;
use App\Models\GreencardCashback;
use App\Models\OsagoCoefficient;
use App\Models\OsagoTariff;
use App\Models\TransportCategory;
use Illuminate\Http\Request;

class GreenCardController extends Controller
{
    public function index()
    {
        $prices = GreencardCashback::orderBy('months')->pluck('amount', 'months');

        return view('greencard', compact('prices'));
    }

    public function updateCashback(GreenCardCashbackRequest $request)
    {
        $data = $request->validated();

        foreach ($data['months'] as $key => $month) {
            GreencardCashback::updateOrCreate(
                ['months' => $month],
                ['amount' => $data['amount'][$key]]
            );
        }

        return back()
            ->with('success','Суммы успешно обновлены');
    }
}
