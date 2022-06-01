@extends('statamic::layout')
@section('title', 'Import < Edit')

@section('content')

    <form action="{{ cp_route('weareframework.product-importer.imported.update', $fwkProduct) }}" method="POST">
        {{ csrf_field() }}
        @method('PATCH')
        <div class="shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 bg-white sm:p-6">
                @if($errors->count() > 0)
                    <div class="pb-4">
                        <p class="mt-1 text-md leading-5 text-red-400">You have <strong>{{ $errors->count() }}</strong> error(s). Please look through below to rectify</p>
                    </div>
                @endif
                <div class="grid grid-cols-6 gap-6">
                    @foreach ($fields as $key => $value)
                        <div class="col-span-6 sm:col-span-3">
                            <label for="{{ $value }}" class="block text-sm font-medium {{ $errors->has($value) ? ' text-red-700' : 'text-gray-700' }}">{{ str_replace('_', ' ', $value) }}</label>
                            <input id="{{ $value }}" name="{{ $value }}" value="{{ old($value, $fwkProduct[$value]) ?? null }}" class="mt-1 form-input block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                            @if ($errors->has($value))
                                <span class="mt-1 text-sm leading-5 text-red-400" role="alert">
                                    {{ $errors->first($value) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a class="btn-default" href="{{ cp_route('weareframework.product-importer.imported.index') }}">Back</a>
                <button class="btn-primary">
                    Save
                </button>
            </div>
        </div>
    </form>

@endsection
