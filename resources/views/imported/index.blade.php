@extends('statamic::layout')
@section('title', 'Done < Data Import')

@php($status = !is_null(cache("fwk-product-parent-match-total")) && cache("fwk-product-parent-match-total") === cache("fwk-product-parent-match-processed") ? 'done' : 'ongoing')
@section('content')
        <header class="mb-3">
            <h1>Imported</h1>
        </header>

        <div class="card rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
            <header class="text-center">
                <h1 class="mb-3">Imported Products</h1>
                <h3 class="mb-3">Actions</h3>
                <div class="flex justify-center items-center">
                    <form onsubmit="return confirm('are you sure?')" class="pr-2" action="{{ cp_route('weareframework.product-importer.imported.delete-all') }}" method="POST">
                        {{ csrf_field() }}
                        @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm mb-1">Delete All products</button>
                    </form>
                    <form action="{{ cp_route('weareframework.product-importer.imported.match-all') }}" method="POST">
                        {{ csrf_field() }}
                        <button type="submit" class="btn-primary btn-sm mb-1">Match All children SKUs</button>
                    </form>

                    <a href="{{ cp_route('weareframework.product-importer.statamic.index') }}" class="btn-flat btn-sm mb-1 ml-2">Pull Into Collection</a>
                </div>
                @if (is_null(cache("fwk-product-parent-match-total")))
                    <p class="text-grey">Match all children SKUs is being processed...</p>
                @else
                    <p class="text-grey"><strong>{{ cache("fwk-product-parent-match-processed") }}</strong> products rows of <strong>{{ cache("fwk-product-parent-match-total") }}</strong> product rows have been ran.</p>
                @endif
                @if ($status === 'ongoing')
                    <button type="button" class="btn-primary btn-sm mt-2" onclick="window.location.reload()">Refresh</button>
                @endif

            </header>

            @if ($fwkProducts->count() > 0)
                <div class="mb-4 mt-6">
                    <h2 class="mb-1">Products</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Index</th>
                                <th>SKU</th>
                                <th>Parent</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fwkProducts as $index => $fwkProduct)
                                <tr>
                                    <td class="text-black mb-1" style="word-break: break-word;">{!! $index !!}</td>
                                    <td class="text-black mb-1" style="word-break: break-word;">{!! $fwkProduct->sku ?? 'no sku' !!}</td>
                                    <td class="text-black mb-1" style="word-break: break-word;">{!! $fwkProduct->parent_sku ?? 'no parent' !!}</td>
                                    <td class="text-black mb-1" style="word-break: break-word;">{!! $fwkProduct->type ?? 'no type' !!}</td>
                                    <td class="text-black mb-1" style="word-break: break-word;">
                                        @if(strtolower($fwkProduct->type) === 'configurable')
                                            <form action="{{ cp_route('weareframework.product-importer.imported.match', $fwkProduct) }}" method="POST">
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn-primary btn-sm mb-1">Match children SKUs</button>
                                            </form>
                                        @endif
                                        <a href="{{ cp_route('weareframework.product-importer.imported.edit', $fwkProduct) }}" class="btn-default btn-sm mb-1">Edit</a>
                                        <form onsubmit="return confirm('are you sure?')" action="{{ cp_route('weareframework.product-importer.imported.delete', $fwkProduct) }}" method="POST">
                                            {{ csrf_field() }}
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm mb-1">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $fwkProducts->links() }}
                </div>
            @endif
        </div>
@endsection
