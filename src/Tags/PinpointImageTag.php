<?php

namespace Weareframework\PinpointImage\Tags;

use Statamic\Facades\Asset;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

class PinpointImageTag extends Tags
{

    protected static $handle = 'pinpoint_image';

    public static function render(...$arguments): string
    {
        return '';
    }

    public function index()
    {
        try {
            $field = $this->params->get('field') ?? null;

            if (is_null($field)) {
                return '';
            }

            $image = ($asset = Asset::find($field['image'] ?? ''))
                ? (new AssetResource($asset))->resolve()
                : null;

            return view('pinpoint-image::tags.index', [
                'annotations' => $field['annotations'] ?? [],
                'image' => $image ?? null,
            ])->render();
        } catch (\Exception $e) {
            return '';
        }
    }
}

