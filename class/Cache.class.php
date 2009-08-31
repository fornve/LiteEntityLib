<?php

	/*
	 * @package LiteEntityLib http://github.com/fornve/LiteEntityLib/tree
	 * @version 1.0
	 * @author Marek Dajnowski http://sum-e.com/Contactus
	 * @documentation http://www.dajnowski.net/wiki/index.php5/Cache
	 */
	class Cache
	{
		private $cache = null;
		
		function __construct()
		{
			switch( CACHE_TYPE )
			{
				case 'memcache':
				{
					if( !class_exists( 'Memcache' ) )
						return false;

					$this->cache = new Memcache();
					
					if( !$this->cache->connect( CACHE_HOST, (int)CACHE_PORT ) )
						$this->cache = null;
						
					break;
				}

				case 'disk':
				case 'db':
					return false;
			}
		}

		function __destruct()
		{
			$this->close();
		}

		function set( $key, $var , $flag = null, $expire = null )
		{
			if( $this->cache )
				$this->cache->set( $key, $var, $flag, $expire );
		}

		function get( $key, $flags = null )
		{
			if( $this->cache )
				return $this->cache->get( $key, $flags );
		}

		function delete( $key, $timeout = null )
		{
			if( $this->cache )
				$this->cache->delete( $key, $timeout );
		}

		function close()
		{
			if( $this->cache )
				return $this->cache->close();
		}
	}
