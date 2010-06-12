<?php

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
			switch( CACHE_TYPE )
			{
				case 'memcache':
				{
					if( !class_exists( 'Memcache' ) )
						return false;

					if( !self::$cache )
						self::$cache = new Memcache();

					if( !self::$cache->connect( CACHE_HOST, (int)CACHE_PORT ) )
						self::$cache = null;

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
				if( defined( 'PRODUCTION' ) && !PRODUCTION )
					$_SESSION[ 'cache_query' ][] = "+ ". $key;

				self::$cache->set( $key, $var, $flag, $expire );
			}
		}

		function get( $key, $flags = null )
		{
			if( self::$cache )
			{
				if( defined( 'PRODUCTION' ) && !PRODUCTION )
					$_SESSION[ 'cache_query' ][] = "= ". $key;

				return self::$cache->get( $key, $flags );
			}
		}

		function delete( $key, $timeout = null )
		{
			if( self::$cache )
			{
				if( defined( 'PRODUCTION' ) && !PRODUCTION )
					$_SESSION[ 'cache_query' ][] = "- ". $key;

				self::$cache->delete( $key, $timeout );
			}
		}

		function close()
		{
			if( self::$cache )
				return self::$cache->close();
		}
	}
