Werner Dweight's ImageManager Bundle
====================================

**Warning:**

> This bundle IS STILL UNDER DEVELOPMENT and though it may work in a way
> quite a lot of code is still missing! Usage of this bundle is strongly
> discouraged until the version 1.0 is released!

This bundle adds support for image resizing and cropping.

Installation
------------

1) Download using composer

```js
	{
	    "require": {
	        "friendsofsymfony/user-bundle": "~1.3"
	    }
	}
```

2) Enable the bundle

Enable the bundle in your kernel:

```php
	<?php
	/// app/appKernel.php

	public function registerBundles()
	{
    	$bundles = array(
        	/// ...
        	new WernerDweight\ImageManagerBundle\wdImageManagerBundle(),
    	);
	}
```

Usage
-----

Code to be used inside a controller:
```php
		/// get manager
		$im = $this->get('wdImageManager');
		/// load image
		$im->loadImage('path/to/image.jpg');
		/// resize image
		$im->resize(800,600);
		/// crop image
		$im->crop(800,400);
		/// save image
		$im->saveImage('path/','name','png',100);
```

Resizing and cropping can be done at once like this:

```php
		/// if third parameter is set to true, cropping will take effect when resiznig
		$im->resize(800,600,true);
```

License
-------
This bundle is under the MIT license. See the complete license in the root directiory of the bundle.
