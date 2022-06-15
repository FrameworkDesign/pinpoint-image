# Pinpoint Image

> Pinpoint image is a Statamic addon that does something pretty neat. Oh yes, thats right, we need to explain that bit.
> Right then, It allows you to create your own pinpoint image! 
> 
> hmmmm, that still doesnt explain it, does it?
> 
> Perhaps an image, people like pictures:

![Alt text](assets/pinpoint-img-cp.png "Optional title")

## Features

This addon does:

- Allows you to add your own pinpoint image (which was wonderfully described above)
- Edit the outputted field on the frontend


## License
Pinpoint image requires a license to be used while on a production site.
You can purchase one at https://statamic.com/addons/weareframework/pinpoint-image.

You may use Pinpoint image without a license while developing locally.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require weareframework/pinpoint-image
```

## How to Use

Install it, use it, let your site abuse it


This can be used within Antlers using the product_color_swatch tag:

```twig
{{ pinpoint_image :field="pinpoint_image_field" }}
```

by default it outputs this:
![Alt text](assets/pinpoint-img-front.png "Optional title")

You can hover on each point to get your text you typed in the cp admin.

## Templating

Pass field to the `pinpoint_image` tag.

```twig
{{ pinpoint_image :field="pinpoint_image_field" }}
```

This will render a default pinpoint_image tag with the default output as shown above. Nice

## Customizing the generated html

If you want to customize the generated html, you can publish the views using

```bash
php artisan vendor:publish
```

and choosing `Weareframework\PinpointImage\ServiceProvider`
