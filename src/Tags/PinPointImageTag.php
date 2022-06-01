<?php

namespace Weareframework\PinpointImage\Tags;

use Statamic\Support\Str;
use Statamic\Tags\Tags;

class PinPointImageTag extends Tags
{
    public static function render(...$arguments): string
    {
        return view('pinpoint-image::tags.index')->render();
    }
}

