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

        foreach (GreencardCashback::TRANSPORT_TYPE as $type) {
            foreach (Order::TRIP_COUNTRIES as $tripCountry) {
                $prices[$tripCountry][$type] = GreencardCashback::where('trip_country', $tripCountry)->where('transport_type', $type)->orderBy('months')->pluck('amount', 'months');
            }
        }

        return view('greencard', compact('prices'));
    }

    public function updateCashback(GreenCardCashbackRequest $request)
    {
        $data = $request->validated();

        foreach ($data['months'] as $key => $month) {
            foreach (GreencardCashback::TRANSPORT_TYPE as $type) {
                foreach (Order::TRIP_COUNTRIES as $tripCountry) {
                    GreencardCashback::updateOrCreate(
                        ['months' => $month, 'trip_country' => $tripCountry, 'transport_type' => $type],
                        ['amount' => $data['amount_' . $tripCountry . '_' . $type][$key]]
                    );
                }
            }
        }

        return back()
            ->with('success','Суммы успешно обновлены');
    }
}
