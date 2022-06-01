@extends('statamic::layout')
@section('title', 'Fwk Product Import')

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">Statamic Import</h1>
    </div>
    <div class="mb-3">
        <p>Here we can now take the products imported via csv and now bring them into statamic</p>
    </div>
    @if($fwkProductsCount > 0)
        <div>
            <div class="mb-3">
                <p>You have {{ $fwkProductsCount ?? 0 }} Products imported so far</p>
                <br>
                <a href="{{ cp_route('weareframework.product-importer.statamic.target') }}" class="btn-primary">Begin</a>
            </div>
        </div>
   @else
        <div>
            <p>No products imported</p>
            <a href="{{ cp_route('weareframework.product-importer.statamic.targe') }}" class="btn-primary">Begin</a>
        </div>
   @endif
@stop
