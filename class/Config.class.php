<?php

class Config
{
	static $data = array();

	public static function set( $variable, $value )
	{
		self::$data[ $variable ] = $value;
	}

	public static function get( $variable )
	{
		return self::$data[ $variable ];
	}
}
