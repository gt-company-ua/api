@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'Прайсы')

@section('content_header')
    <h1 class="m-0 text-dark">Прайсы</h1>
@stop

@section('content')
    <x-adminlte-card theme-mode="outline">
        @php
            $columns = ['Прайс', 'Дата изменения', 'Скачать'];
            $config = ['ordering' => false];
            $zk = 'prices/zk.csv';
            $vzr = 'prices/vzr.csv';
        @endphp

        <x-adminlte-datatable id="table-prices" :heads="$columns" :config="$config">
            <tr>
                <td>Зеленая карта</td>
                <td>{{ date('d.m.Y H:i:s', \Illuminate\Support\Facades\Storage::disk('local')->lastModified($zk)) }}</td>
                <td><a href="{{ route('prices.download', ['filename' => 'zk.csv']) }}"><i class="fa fa-download"></i></a></td>
            </tr>
            <tr>
                <td>ВЗР</td>
                <td>{{ date('d.m.Y H:i:s', \Illuminate\Support\Facades\Storage::disk('local')->lastModified($vzr)) }}</td>
                <td><a href="{{ route('prices.download', ['filename' => 'vzr.csv']) }}"><i class="fa fa-download"></i></a></td>
            </tr>
        </x-adminlte-datatable>
    </x-adminlte-card>
@stop