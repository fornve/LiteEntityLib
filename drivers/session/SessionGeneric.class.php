<?php

require_once( 'SessionDriver.class.php' );

class SessionGeneric implements SessionDriver
{
	public function get( $name )
	{
		return $_SESSION[ $name ];
	}

	public function set( $name, $value )
	{
		$_SESSION[ $name ] = $value;
	}

	public function is( $name )
	{
		return isset( $_SESSION[ $name ] );
	}

	public function delete( $name )
	{
		unset( $_SESSION[ $name ] );
	}
}
