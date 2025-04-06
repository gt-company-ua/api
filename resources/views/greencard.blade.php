@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'Green Card')

@section('content_header')
    <h1 class="m-0 text-dark">Green Card</h1>
@stop

@section('content')
    <x-adminlte-card theme-mode="outline">
        <div class="row">
            <div class="col-sm-5">
                @if ($message = \Illuminate\Support\Facades\Session::get('success'))
                    <div class="alert alert-success">
                        <strong>{{ $message }}</strong>
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="card card-info card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="info-tabs" role="tablist">
                    @php $count = 0; @endphp
                    @foreach($prices as $company => $values)
                        <li class="nav-item">
                            <a class="nav-link @if($count === 0) active @endif" id="prices-tab-{{ $company }}" data-toggle="pill" href="#prices-content-{{ $company }}" role="tab" aria-controls="tariffs-content" aria-selected="true">Цены {{ $company }}</a>
                        </li>
                        @php $count ++; @endphp
                    @endforeach
                    @php $count = 0; @endphp
                    @foreach($cashback as $company => $values)
                        <li class="nav-item">
                            <a class="nav-link" id="cashback-tab-{{ $company }}" data-toggle="pill" href="#cashback-content-{{ $company }}" role="tab" aria-controls="tariffs-content" aria-selected="true">Cashback {{ $company }}</a>
                        </li>
                        @php $count ++; @endphp
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    @php $count = 0; @endphp
                    @foreach($prices as $company => $values)
                        <div class="tab-pane fade show @if($count === 0) active @endif" id="prices-content-{{ $company }}" role="tabpanel" aria-labelledby="prices-tab-{{ $company }}">
                            <form action="{{ route('greencard.prices', ['company' => $company]) }}" method="post">
                                @csrf
                                @method('PUT')
                                <section class="col-lg-12">
                                    <div class="row">
                                        <section class="col-lg-12">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr class="info">
                                                    <th class="col-md-2" rowspan="2">Период</th>
                                                    <th class="col-md-2 text-center" colspan="2">Грузовые и Автобусы</th>
                                                    <th class="col-md-2 text-center" colspan="2">Мотоцикл</th>
                                                    <th class="col-md-2 text-center" colspan="2">Прицеп</th>
                                                    <th class="col-md-2 text-center" colspan="2">Остальные</th>
                                                </tr>
                                                <tr class="info">
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @for($month = 0; $month <= 12; $month++)
                                                    <tr>
                                                        <td class="success">
                                                            <input type="hidden" name="months[{{ $month }}]" value="{{ $month }}">
                                                            @if ($month === 0)15 дней@else{{ $month }} мес.@endif
                                                        </td>
                                                        @foreach(\App\Models\GreencardCashback::TRANSPORT_TYPE as $transport)
                                                            @foreach(\App\Models\Order::TRIP_COUNTRIES as $country)
                                                                <td class="success">
                                                                    <input type="text" name="amount_{{ $country }}_{{ $transport }}[{{ $month }}]" class="form-control" value="@if (isset($values[$country][$transport][$month])){{ $values[$country][$transport][$month] }}@endif">
                                                                </td>
                                                            @endforeach
                                                        @endforeach
                                                    </tr>
                                                @endfor
                                                </tbody>
                                            </table>
                                        </section>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="submit" class="btn btn-primary">Сохранить цены {{ $company }}</button>
                                    </div>
                                </section>
                            </form>
                        </div>
                        @php $count ++; @endphp
                    @endforeach

                    @php $count = 0; @endphp
                    @foreach($cashback as $company => $values)
                        <div class="tab-pane fade show" id="cashback-content-{{ $company }}" role="tabpanel" aria-labelledby="cashback-tab-{{ $company }}">
                            <form action="{{ route('greencard.cashback', ['company' => $company]) }}" method="post">
                                @csrf
                                @method('PUT')
                                <section class="col-lg-12">
                                    <div class="row">
                                        <section class="col-lg-12">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr class="info">
                                                    <th class="col-md-2" rowspan="2">Период</th>
                                                    <th class="col-md-2 text-center" colspan="2">Грузовые и Автобусы</th>
                                                    <th class="col-md-2 text-center" colspan="2">Мотоцикл</th>
                                                    <th class="col-md-2 text-center" colspan="2">Прицеп</th>
                                                    <th class="col-md-2 text-center" colspan="2">Остальные</th>
                                                </tr>
                                                <tr class="info">
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                    <th class="col-md-1">Сумма Европа</th>
                                                    <th class="col-md-1">Сумма СНГ</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @for($month = 0; $month <= 12; $month++)
                                                    <tr>
                                                        <td class="success">
                                                            <input type="hidden" name="months[{{ $month }}]" value="{{ $month }}">
                                                            @if ($month === 0)15 дней@else{{ $month }} мес.@endif
                                                        </td>
                                                        @foreach(\App\Models\GreencardCashback::TRANSPORT_TYPE as $transport)
                                                            @foreach(\App\Models\Order::TRIP_COUNTRIES as $country)
                                                                <td class="success">
                                                                    <input type="text" name="amount_{{ $country }}_{{ $transport }}[{{ $month }}]" class="form-control" value="@if (isset($values[$country][$transport][$month])){{ $values[$country][$transport][$month] }}@endif">
                                                                </td>
                                                            @endforeach
                                                        @endforeach
                                                    </tr>
                                                @endfor
                                                </tbody>
                                            </table>
                                        </section>
                                    </div>
                                    <div class="row mt-3">
                                        <button type="submit" class="btn btn-primary">Сохранить Cashback {{ $company }}</button>
                                    </div>
                                </section>
                            </form>
                        </div>
                        @php $count ++; @endphp
                    @endforeach

                </div>
            </div>
        </div>

    </x-adminlte-card>
@stop
