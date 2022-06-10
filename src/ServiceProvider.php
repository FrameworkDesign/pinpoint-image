<?php

namespace Weareframework\PinpointImage;

use Statamic\Providers\AddonServiceProvider;
use Weareframework\PinpointImage\Fieldtypes\PinPointImage;
use Weareframework\PinpointImage\Tags\PinpointImageTag;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $tags = [
        PinpointImageTag::class,
    ];

    protected $fieldtypes = [
        PinPointImage::class,
    ];

    public function bootAddon()
    {
        //
    }
}
