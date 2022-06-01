@extends('statamic::layout')
@section('title', 'Your Data < Data Import')

@section('content')

    <div class="flex items-center mb-3">
        <h1 class="flex-1">WeAreFramework Product Import</h1>
    </div>
    <div class="flex items-center mb-3">
        <p>Upload your CSV and lets get a going</p>
    </div>
    <div>
        <form action="{{ cp_route('weareframework.product-importer.import.process') }}" method="post">
            {{ csrf_field() }}

            <header class="mb-3">
                <div class="flex items-center justify-between">
                    <h1>Your data</h1>
                    <button class="btn-primary">Continue</button>
                </div>
            </header>

            <div class="card rounded p-3 lg:px-7 lg:py-5 shadow bg-white mb-2">
                <header class="text-center">
                    <h1 class="mb-3">Your data</h1>
                    <p class="text-grey">This page shows the first <strong>5</strong> items of your data. If your data looks good, press continue. Your uploaded file contains <strong>{{ $rowCount }}</strong> rows of data.</p>
                </header>
            </div>

            @foreach ($preview as $row)
                <div class="card mb-1">
                    <table class="data-table">
                        <tbody>
                            @foreach ($row as $key => $value)
                                <tr>
                                    <th class="pl-2 py-1 w-1/4">{{ $key }}</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </form>
    </div>
@endsection
