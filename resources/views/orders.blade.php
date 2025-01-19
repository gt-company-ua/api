@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'Сделки')

@section('content_header')
    <h1 class="m-0 text-dark">Сделки</h1>
@stop

@section('content')
    <x-adminlte-card theme-mode="outline">
        @php
            $columns = ['#', 'Дата', 'Полис', 'ФИО', 'Телефон', 'Сумма', 'Статус', 'Liqpay', 'Assist'];
            $config = ['ordering' => false];
        @endphp

        <x-adminlte-datatable id="table-orders" :heads="$columns" :config="$config">
            @foreach ($orders as $row)
                <tr data-row-id="{{ $row->id }}">
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->created_at->format('d.m.Y H:i:s') }}</td>
                    <td>{{ $row->order_type }}</td>
                    @if ( ! is_null($row->insurant))
                        <td>{{ $row->insurant->surname }} {{ $row->insurant->name }} {{ $row->insurant->patronymic }}</td>
                        <td>{{ $row->insurant->phone }}</td>
                    @else
                        <td>-</td>
                        <td>-</td>
                    @endif
                    <td>{{ $row->price }}</td>
                    <td>{{ $row->payment_status }}</td>
                    <td>{{ $row->payment_url }}</td>
                    <td>@if (!is_null($row->assist)) {{ $row->assist->price }} | {{ $row->assist->payment_status }} @endif</td>
                </tr>
            @endforeach
        </x-adminlte-datatable>
    </x-adminlte-card>
@stop
