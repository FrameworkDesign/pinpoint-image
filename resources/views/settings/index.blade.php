@extends('statamic::layout')

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">WeAreFramework Product Importer Settings</h1>
    </div>
    <div class="flex items-center mb-3">
        <p>Control your Product Importer general settings here. Make sure to read the instructions on each input.</p>
    </div>

    <div>
        <publish-form
            title="Settings"
            action="{{ cp_route('weareframework.product-importer.settings.update') }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
        />

    </div>
@stop
