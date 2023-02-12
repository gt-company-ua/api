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
                        <a class="nav-link" id="k3-tab" data-toggle="pill" href="#k3-content" role="tab" aria-controls="k3-content" aria-selected="false">К3 (Сфера использования)</a>
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
                    <div class="tab-pane fade" id="k3-content" role="tabpanel" aria-labelledby="k3-tab"></div>
                </div>
            </div>
        </div>

    </x-adminlte-card>
@stop