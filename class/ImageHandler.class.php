<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @package framework
 */
class ImageHandler
{
	public $cache_root = '/tmp';	// cache root directory
	public $cache_prefix = 'cache-';
	public $width = 0;
	public $height = 0;
	public $force_regeneration = false;
	public $add_borders = false;
	public $jpeg_quality = 100; // int {0-100} - 100 means 100% quality
	public $limit = 1600; // height / width limit (in pixels)
	public $zoom_up = true; // Should system zoom up smaller images to requested height?
	public $autorotate = true; // Autorotate image by exif information

	/*
	 * This method prepares all variables
	 * Should not take any action
	 *
	 * @param file string path/filename to original file
	 * @param width int Output with in pixels
	 * @param height int Output height in pixels
	 */
	function __construct( $file, $width = 0, $height = 0 )
	{
		if( !file_exists( $file ) )
			return false;

		$this->file = $file;
		$this->width = $width;
		$this->height = $height;
		$this->DetectImageMimeType();

		$uri = explode( '/', $file );
		$this->filename = str_replace( array( '/', '.' ), array( '-', '-' ), $file );
		unset( $uri[ count( $uri ) - 1 ] );
		$this->path = '/'. implode( '/', $uri );
	}

	// image manipulation methods

	protected function load( $file )
	{
		$image = imagecreatefromstring( file_get_contents( $file ) );
		$background = imagecolorallocatealpha( $image, 255, 255, 255, 127 );

		if( !$image )
		{
			throw new Exception( "Image {$file} not loaded." );
		}

		if( $this->autorotate )
		{
			$exif = exif_read_data( $file );

			switch( $exif[ 'Orientation' ] )
			{
				case 1: // nothing
				break;

				case 2: // horizontal flip
				//	$image->flipImage($public,1);
				break;

				case 3: // 180 rotate left
					$image = imagerotate( $image, 180, $background );
				break;

				case 4: // vertical flip
				//	$image->flipImage($public,2);
				break;

				case 5: // vertical flip + 90 rotate right
				//	$image->flipImage($public, 2);
						$image = imagerotate( $image, -90, $background );
				break;

				case 6: // 90 rotate right
					$image = imagerotate( $image, -90, $background );
				break;

				case 7: // horizontal flip + 90 rotate right
				//	$image->flipImage($public,1);
					$image = imagerotate( $image, -90, $background );
				break;

				case 8:    // 90 rotate left
					$image = imagerotate( $image, 90, $background );
				break;
			}
		}

		return $image;
	}

