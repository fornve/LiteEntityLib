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

		require_once( Config::get( 'include-path' ) ."/drivers/session/{$driver}.class.php" );

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
