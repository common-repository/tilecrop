<?php
/**
 * WordPress TileCrop Image Editor
 *
 * @package WordPress
 * @subpackage Image_Editor
 */

/**
 * WordPress Image Editor Class for Image Manipulation through GD PHP Module with custom cropping/resizing
 *
 * @since 3.5.0
 * @package WordPress
 * @subpackage Image_Editor
 * @uses WP_Image_Editor Extends class
 */
class WP_Image_Editor_TileCrop_GD extends WP_Image_Editor_GD {

	public $image = false; // GD Resource
/**
	 * Resizes current image.
	 * Wraps _resize, since _resize returns a GD Resource.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $max_w
	 * @param int $max_h
	 * @param boolean $crop
	 * @return boolean|WP_Error
	 */
	

	protected function _resize( $max_w, $max_h, $crop = false ) {
		$dims = image_resize_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h, $crop );
		if ( ! $dims ) {
			return new WP_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions'), $this->file );
		}
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

		if($crop > 1){
			
			$src_w = $this->size['width'];
			$src_h = $this->size['height'];

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
			$pixelColor[] = imagecolorat($this->image, 0, 0);
			$pixelColor[] = imagecolorat($this->image, 0, ($src_h-1));
			$pixelColor[] = imagecolorat($this->image, ($src_w-1), 0);
			$pixelColor[] = imagecolorat($this->image, ($src_w-1), ($src_h-1));

			$pixelColorStringsCounts = array_count_values($pixelColor);

			if(max($pixelColorStringsCounts)>1){
				$bestColor = array_search(max($pixelColorStringsCounts), $pixelColorStringsCounts);
				$colorArray['alpha'] = ($bestColor & 0x7F000000) >> 24;
				$colorArray['red'] = ($bestColor >> 16) & 0xFF;
            			$colorArray['green'] = ($bestColor >> 8) & 0xFF;
           			$colorArray['blue'] = $bestColor & 0xFF;
			}else{
				$colorArray = array('red' => 255,'green' => 255,'blue' => 255,'alpha' => 0);
			}


			// Create new canvas of correct aspect ratio with background color
			$tileCropImage = wp_imagecreatetruecolor($temp_w, $temp_h);

			// Apply Fill Color
			$color = imagecolorallocatealpha($tileCropImage,  $colorArray['red'],$colorArray['green'],$colorArray['blue'], $colorArray['alpha']);
			imagefill($tileCropImage, 0, 0, $color);
			imagesavealpha($tileCropImage, TRUE);

			// Composite images
			imagecopy($tileCropImage,$this->image,$cx,$cy,0,0,$src_w,$src_h);

		}
		
		$resized = wp_imagecreatetruecolor( $dst_w, $dst_h );

		if(is_resource($tileCropImage)){
			imagecopyresampled( $resized, $tileCropImage, 0, 0, 0, 0, $dst_w, $dst_h, $temp_w, $temp_h );
		}else{
			imagecopyresampled( $resized, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
		}
		if ( is_resource( $resized ) ) {
			$this->update_size( $dst_w, $dst_h );
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}






}// end class
