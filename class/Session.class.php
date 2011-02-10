<?php

class Session
{
	protected static $instance;

	public function __construct()
	{
		$driver = Config::get( 'session_driver' ); 

		if( !$driver )
		{
			$driver = 'SessionGeneric';
		}

		require_once( INCLUDE_PATH ."/drivers/session/{$driver}.class.php" );

		$this->driver = new $driver();
		self::$instance = $this;
	} 

	public function getInstance()
	{
		if( !is_object( self::$instance ) )
		{
			self::$instance = new Session();
		}

		return self::$instance;
	} 

	public function get( $name )
	{
		$session = self::getInstance();

		if( $session->driver->is( $name ) )
		{
			return $session->driver->get( $name );
		}
	} 

	public function set( $name, $value )
	{
		$session = self::getInstance();
		return $session->driver->set( $name, $value );
	} 

	public function is( $name )
	{
		$session = self::getInstance();
		return $session->driver->is( $name );
	}

	public function delete( $name )
	{
		$session = self::getInstance();

		if( $session->driver->is( $name ) )
		{
			return $session->driver->delete( $name );
		}
	} 
}
