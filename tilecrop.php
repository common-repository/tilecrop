<?php
/*
Plugin Name: TileCrop
Plugin URI: http://wordpress.org/plugins/tilecrop/
Description: Enables images to be cropped to a specific aspect ratio w/o losing any image data.  It ALSO enables upscaling of any tilecropped images.
Author: Tor N. Johnson
Version: 1.0.0
Author URI: http://profiles.wordpress.org/kasigi
License: GPL2 
*/


function image_editors_tilecrop( $editors ) {
	if( ! class_exists('WP_Image_Editor_TileCrop_GD') || ! class_exists('WP_Image_Editor_TileCrop_IM') )
		include_once 'editors/tilecropGD.php';
		include_once 'editors/tilecropIM.php';

	if( ! in_array( 'WP_Image_Editor_TileCrop_IM', $editors ) )
		array_unshift( $editors, 'WP_Image_Editor_TileCrop_GD' );
		array_unshift( $editors, 'WP_Image_Editor_TileCrop_IM' );

	return $editors;
} // end image_editors_tilecrop

add_filter( 'wp_image_editors', 'image_editors_tilecrop' );



function add_image_size_tilecrop( $name, $width = 0, $height = 0, $crop = 2 ) {
	global $_wp_additional_image_sizes;
	$_wp_additional_image_sizes[$name] = array( 'width' => absint( $width ), 'height' => absint( $height ), 'crop' =>  (int) $crop );
} // end add_image_size_tilecrop

