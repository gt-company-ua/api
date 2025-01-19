<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->where('draft', false)->with('assist')->limit(100)->get();

        return view('orders', compact('orders'));
    }
}
