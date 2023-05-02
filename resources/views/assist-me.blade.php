@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'AssistMe')

@section('content_header')
    <h1 class="m-0 text-dark">AssistMe</h1>
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

        <form action="{{ route('assist.tariffs') }}" method="post">
            @csrf
            @method('PUT')
            <section class="col-lg-12">
                <div class="row">
                    <section class="col-lg-12">
                        <table class="table table-striped">
                            <thead>
                            <tr class="info">
                                <th class="col-1">Тип ТС</th>
                                <th class="col-1">15 дней</th>
                                @for($months = 1; $months <=12; $months++)
                                    <th class="col-1">{{ $months }} мес.</th>
                                @endfor
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($transportCategories as $category)
                                <tr>
                                    <td class="info">{{ $category->name_ua }}
                                        <input type="hidden" name="transport_category_id[{{ $category->id }}]" value="{{ $category->id }}">
                                    </td>
                                    @for($months = 0; $months <=12; $months++)
                                        <td class="success">
                                            <input type="hidden" name="trip_duration[{{ $category->id }}][{{ $months }}]" value="{{ $months }}">
                                            <input type="text" name="price[{{ $category->id }}][{{ $months }}]" class="form-control" value="{{ $prices[$category->id][$months] ?? '' }}">
                                        </td>
                                    @endfor
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

    </x-adminlte-card>
@stop
