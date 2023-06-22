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
                    <li class="nav-item">
                        <a class="nav-link active" id="cashback-tab" data-toggle="pill" href="#cashback-content" role="tab" aria-controls="tariffs-content" aria-selected="true">Cashback</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade show active" id="cashback-content" role="tabpanel" aria-labelledby="cashback-tab">
                        <form action="{{ route('greencard.cashback') }}" method="post">
                            @csrf
                            @method('PUT')
                            <section class="col-lg-12">
                                <div class="row">
                                    <section class="col-lg-12">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr class="info">
                                                <th class="col-md-2">Период</th>
                                                <th class="col-md-5">Сумма Европа</th>
                                                <th class="col-md-5">Сумма СНГ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @for($month = 0; $month <= 12; $month++)
                                                <tr>
                                                    <td class="success">
                                                        <input type="hidden" name="months[{{ $month }}]" value="{{ $month }}">
                                                        @if ($month === 0)15 дней@else{{ $month }} мес.@endif
                                                    </td>
                                                    <td class="success">
                                                        <input type="text" name="amount_{{ \App\Models\Order::TRIP_COUNTRY_EU }}[{{ $month }}]" class="form-control" value="@if (isset($prices[\App\Models\Order::TRIP_COUNTRY_EU][$month])){{ $prices[\App\Models\Order::TRIP_COUNTRY_EU][$month] }}@endif">
                                                    </td>
                                                    <td class="success">
                                                        <input type="text" name="amount_{{ \App\Models\Order::TRIP_COUNTRY_SNG }}[{{ $month }}]" class="form-control" value="@if (isset($prices[\App\Models\Order::TRIP_COUNTRY_SNG][$month])){{ $prices[\App\Models\Order::TRIP_COUNTRY_SNG][$month] }}@endif">
                                                    </td>
                                                </tr>
                                            @endfor
                                            </tbody>
                                        </table>
                                    </section>
                                </div>
                                <div class="row mt-3">
                                    <button type="submit" class="btn btn-primary">Сохранить Cashback</button>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </x-adminlte-card>
@stop
