<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/*
 * @package LiteEntityLib http://github.com/fornve/LiteEntityLib/tree
 * @version 1.0
 * @author Marek Dajnowski http://sum-e.com/Contactus
 * @documentation http://www.dajnowski.net/wiki/index.php5/Cache
 */
class Cache
{
	protected static $cache = null;
	protected static $instance = null;

	public static function &getInstance()
	{
		if( !self::$instance )
		{
			self::$instance = new Cache();
		}

		return self::$instance;
	}

	function __construct()
	{
		if( !Config::_( 'cache' ) )
		{
			return false;
		}

		switch( Config::_( 'cache.type' ) )
		{
			case 'memcache':
			{
				if( !class_exists( 'Memcache' ) )
					return false;

				if( !self::$cache )
				{
					self::$cache = new Memcache();
				}

				if( !self::$cache->connect( Config::_( 'cache.host' ), (int)Config::_( 'cache.port' ) ) )
				{
					self::$cache = null;
				}

				break;
			}

			case 'disk':
			case 'db':
				return false;
		}
	}

	function __destruct()
	{
		//$this->close();
	}

	function set( $key, $var , $flag = null, $expire = null )
	{
		if( self::$cache )
		{
			if( !Config::_( 'production' ) )
			{
				$_SESSION[ 'cache_query' ][] = "+ ". $key;
			}

			self::$cache->set( $key, $var, $flag, $expire );
		}
	}

	function get( $key, $flags = null )
	{
		if( self::$cache )
		{
			if( !Config::_( 'production' ) )
			{
				$_SESSION[ 'cache_query' ][] = "= ". $key;
			}

			return self::$cache->get( $key, $flags );
		}
	}

	function delete( $key, $timeout = null )
	{
		if( self::$cache )
		{
			if( !Config::_( 'production' ) )
			{
				$_SESSION[ 'cache_query' ][] = "- ". $key;
			}

			self::$cache->delete( $key, $timeout );
		}
	}

	function close()
	{
		if( self::$cache )
		{
			return self::$cache->close();
		}
	}
}
