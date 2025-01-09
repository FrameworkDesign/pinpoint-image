<?php

namespace Weareframework\PinpointImage\Tags;

use Statamic\Facades\Asset;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Http\Resources\API\AssetResource;
use Statamic\Support\Str;
use Statamic\Tags\Tags;
use Tiptap\Editor;
use Tiptap\Extensions;
use Tiptap\Marks;
use Tiptap\Nodes;

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

            $field['annotations'] = collect($field['annotations'])->map(function($value) {
                if ( !isset($value['data']['fields']) ) {
                    return $value;
                }

                $value['data']['fields'] = collect($value['data']['fields'])->map(function($field) {
                    $field = match ($field['value']) {
                        'link' => $this->linkField($field),
                        'code' => $this->codeField($field),
                        'bard' => $this->bardField($field),
                        default => $field
                    };
                    return $field;
                })->all();
                return $value;
            })->all();

            return view('pinpoint-image::tags.index', [
                'annotations' => $field['annotations'] ?? [],
                'image' => $image ?? null,
            ])->render();
        } catch (\Exception $e) {
            return '';
        }
    }

    private function codeField($field)
    {
        $field['content'] = isset($field['content'], $field['content']['code']) ? $field['content']['code'] : null;
        return $field;
    }

    private function bardField($field)
    {
        $field['content'] = app()->makeWith(Editor::class, [
            'configuration' => [
                'extensions' => [
                    new Extensions\StarterKit,
                    new Marks\Link,
                    new Marks\Bold,
                    new Marks\Italic,
                    new Nodes\Image,
                    new Nodes\Table,
                    new Nodes\TableCell,
                    new Nodes\TableHeader,
                    new Nodes\TableRow,
                ],
            ],
            'content' => $field['content']
        ])->setContent(['content' => $field['content']])->getHtml();

        return $field;
    }

    private function linkField($field)
    {
        $field['type'] = 'link';

        $selectedEntry = $field['content'] && Str::startsWith($field['content'], 'entry::') ? Str::after($field['content'], 'entry::') : null;
        if (! is_null($selectedEntry)) {
            $field['type'] = 'entry';
            $field['content'] = $this->getUrlFromId($selectedEntry);
        }

        $selectedAsset = $field['content'] && Str::startsWith($field['content'], 'asset::') ? Str::after($field['content'], 'asset::') : null;
        if (! is_null($selectedAsset)) {
            $field['type'] = 'asset';
            $field['content'] = ($asset = Asset::find($selectedAsset)) ? (new AssetResource($asset))->resolve() : null;
        }

        $url = ($field !== '@child' && ! $selectedEntry && ! $selectedAsset) ? $field['content'] : null;
        if (! is_null($selectedAsset)) {
            $field['type'] = 'url';
            $field['content'] = $url;
        }

        return $field;
    }

    protected function getUrlFromId($id)
    {
        if (! $data = Data::find($id)) {
            return;
        }

        if ($localized = $data->in($this->targetSite()->handle())) {
            $data = $localized;
        } elseif ($this->wantsSpecificSite()) {
            return;
        }

        return $this->wantsAbsoluteUrl() ? $data->absoluteUrl() : $data->url();
    }

    protected function targetSite()
    {
        return Site::get($this->params->get('in', Site::current()->handle()));
    }

    protected function wantsSpecificSite()
    {
        return $this->params->has('in');
    }

    protected function wantsAbsoluteUrl()
    {
        return $this->params->bool('absolute', false);
    }
}

