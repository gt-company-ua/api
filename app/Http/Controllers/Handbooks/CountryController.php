<?php

namespace App\Http\Controllers\Handbooks;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $countries = Country::orderBy('name')->get();

        return $this->sendResponse($countries);
    }
}
