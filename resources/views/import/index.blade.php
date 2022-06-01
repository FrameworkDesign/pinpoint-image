@extends('statamic::layout')
@section('title', 'Fwk Product Import')

@section('content')
    <div class="flex items-center mb-3">
        <h1 class="flex-1">WeAreFramework Product Import</h1>
    </div>
    <div class="flex items-center mb-3">
        <p>Upload your CSV and lets get a going</p>
    </div>
    <div>
        <form action="{{ cp_route('weareframework.product-importer.import.upload') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}

            <header class="mb-3">
                <div class="flex items-center justify-between">
                    <h1>Import</h1>
                    <button class="btn-primary">Continue</button>
                </div>
            </header>

            <div class="card rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
                <header class="text-center mb-6">
                    <h1 class="mb-3">Start import</h1>
                    <p class="text-grey">Imports allow you to create a lot of entries at once. </p>
                </header>
                <div class="mb-5">
                    <label for="file" class="font-bold text-base mb-sm">File</label>
                    <input id="file" name="file" type="file" tabindex="1" class="input-text" accept="text/csv">
                    <div class="text-2xs text-grey-60 mt-1 flex items-center">
                        A CSV file, make sure it includes a header row
                    </div>
                    @if ($errors->has('file'))
                        <span class="text-red text-2xs">{{ $errors->first('file') }}</span>
                    @endif
                </div>
                <div class="mb-5">
                    <label for="delimiter" class="font-bold text-base mb-sm">Delimiter</label>
                    <input id="delimiter" name="delimiter" placeholder="," value="," type="text" tabindex="1" class="input-text">
                    @if ($errors->has('delimiter'))
                        <span class="text-red text-2xs">{{ $errors->first('delimiter') }}</span>
                    @endif
                    <div class="text-2xs text-grey-60 mt-1 flex items-center">
                        Defaults to <code>,</code>. Is usually one of <code>,</code>,<code>;</code>,<code>|</code>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
