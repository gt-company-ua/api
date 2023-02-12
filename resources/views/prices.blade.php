@extends('adminlte::page')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('title', 'Прайсы')

@section('content_header')
    <h1 class="m-0 text-dark">Прайсы</h1>
@stop

@section('content')
    <x-adminlte-card theme-mode="outline">
        <form action="{{ route('prices.upload') }}" enctype="multipart/form-data" method="post">
            @csrf
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
            <div class="row">
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="inputFile">Калькулятор</label>
                        <div class="input-group">
                            <select name="filename" class="form-control" id="filename" required>
                                <option value="zk.csv">Зеленая карта</option>
                                <option value="vzr.csv">ВЗР</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="inputFile">Файл</label>
                        <div class="input-group">
                            <div class="custom-file row">
                                <input type="file" name="file" class="custom-file-input" id="inputFile" required>
                                <label class="custom-file-label" for="inputImage">Выбрать файл</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button type="submit" class="btn btn-primary">Загрузить</button>
                        </div>
                    </div>
                </div>

            </div>
        </form>

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