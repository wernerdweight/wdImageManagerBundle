Werner Dweight's ImageManager Bundle
====================================

This bundle adds support for image resizing and cropping.

[![Build Status](https://travis-ci.org/wernerdweight/wdImageManagerBundle.svg?branch=master)](https://travis-ci.org/wernerdweight/wdImageManagerBundle)
[![SymfonyInsight](https://insight.symfony.com/projects/ef51680d-2247-462e-8284-e6ceac4eff32/mini.svg)](https://insight.symfony.com/projects/ef51680d-2247-462e-8284-e6ceac4eff32)
[![SymfonyInsight](https://insight.symfony.com/projects/ef51680d-2247-462e-8284-e6ceac4eff32/mini.svg)](https://insight.symfony.com/projects/ef51680d-2247-462e-8284-e6ceac4eff32)


Installation
------------

1) Download using composer

```js
    {
        "require": {
            "wernerdweight/image-manager-bundle": "~3.0"
        }
    }
```

2) Enable the bundle

Enable the bundle in your kernel:

```php
    <?php
    // config/bundles.php
    return [
        // ...
        WernerDweight\ImageManagerBundle\WDImageManagerBundle::class => ['all' => true],
    ];
```

3) Setup config

```yml
# config/packages/wd_image_manager.yaml
wd_image_manager:
    upload_root: absolute/path/to/web/directory # typically %kernel.root_dir%/../web
    upload_path: relative/path/for/storing/images # e.g. uploads/images
    secret: thisIsNotSecret # you can use app secret (%secret%) or any other custom secret (needed for encryption)
    autorotate: true # default false (if true, an attempt will be made to automatically rotate images based on exif data)
    versions:
        version_name_x: # this key will be used as name of directory to where this version of images will be saved
            width: 200  # desired image width in pixels (if you ommit width or height image will keep its original dimensions)
            height: 200 # desired image height in pixels (if you ommit width or height image will keep its original dimensions)
            crop: true  # set this to true if you want the resulting image to have EXACTLY the dimensions specified (default false)
            watermark: # if you don't want your images to be watermarked, do not set this key at all
                file: absolute/path/to/watermark/file # e.g. %kernel.root_dir%/../web/watermark.png
                size: 50 # css-like watermark size; possible values: cover/contain/percentage (integer 0 - 100)
                position:
                    top: 50 # css-like position from top in percent (integer 0 - 100; default 100 - align to bottom)
                    left: 50 # css-like position from left in percent (integer 0- 100; default 100 - align to right)
        encrypted_version_name:
            encrypted: true # if encrypted is set to true original image will be saved encrypted (intended for image download restrictions)
        ...
            
```

Usage
-----

How to use:
```php
        // this example expects you to pass image manager via DI into $imageManager
        
        // upload your image file(s), create database records etc.
        ...

        // process image (one by one)
        // 1st parameter ($file) is instance of Symfony\Component\HttpFoundation\File\UploadedFile (mandatory)
        // 2nd parameter is the filename of newly created file (mandatory)
        // 3rd parameter is a path under upload_path from config (optional)
        $processedImageInfo = $imageManager->processImage($file, 'destination-filename', '/optional/subpath');

```

License
-------
This bundle is under the MIT license. See the complete license in the root directiory of the bundle.
