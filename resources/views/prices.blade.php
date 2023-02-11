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
            $zk = storage_path('app/prices/') . 'zk.csv'
        @endphp

        <x-adminlte-datatable id="table-prices" :heads="$columns" :config="$config">
            <tr>
                <td>Зеленая карта</td>
                <td>{{ \Illuminate\Support\Facades\Storage::lastModified($zk)->format('d.m.Y H:i:s') }}</td>
                <td>{{ route('prices.download', ['filename' => 'zk.csv']) }}</td>
            </tr>
        </x-adminlte-datatable>
    </x-adminlte-card>
@stop