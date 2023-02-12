<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceUploadRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PricesController extends Controller
{
    public function index()
    {
        return view('prices');
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::download('prices/' . $filename);
    }

    public function upload(PriceUploadRequest $request): \Illuminate\Http\RedirectResponse
    {
        Storage::disk('local')->putFileAs('prices', $request->file('file'), $request->post('filename'));

        return back()
            ->with('success','Файл успешно загружен')
            ->with('file', $request->input('filname'));
    }
}
