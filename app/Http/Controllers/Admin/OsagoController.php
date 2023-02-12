<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateK1Request;
use App\Models\TransportCategory;
use App\Models\TransportPower;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OsagoController extends Controller
{
    public function index()
    {
        $transportCategories = TransportCategory::orderBy('ordering')->with('powers')->get();

        return view('osago', compact('transportCategories'));
    }

    public function updateK1(UpdateK1Request $request): RedirectResponse
    {
        $data = $request->validated();

        foreach ($data['id'] as $key => $value) {
            $transportPower = TransportPower::findOrFail($value);
            $transportPower->update([
                'coefficient' => $data['coefficient'][$key],
                'api_id' => $data['api_id'][$key],
            ]);
        }

        return back()
            ->with('success','Коэффициенты К1 успешно обновлены')
            ->with('file', $request->input('filname'));
    }
}
