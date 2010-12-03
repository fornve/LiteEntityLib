<?php

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
			$this->filename = $uri[ count( $uri ) - 1 ];
			unset( $uri[ count( $uri ) - 1 ] );
			$this->path = '/'. implode( '/', $uri );
		}

		// image manipulation methods

		private function Load( $file )
		{
			$image = imagecreatefromstring( file_get_contents( $file ) );
			
			if( !$image )
			{
				error_log( "[ImageHandler Error]: Image {$file} not loaded." );
				exit;
			}
			
			error_log("{$file} loaded.");
			return $image;
		}

		private function DetectImageMimeType()
		{
			$valid_extensions = array( 'jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png' );
			
			$filename = explode( '.',strtolower(  basename( $this->file ) ) );
			$extension = $filename[ count( $filename ) - 1 ];
			
			$this->file_type = $valid_extensions[ $extension ];
		}

		private function GetImageDimensions( $file )
		{
			if( $this->file != $file )
			{
				$image = $this->Load( $file );
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

		private function AddBorders()
		{
			$xpos = 0;
			$ypos = 0;
			$width = $this->width;
			$height = $this->height;

			if( !$this->width || !$this->source_height ) // check if image sizes are > than 0
				return $this->file;

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
			
			if( $this->file_type == 'jpeg' )
			{
				$background = imagecolorallocatealpha( $destination_image, 255, 255, 255, 0 );
			}
			elseif( $this->file_type == 'png' )
			{
				$background = imagecolorallocatealpha( $destination_image, 255, 255, 255, 127 );
			}
			
			imagefill( $destination_image, 0, 0, $background );

			imagecopyresampled( $destination_image, $this->image, $xpos, $ypos, 0, 0, $width, $height, $this->source_width, $this->source_height );

			return $destination_image;

		}

		private function Resize( $source_image )
		{
			// default - stretch image

			if( $this->height < 0 || $this->height === null ) // fit to width
			{
				$this->height = (int)ceil( $this->width / $this->source_ratio );
			}
			elseif( !$this->width < 0 || $this->height === null ) // fit to height
			{
				$this->width = (int)ceil( $this->height * $this->source_ratio );
			}

			$destination_image = imagecreatetruecolor( $this->width, $this->height );
			$a = imagecopyresampled( $destination_image, $source_image, 0, 0, 0, 0, $this->width, $this->height, $this->source_width, $this->source_height );
			
			return $destination_image;
		}

		// private methods

		private function GetCacheFilename()
		{
			if( is_array( $this->options ) )
			{
				$options = implode( '-'. $this->options ) .'-';
			}
			
			$this->cache_filename = "{$this->cache_prefix}{$this->filename}";
			$this->cache_path = "{$this->width}x{$this->height}";
			$this->cache_file = "{$this->cache_root}/{$this->cache_path}/{$this->cache_filename}";

			if( !file_exists( $this->cache_root .'/'. $this->cache_path ) )
			{
				mkdir( $this->cache_root .'/'. $this->cache_path, 0700, true );
			}
		}

		private function GenerateCache()
		{
			if( $this->force_regeneration && file_exists( $this->cache_filename ) ) // force thumbnail regeneration
			{
				unlink( $cache_filename );
			}

			if( $this->width && $this->add_borders )
			{
				$image = $this->AddBorders();
			}
			elseif( $this->width || $this->height )
			{
				$image = $this->Resize( $this->image );
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

		private function GetSourceDimensions()
		{
			$dimensions = $this->GetImageDimensions( $this->file );
			$this->source_width = $dimensions[ 'width' ];
			$this->source_height = $dimensions[ 'height' ];
			$this->source_ratio = $dimensions[ 'ratio' ];
			
			if( !$this->width )
			{	
				$this->width = $this->source_width;
			}
			
			if( !$this->height )
			{
				$this->height = $this->source_height;
			}
		}

		private function Process()
		{
			$this->GetCacheFilename();

			if( !file_exists( $this->cache_file ) || $this->force_regeneration )
			{
				$this->image = $this->Load( $this->file );
				$this->GetSourceDimensions();
				$this->GenerateCache();
			}
			else
			{
				error_log( "Read from cache: {$this->filename}" );
			}
		}

		// public methods

		public function GetUrl()
		{
			$this->Process();
			return "Cached/{$this->cache_path}/{$this->cache_dir}";
		}

		public function Output()
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

			$this->Process();
			header( "Content-Type: image/{$this->file_type}" );
			header( "Pragma: hack" );
			header( "Expires: " . gmdate("D, d M Y H:i:s", time() + 18144000 ) . " GMT" );
			header( "Last-Modified: " . gmdate("D, d M Y H:i:s", time() ) . " GMT" );
			header( "Cache-Control: public"  );
			readfile( $this->cache_file );
		}
	}
