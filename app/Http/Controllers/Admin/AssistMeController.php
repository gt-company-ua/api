<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAssistMePricesRequest;
use App\Http\Requests\Admin\UpdateK1Request;
use App\Models\AssistMePrice;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssistMeController extends Controller
{
    public function index()
    {
        $transportCategories = TransportCategory::orderBy('ordering')->get();

        $prices = [];

        foreach ($transportCategories as $category) {
            $prices[$category->id] = AssistMePrice::where('transport_category_id', $category->id)->pluck('price', 'trip_duration');
        }

        return view('assist-me', compact('transportCategories', 'prices'));
    }

    public function update(UpdateAssistMePricesRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['transport_category_id'] as $key => $value) {
            foreach ($data['price'][$key] as $tripDuration => $price) {
                AssistMePrice::updateOrCreate(
                    ['transport_category_id' => $key, 'trip_duration' => $tripDuration],
                    ['price' => $price]
                );
            }
        }

        return back()
            ->with('success','Тарифы успешно обновлены');
    }
}
