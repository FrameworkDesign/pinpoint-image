@extends('statamic::layout')
@section('title', 'Fwk Product Import')

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">WeAreFramework Dashboard</h1>
    </div>
    <div class="mb-3">
        <p>Upload your CSV and lets get a going</p>
        <a href="{{ cp_route('weareframework.product-importer.import.index') }}" class="btn-primary">Import</a>
    </div>
    @if($fwkProductsCount > 0)
        <div>
            <div class="mb-3">
                <p>You have {{ $fwkProductsCount ?? 0 }} Products imported so far</p>
                <a href="{{ cp_route('weareframework.product-importer.imported.index') }}" class="btn-primary">View</a>
                <br>
                <br>
                <p>Or import into statamic:</p>
                <a href="{{ cp_route('weareframework.product-importer.statamic.index') }}" class="btn-primary">Pull into collection</a>
            </div>
        </div>
    @endif
@stop
