@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'ОСАГО')

@section('content_header')
    <h1 class="m-0 text-dark">ОСАГО</h1>
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
                        <a class="nav-link active" id="k1-tab" data-toggle="pill" href="#k1-content" role="tab" aria-controls="k1-content" aria-selected="true">К1 (Тип транспортного средства)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="k3-tab" data-toggle="pill" href="#k2-content" role="tab" aria-controls="k2-content" aria-selected="false">К2, К4, Льготы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tariffs-tab" data-toggle="pill" href="#tariffs-content" role="tab" aria-controls="tariffs-content" aria-selected="false">Тарифы</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade show active" id="k1-content" role="tabpanel" aria-labelledby="k1-tab">
                        <form action="{{ route('osago.k1') }}" method="post">
                            @csrf
                            @method('PUT')
                            <section class="col-lg-12">
                                <div class="row">
                                    @foreach ($transportCategories as $category)
                                        <section class="col-lg-6">
                                            <h4>{{ $category->name_ua }}</h4>
                                            <table class="table table-striped">
                                                <thead>
                                                <tr class="info">
                                                    <th class="col-md-6">Объем двигателя/мощность</th>
                                                    <th class="col-md-2">Коэфф.</th>
                                                    <th class="col-md-2">Объем</th>
                                                    <th class="col-md-2 danger">API ID</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($category->powers as $power)
                                                    <tr>
                                                        <td class="info">{{ $power->name_ua }}</td>
                                                        <td class="success">
                                                            <input type="hidden" name="id[{{ $power->id }}]" value="{{ $power->id }}">
                                                            <input type="text" name="coefficient[{{ $power->id }}]" class="form-control" value="{{ $power->coefficient }}">
                                                        </td>
                                                        <td class="danger">
                                                            <input type="text" name="capacity[{{ $power->id }}]" class="form-control" value="{{ $power->capacity }}">
                                                        </td>
                                                        <td class="danger">
                                                            <input type="text" name="api_id[{{ $power->id }}]" class="form-control" value="{{ $power->api_id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </section>
                                    @endforeach
                                </div>
                                <div class="row mt-3">
                                    <button type="submit" class="btn btn-primary">Сохранить K1</button>
                                </div>
                            </section>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="k2-content" role="tabpanel" aria-labelledby="k2-tab">
                        <form action="{{ route('osago.k2') }}" method="post">
                            @csrf
                            @method('PUT')
                            <section class="col-lg-12">
                                <div class="row">
                                    <section class="col-lg-6">
                                        <h4>К2 (Место регистрации транспортного средства)</h4>
                                        <table class="table table-striped">
                                            <thead>
                                            <tr class="info">
                                                <th class="col-md-6">Зона</th>
                                                <th class="col-md-2">Коэфф.</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($coefficients as $coefficient)
                                                @php
                                                if ( ! substr_count($coefficient->alias, 'zone')) continue;
                                                @endphp
                                                <tr>
                                                    <td class="info">{{ $coefficient->name }}</td>
                                                    <td class="success">
                                                        <input type="hidden" name="id[{{ $coefficient->id }}]" value="{{ $coefficient->id }}">
                                                        <input type="text" name="coefficient[{{ $coefficient->id }}]" class="form-control" value="{{ $coefficient->coefficient }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </section>
                                    <section class="col-lg-6">
                                        <h4>К4 (Физ лицо / Юр Лицо)</h4>
                                        <table class="table table-striped">
                                            <thead>
                                            <tr class="info">
                                                <th class="col-md-6">Тип</th>
                                                <th class="col-md-2">Коэфф.</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($coefficients as $coefficient)
                                                @php
                                                if ( ! in_array($coefficient->alias, \App\Models\Order::INSURANT_TYPES)) continue;
                                                @endphp
                                                <tr>
                                                    <td class="info">{{ $coefficient->name }}</td>
                                                    <td class="success">
                                                        <input type="hidden" name="id[{{ $coefficient->id }}]" value="{{ $coefficient->id }}">
                                                        <input type="text" name="coefficient[{{ $coefficient->id }}]" class="form-control" value="{{ $coefficient->coefficient }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>

                                        <h4>Льгота</h4>
                                        <table class="table table-striped">
                                            <thead>
                                            <tr class="info">
                                                <th class="col-md-6">Льгота</th>
                                                <th class="col-md-2">Коэфф.</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($coefficients as $coefficient)
                                                @php
                                                if ( ! substr_count($coefficient->alias, 'discount')) continue;
                                                @endphp
                                                <tr>
                                                    <td class="info">{{ $coefficient->name }}</td>
                                                    <td class="success">
                                                        <input type="hidden" name="id[{{ $coefficient->id }}]" value="{{ $coefficient->id }}">
                                                        <input type="text" name="coefficient[{{ $coefficient->id }}]" class="form-control" value="{{ $coefficient->coefficient }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </section>
                                </div>
                                <div class="row mt-3">
                                    <button type="submit" class="btn btn-primary">Сохранить К2, К4, Льготы</button>
                                </div>
                            </section>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="tariffs-content" role="tabpanel" aria-labelledby="tariffs-tab">
                        <form action="{{ route('osago.tariffs') }}" method="post">
                            @csrf
                            @method('PUT')
                            <section class="col-lg-12">
                                <div class="row">
                                    <section class="col-lg-12">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr class="info">
                                                <th class="col-md-4">Тариф</th>
                                                <th class="col-md-4">Франшиза</th>
                                                <th class="col-md-4">Коэффициент</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($tariffs as $tariff)
                                                <tr>
                                                    <td class="info">{{ $tariff->tariff }}</td>
                                                    <td class="success">
                                                        <input type="hidden" name="id[{{ $tariff->id }}]" value="{{ $tariff->id }}">
                                                        <input type="text" name="franchise[{{ $tariff->id }}]" class="form-control" value="{{ $tariff->franchise }}">
                                                    </td>
                                                    <td class="success">
                                                        <input type="text" name="coefficient[{{ $tariff->id }}]" class="form-control" value="{{ $tariff->coefficient }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </section>
                                </div>
                                <div class="row mt-3">
                                    <button type="submit" class="btn btn-primary">Сохранить тарифы</button>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </x-adminlte-card>
@stop
