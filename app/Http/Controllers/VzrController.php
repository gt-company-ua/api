<?php

namespace App\Http\Controllers;

use App\Http\Requests\VzrCalculateRequest;
use App\Services\VzrService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use PHPUnit\Exception;

class VzrController extends Controller
{
    use ApiResponser;

    public function calculate(VzrCalculateRequest $request)
    {
        $data = $request->validated();
        try {
            $calculate = (new VzrService())->calculate($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($calculate);
    }


    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
