=== TileCrop ===
Contributors: kasigi
Tags: image
Requires at least: 3.5
Stable tag: 1.0.0 
Tested up to: 4.4.2

Extends the resize/crop function to allow images to be matted and enforce aspect ratios.

== Description ==

The traditional wordpress image resizing engine will resize images to fit within the specified size but not preserve the destination pixel dimensions OR will crop the image. This plugin gives a third alternative - the image will be reduced to fit within the specified size and then the canvas extended to preserve the destination pixel dimensions. The canvas will then be filled in based on a sampling of the colors in the four corners of the image.


== Screenshots ==

1. This is an example of the code in functions.php


== Installation ==

1. Upload the folder `TileCrop` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add tilecrop image definitions in the functions.php of the theme. Example below:

	if ( function_exists('add_image_size_tilecrop') ){
		add_image_size_tilecrop( 'sponsorLogo', 140, 60, 2 ); //300 pixels wide (and unlimited height)
	}else{
		add_image_size( 'sponsorLogo', 140, 60, false ); //300 pixels wide (and unlimited height)
	}

Note: Tilecrop uses a non-standard crop value to engage the crop engine.  Instead of the usual true/false value, the crop value MUST be 2 to engage the tilecrop logic.


== Changelog ==

= 0.1 (2013-07-23) =
* Alpha version
= 1.0.0 (2013-08-28) =
* Initial Release

== Requirements ==

This plugin requires that either ImageMagick or GD be installed on the server.
