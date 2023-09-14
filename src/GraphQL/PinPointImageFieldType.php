<?php

namespace Weareframework\PinpointImage\GraphQL;

use Statamic\Facades\Asset;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\ArrayType;
use Statamic\Http\Resources\API\AssetResource;

class PinPointImageFieldType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'PinPointImage';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'label' => [
                'type' => GraphQL::string(),
                'description' => 'The breakpoint label.',
            ],
            'image' => [
                'type' => GraphQL::type(ArrayType::NAME),
                'resolve' => function ($field) {

                    if (! isset($field['image'])) {
                        return [];
                    }

                    return collect($field['image'])->map(function ($url) {
                        return ($asset = Asset::find($url))
                            ? (new AssetResource($asset))->resolve()
                            : null;
                    })->filter()->values()->first();
                }
            ],
            'annotations' => [
                'type' => GraphQL::type(ArrayType::NAME),
                'resolve' => function ($field) {

                    if (!isset($field['annotations'])) {
                        return [];
                    }

                    return $field['annotations'];
                },
            ]
        ];
    }
}
