<section class="pinpoint-image">
    @if(isset($image['url']))
        <img class="pinpoint-image__image" src="{{ $image['url'] ?? '' }}">
    @endif
    @if(isset($annotations) && count($annotations) > 0)
        @foreach($annotations as $key => $annotation)
            <div class="pinpoint-image__annotation" style="top: {{ $annotation['y'] ?? 0 }}%; left: {{ $annotation['x'] ?? 0 }}%;">
                <div class="pinpoint-image__annotationKey">
                    <div class="pinpoint-image__annotationKeyInner">
                    {{ ($key + 1) }}
                    </div>
                </div>
                <div class="pinpoint-image__annotationHover">
                    {{ $annotation['data']['heading'] ?? '' }}
                </div>
            </div>
        @endforeach
    @endif
</section>

<style>
    .pinpoint-image {
        position: relative;
    }
    .pinpoint-image__image {
        position: relative;
        width: 100%;
    }
    .pinpoint-image__annotation {
        position: absolute;
    }
    .pinpoint-image__annotationKey {
        position: absolute;
        z-index: 1;
        border-radius: 50% 50% 0;
        transform: rotate(45deg);
        background-color: #303750;
        color: #fff;
        height: 30px;
        width: 30px;
        text-align: center;
        line-height: 30px;
        font-weight: 700;
        font-size: 12px;
        cursor: pointer;
    }
    .pinpoint-image__annotationKeyInner {
        transform: rotate(-45deg);
    }
    .pinpoint-image__annotationHover {
        position: relative;
        z-index: 5;
        padding: 8px;
        background-color: #303750;
        color: #fff;
        line-height: 1.1;
        font-weight: 700;
        font-size: 12px;
        pointer-events: none;
        display: none;
    }
    .pinpoint-image__annotationKey:hover + .pinpoint-image__annotationHover {
        display: block;
    }
</style>
