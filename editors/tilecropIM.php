<?php
/**
 * WordPress TileCrop Image Editor
 *
 * @package WordPress
 * @subpackage Image_Editor
 */

/**
 * WordPress Image Editor Class for Image Manipulation through ImageMagick PHP Module with custom cropping/resizing
 *
 * @since 3.5.0
 * @package WordPress
 * @subpackage Image_Editor
 * @uses WP_Image_Editor Extends class
 */
class WP_Image_Editor_TileCrop_IM extends WP_Image_Editor_Imagick {

	public $image = null; // Imagick Object

	/**
	 * Resizes current image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $max_w
	 * @param int $max_h
	 * @param boolean $crop
	 * @return boolean|WP_Error
	 */
	public function resize( $max_w, $max_h, $crop = false ) {
		if ( ( $this->size['width'] == $max_w ) && ( $this->size['height'] == $max_h ) )
			return true;

		$dims = image_resize_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h, $crop );


		if ( ! $dims )
			return new WP_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions') );
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
		

		if($crop > 1){
			
			$src_w = $this->size['width'];
			$src_h = $this->size['height'];
			
			try {

			// Check if CYMK and convert if needed
			$cs = $this->image->getImageColorspace();

			// Determine new canvas size.  It should be equal to the aspect ratio of the target BUT the scale of the source

			if(($src_w / $dst_w) < ($src_h / $dst_h)){
				$keyDim = "height";
				$temp_h = $src_h;
				$temp_w = round($src_w * (($src_h * $dst_w)/($src_w * $dst_h)));
				$cy=0;
				$cx = abs(round(0.5 * ($temp_w - $src_w)));
			}else{
				$keyDim = "width";
				$temp_w = $src_w;
				$temp_h = round($src_h * (($src_w * $dst_h)/($src_h * $dst_w)));
				$cx=0;
				$cy = abs(round(0.5 * ($temp_h - $src_h)));
			}
			// Get background color & format of image
			$pixelColor[] = $this->image->getImagePixelColor(0, 0);
			$pixelColor[] = $this->image->getImagePixelColor(0, $src_h);
			$pixelColor[] = $this->image->getImagePixelColor($src_w, 0);
			$pixelColor[] = $this->image->getImagePixelColor($src_w, $src_h);

			foreach($pixelColor as $pixel){
				$pixelColorStrings[]=$pixel->getColorAsString();
			}

			$pixelColorStringsCounts = array_count_values($pixelColorStrings);
			if(max($pixelColorStringsCounts)>1){
				$bestColor = array_search(max($pixelColorStringsCounts), $pixelColorStringsCounts);
				$pixelNumber = array_search($bestColor,$pixelColorStrings);
			}else{
				$pixelNumber = 0;
				if ($cs == Imagick::COLORSPACE_CMYK) {
					$pixelColor[0]=new ImagickPixel("cmyk(0,0,0,0)");
				}else{
					$pixelColor[0]=new ImagickPixel("rgb(255,255,255)");
				}
				
			}

			foreach($pixelColorStringsCounts as $keyCount => $colorValue){
				$pixelColorStringArray[$keyCount] = $colorValue;
			}
			//error_log("TileCrop WP_Image_Editor_TileCrop_IM Color Value ".$pixelColor[$pixelNumber]->getColorAsString(), 0);
			$format = $this->image->getImageFormat();


			// Create new canvas of correct aspect ratio with background color
			$placeholder = $this->image;

			$tileCropImage = new Imagick();



			$tileCropImage->newImage($temp_w, $temp_h, $pixelColor[$pixelNumber]);
			$tileCropImage->setImageColorspace($cs);

			$tileCropImage->setImageFormat($format);

			$cs2 = $tileCropImage->getImageColorspace();



			// Composite images
			$tileCropImage->compositeImage($this->image, Imagick::COMPOSITE_DEFAULT, $cx, $cy);
			


			// $replace $this->image with composite
			
			$this->image->clear();
			$this->image->destroy();
			$this->image = null;
			$this->image = $tileCropImage->getImage();
			$this->update_size(); 
			$geo = $this->image->getImageGeometry();
			
			

			}
			catch ( Exception $e ) {
				return new WP_Error( 'image_resize_error', $e->getMessage() );
			}


		}elseif ( $crop === true || $crop === 1) {
			return $this->crop( $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h );
		}

		try {
			/**
			 * @TODO: Thumbnail is more efficient, given a newer version of Imagemagick.
			 * $this->image->thumbnailImage( $dst_w, $dst_h );
			 */
			$this->image->scaleImage( $dst_w, $dst_h );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_resize_error', $e->getMessage() );
		}

		return $this->update_size( $dst_w, $dst_h );
	} // end resize()


}// end class