	protected function detectImageMimeType()
	{
		$valid_extensions = array( 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png' );

		$filename = explode( '.', strtolower(  basename( $this->file ) ) );
		$extension = $filename[ count( $filename ) - 1 ];

		$this->file_type = $valid_extensions[ $extension ];
	}

	protected function getImageDimensions( $file )
	{
		if( $this->file != $file )
		{
			$image = $this->load( $file );
		}
		else
		{
			$image = $this->image;
		}

		$dimensions[ 'width' ] = imagesx ( $image );
		$dimensions[ 'height' ] = imagesy ( $image );
		$dimensions[ 'ratio' ] = $dimensions[ 'width' ] / $dimensions[ 'height' ];

		return $dimensions;
	}

	protected function addBorders()
	{

		if( !$this->zoom_up )
		{
			if( $this->width > $this->source_width && $this->height > $this->source_height )
			{
				$this->width = $this->source_width;
				$this->height = $this->source_height;
			}
			elseif( $this->width > $this->source_width && $this->height < $this->source_height )
			{
				$this->width = $this->source_width;
				$this->height = $this->height;
			}
			elseif( $this->width < $this->source_width && $this->height > $this->source_height )
			{
				$this->height = $this->source_height;
				$this->width = $this->width;
			}
		}

		$xpos = 0;
		$ypos = 0;
		$width = $this->width;
		$height = $this->height;

		if( !$this->width || !$this->source_height ) // check if image sizes are > than 0
		{
			return $this->file;
		}

		if( $this->source_width / $this->source_height > 1 && $width / $height >= 1  ) // fit to width
		{
			$resize_ratio = $this->width / $this->source_width;
			$ypos = (int)ceil( ( $this->height - $this->source_height * $resize_ratio ) / 2 );
			$height = (int)floor( $this->source_height * $resize_ratio );
		}
		else // fit to height
		{
			$resize_ratio = $this->height / $this->source_height;
			$xpos = (int)ceil( ( $this->width - $this->source_width * $resize_ratio ) / 2 );
			$width = (int)floor( $this->source_width * $resize_ratio );
		}

		$destination_image = imagecreatetruecolor( $this->width, $this->height );

		imagesavealpha( $destination_image, true );

		if( strtolower( $this->file_type ) == 'jpeg' )
		{
			$background = imagecolorallocatealpha( $destination_image, 255, 255, 255, 0 );
		}
		elseif( strtolower( $this->file_type ) == 'png' )
		{
			$background = imagecolorallocatealpha( $destination_image, 255, 255, 255, 127 );
		}

		imagefill( $destination_image, 0, 0, $background );

		imagecopyresampled( $destination_image, $this->image, $xpos, $ypos, 0, 0, $width, $height, $this->source_width, $this->source_height );

		return $destination_image;

	}

	protected function resize( $source_image )
	{
		// default - stretch image

		if( $this->height < 1 || $this->height === null ) // fit to width
		{
			$this->height = (int)ceil( $this->width / $this->source_ratio );
		}
		elseif( !$this->width < 1 || $this->height === null ) // fit to height
		{
			$this->width = (int)ceil( $this->height * $this->source_ratio );
		}

		if( !$this->zoom_up )
		{
			if( $this->width > $this->source_width && $this->height > $this->source_height )
			{
				$this->width = $this->source_width;
				$this->height = $this->source_height;
			}
			elseif( $this->width > $this->source_width && $this->height < $this->source_height )
			{
				$this->width = $this->source_width;
				$this->height = $this->height;
			}
			elseif( $this->width < $this->source_width && $this->height > $this->source_height )
			{
				$this->height = $this->source_height;
				$this->width = $this->width;
			}
		}

		$destination_image = imagecreatetruecolor( $this->width, $this->height );
		$a = imagecopyresampled( $destination_image, $source_image, 0, 0, 0, 0, $this->width, $this->height, $this->source_width, $this->source_height );

		return $destination_image;
	}

	// protected methods

	protected function getCacheFilename()
	{
		if( is_array( $this->options ) )
		{
			// default - stretch image

			if( $this->height < 1 || $this->height === null ) // fit to width
			{
				$this->height = (int)ceil( $this->width / $this->source_ratio );
			}
			elseif( !$this->width < 1 || $this->height === null ) // fit to height
			{
				$this->width = (int)ceil( $this->height * $this->source_ratio );
			}

			$destination_image = imagecreatetruecolor( $this->width, $this->height );
			$a = imagecopyresampled( $destination_image, $source_image, 0, 0, 0, 0, $this->width, $this->height, $this->source_width, $this->source_height );

			return $destination_image;
		}

		$this->cache_filename = "{$this->cache_prefix}{$this->filename}";
		$this->cache_path = "{$this->width}x{$this->height}";
		$this->cache_file = "{$this->cache_root}/{$this->cache_path}/{$this->cache_filename}";

		if( !file_exists( $this->cache_root .'/'. $this->cache_path ) )
		{
			mkdir( $this->cache_root .'/'. $this->cache_path, 0700, true );
		}
	}

	protected function generateCache()
	{
		if( $this->force_regeneration && file_exists( $this->cache_filename ) ) // force thumbnail regeneration
		{
			unlink( $cache_filename );
		}

		if( $this->width && $this->add_borders )
		{
			$image = $this->addBorders();
		}
		elseif( $this->width || $this->height )
		{
			$image = $this->resize( $this->image );
		}
		else
		{
			$image = $this->image;
		}

		if( $this->file_type == 'jpeg' )
		{
			$created = imagejpeg( $image, $this->cache_file, $this->jpeg_quality );
		}
		elseif( $this->file_type == 'png' )
		{
			$created = imagepng( $image, $this->cache_file, 9 );
		}

		if( $created )
		{
			error_log( "Image {$this->cache_file} created." );
		}
		else
		{
			error_log( "[ImageHandler Error]: Image {$this->cache_file} not created." );

		}
	}

	protected function getSourceDimensions()
	{
		$dimensions = $this->getImageDimensions( $this->file );
		$this->source_width = $dimensions[ 'width' ];
		$this->source_height = $dimensions[ 'height' ];
		$this->source_ratio = $dimensions[ 'ratio' ];

		if( !$this->width && !$this->height )
		{
			$this->width = $this->source_width;
			$this->height = $this->source_height;
		}

		if( !$this->width )
		{
			$resize = $this->height / $this->source_height;
			$this->width = (int) floor( $this->source_width * $resize );
		}

		if( !$this->height )
		{
			$resize = $this->width / $this->source_width;
			$this->height = (int) floor( $this->source_height * $resize );
		}
	}

	protected function process()
	{
		$this->getCacheFilename();

		if( !file_exists( $this->cache_file ) || $this->force_regeneration )
		{
			$this->image = $this->load( $this->file );
			$this->getSourceDimensions();
			$this->generateCache();
		}
		else
		{
		//	error_log( "Read from cache: {$this->filename}" );
		}
	}

	// public methods

	public function getUrl()
	{
		$this->Process();
		return "Cached/{$this->cache_path}/{$this->cache_dir}";
	}

	public function output()
	{
		// max height / width limit
		if( $this->width > $this->limit )
		{
			$this->width = $this->limit;
		}

		if( $this->height > $this->limit )
		{
			$this->height = $this->limit;
		}

		$this->process();
		header( "Content-Type: image/{$this->file_type}" );
		header( "Pragma: hack" );
		header( "Expires: " . gmdate("D, d M Y H:i:s", time() + 18144000 ) . " GMT" );
		header( "Last-Modified: " . gmdate("D, d M Y H:i:s", time() ) . " GMT" );
		header( "Cache-Control: public"  );
		readfile( $this->cache_file );
	}
}
