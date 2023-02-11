<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
