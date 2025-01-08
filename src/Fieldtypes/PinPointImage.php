<?php

namespace Weareframework\PinpointImage\Fieldtypes;

use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Fields\Fields as BlueprintFields;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Assets\UndefinedContainerException;
use Statamic\Statamic;
use Statamic\Support\Str;
use Weareframework\PinpointImage\GraphQL\PinPointImageFieldType;

class PinPointImage extends Fieldtype
{
    protected $categories = ['media', 'relationship'];
    protected $defaultValue = [
        'image' => '',
        'annotations' => []
    ];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.assets.config.mode'),
                'type' => 'select',
                'default' => 'list',
                'options' => [
                    'grid' => __('Grid'),
                    'list' => __('List'),
                ],
                'width' => 50,
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.assets.config.container'),
                'type' => 'asset_container',
                'max_items' => 1,
                'mode' => 'select',
                'width' => 50,
            ],
            'folder' => [
                'display' => __('Folder'),
                'instructions' => __('statamic::fieldtypes.assets.config.folder'),
                'type' => 'asset_folder',
                'max_items' => 1,
                'width' => 50,
            ],
            'restrict' => [
                'display' => __('Restrict'),
                'instructions' => __('statamic::fieldtypes.assets.config.restrict'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'allow_uploads' => [
                'display' => __('Allow Uploads'),
                'instructions' => __('statamic::fieldtypes.assets.config.allow_uploads'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'show_filename' => [
                'display' => __('Show Filename'),
                'instructions' => __('statamic::fieldtypes.assets.config.show_filename'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'has_max_fields' => [
                'display' => __('Has max fields limit'),
                'instructions' => 'Do you wish to limit how many fields a user can add?',
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'max_fields' => [
                'type' => 'integer',
                'display' => 'Max Fields',
                'default' => 5,
                'instructions' => 'If toggle above is on, then how many fields do you wish to limit it to?',
                'min' => 1,
            ],
        ];
    }

    public function canHaveDefault()
    {
        return false;
    }

    /*
     * how to load your data in
     */
    public function preProcess($values)
    {
//        dd('preProcess first?');
        if (is_null($values) || empty($values)) {
            return null;
        }

        return $this->getData($values);
    }

    public function process($data)
    {
        return $data;
    }

    protected function fields()
    {
        $fields = $this->config('fields');

        return new BlueprintFields($fields, $this->field()->parent(), $this->field());
        //return new BlueprintFields($this->configFieldItems());
    }

    public function preload()
    {
        $version = Statamic::version();
        $versionArray = explode('.', $version);
        $showAssetOption = $this->showAssetOption();
        $entries = Entry::whereCollection('pages');

        $entryFieldtype = $this->nestedEntriesFieldtype(null);
        $assetFieldtype = $showAssetOption ? $this->nestedAssetsFieldtype(null) : null;

        return [
            'collections' => $this->collections(),
            'default' => $this->defaultValue(),
            'data' => $this->getData($this->field->value() ?? []),
            'container' => $this->container()->handle(),
            'showAssetOption' => $showAssetOption,
            'entries' => $entries,
            'initEntry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ],
            'initAsset' => $showAssetOption ? [
                'config' => $assetFieldtype->config(),
                'meta' => $assetFieldtype->preload(),
            ] : null,
            'statamic_version' => $version,
            'statamic_major_version' => isset($versionArray[0]) ? (int) $versionArray[0] : 4,
        ];
    }

    public function getData($values)
    {
        if ( empty($values['annotations'])) {
            return $values;
        }

        $values['annotations'] = collect($values['annotations'])->map(function($value) {
            if ( !isset($value['data']['fields']) ) {
                return $value;
            }
            $value['data']['fields'] = collect($value['data']['fields'])->map(function($field) {
                $field = match ($field['value']) {
                    'link' => $this->linkField($field),
                    default => $field
                };
//                dd($field,'$fieldhere?');
                return $field;
            })->all();
            return $value;
        })->all();

//        dd($values, 'preload', $this->fields());
        return $values;
    }

    protected function container()
    {
        if ($configured = $this->config('container')) {
            if ($container = AssetContainer::find($configured)) {
                return $container;
            }

            throw new AssetContainerNotFoundException($configured);
        }

        if (($containers = AssetContainer::all())->count() === 1) {
            return $containers->first();
        }

        throw new UndefinedContainerException;
    }

    public function preProcessIndex($data)
    {
        return [];
    }

    private function showAssetOption()
    {
        return $this->config('container') !== null;
    }

    public function toGqlType()
    {
        return GraphQL::type(PinPointImageFieldType::NAME);
    }

    private function linkField($field)
    {
        $showAssetOption = $this->showAssetOption();

        $selectedEntry = $field['content'] && Str::startsWith($field['content'], 'entry::') ? Str::after($field['content'], 'entry::') : null;
        $selectedAsset = $field['content'] && Str::startsWith($field['content'], 'asset::') ? Str::after($field['content'], 'asset::') : null;

        $url = ($field !== '@child' && ! $selectedEntry && ! $selectedAsset) ? $field['content'] : null;

        $entryFieldtype = $this->nestedEntriesFieldtype($selectedEntry);
        $assetFieldtype = $showAssetOption ? $this->nestedAssetsFieldtype($selectedAsset) : null;

        $field['meta']['initialUrl'] = $url;
        $field['meta']['showAssetOption'] = $showAssetOption;

        $field['meta']['initialOption'] = $this->initialOption($field['content'], $selectedEntry, $selectedAsset);
        $field['meta']['initialSelectedEntries'] = $selectedEntry ? [$selectedEntry] : [];
        $field['meta']['initialSelectedAssets'] = $selectedAsset ? [$selectedAsset] : [];
        $field['meta']['entry'] = [
            'config' => $entryFieldtype->config(),
            'meta' => $entryFieldtype->preload(),
        ];
        $field['meta']['asset'] = $showAssetOption ? [
            'config' => $assetFieldtype->config(),
            'meta' => $assetFieldtype->preload(),
        ] : null;
        return $field;
    }

    private function initialOption($value, $entry, $asset)
    {
        if (! $value) {
            return $this->field->isRequired() ? 'url' : null;
        }

        if ($value === '@child') {
            return 'first-child';
        } elseif ($entry) {
            return 'entry';
        } elseif ($asset) {
            return 'asset';
        }

        return 'url';
    }

    private function nestedEntriesFieldtype($value): Fieldtype
    {
        $entryField = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]));

        $entryField->setValue($value);

        $entryField->setConfig(array_merge(
            $entryField->config(),
            ['collections' => $this->collections()]
        ));

        return $entryField->fieldtype();
    }

    private function nestedAssetsFieldtype($value): Fieldtype
    {
        $assetField = (new Field('entry', [
            'type' => 'assets',
            'max_files' => 1,
            'mode' => 'list',
        ]));

        $assetField->setValue($value);

        $assetField->setConfig(array_merge(
            $assetField->config(),
            ['container' => $this->config('container')]
        ));

        return $assetField->fieldtype();
    }

    private function collections()
    {
        $collections = $this->config('collections');

        if (empty($collections)) {
            $site = Site::current()->handle();

            $collections = Blink::once('routable-collection-handles-'.$site, function () use ($site) {
                return Collection::all()->reject(function ($collection) use ($site) {
                    return is_null($collection->route($site));
                })->map->handle()->values()->all();
            });
        }

        return $collections;
    }
}
