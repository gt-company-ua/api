<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OsagoCashbackRequest;
use App\Http\Requests\Admin\UpdateK1Request;
use App\Http\Requests\Admin\UpdateK2Request;
use App\Http\Requests\Admin\UpdateTariffsRequest;
use App\Models\OsagoCashback;
use App\Models\OsagoCoefficient;
use App\Models\OsagoTariff;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use App\Services\api\Ingo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OsagoController extends Controller
{
    public function index()
    {
        $transportCategories = TransportCategory::orderBy('ordering')->with('powers')->get();
        $coefficients = OsagoCoefficient::all();
        $tariffs = OsagoTariff::all();
        $cashback = OsagoCashback::pluck('amount', 'franchise');

        return view('osago', compact('transportCategories', 'coefficients', 'tariffs', 'cashback'));
    }

    public function updateK1(UpdateK1Request $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['id'] as $key => $value) {
            $transportPower = TransportPower::findOrFail($value);
            $transportPower->update([
                'coefficient' => $data['coefficient'][$key],
                'api_id' => $data['api_id'][$key],
                'capacity' => $data['capacity'][$key],
            ]);
        }

        return back()
            ->with('success','Коэффициенты К1 успешно обновлены');
    }

    public function updateK2(UpdateK2Request $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['id'] as $key => $value) {
            $coefficient = OsagoCoefficient::findOrFail($value);
            $coefficient->update([
                'coefficient' => $data['coefficient'][$key],
            ]);
        }

        return back()
            ->with('success','Коэффициенты К2, К4, Льготы, успешно обновлены');
    }

    public function updateTariffs(UpdateTariffsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['id'] as $key => $value) {
            $coefficient = OsagoTariff::findOrFail($value);
            $coefficient->update([
                'coefficient' => $data['coefficient'][$key],
                'franchise' => $data['franchise'][$key],
            ]);
        }

        return back()
            ->with('success','Тарифы успешно обновлены');
    }

    public function updateCashback(OsagoCashbackRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach (Ingo::OSAGO_FRANCHISES as $franchise) {
            OsagoCashback::updateOrCreate(
                ['franchise' => $franchise],
                ['amount' => $data['cashback'][$franchise]]
            );
        }

        return back()
            ->with('success','Cashback успешно обновлен');
    }
}
