<?php
	/**
	 * @package anadvert
	 * @subpackage framework
	 */
	class Common
	{
		function Redirect( $target = '/' )
		{
			header( "Location: {$target}" );
			exit;
		}

		static function Inputs( $array, $input_type = INPUT_GET )
		{
			$input = new stdClass;

			foreach ( $array as $key )
			{
				$input->$key = addslashes( filter_input( $input_type, $key ) );
			}

			return $input;
		}

	}