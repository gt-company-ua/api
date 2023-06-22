<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GreenCardCashbackRequest;
use App\Models\GreencardCashback;
use App\Models\Order;
use App\Models\OsagoCoefficient;
use App\Models\OsagoTariff;
use App\Models\TransportCategory;
use Illuminate\Http\Request;

class GreenCardController extends Controller
{
    public function index()
    {
        $prices = [];
        $prices[Order::TRIP_COUNTRY_EU] = GreencardCashback::where('trip_country', Order::TRIP_COUNTRY_EU)->orderBy('months')->pluck('amount', 'months');
        $prices[Order::TRIP_COUNTRY_SNG] = GreencardCashback::where('trip_country', Order::TRIP_COUNTRY_SNG)->orderBy('months')->pluck('amount', 'months');

        return view('greencard', compact('prices'));
    }

    public function updateCashback(GreenCardCashbackRequest $request)
    {
        $data = $request->validated();

        foreach ($data['months'] as $key => $month) {
            foreach (Order::TRIP_COUNTRIES as $tripCountry) {
                GreencardCashback::updateOrCreate(
                    ['months' => $month, 'trip_country' => $tripCountry],
                    ['amount' => $data['amount_' . $tripCountry][$key]]
                );
            }

        }

        return back()
            ->with('success','Суммы успешно обновлены');
    }
}
