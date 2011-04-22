<?php
/*
 * Copyright (C) 2010 Marek Dajnowski <marek@dajnowski.net>
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

require_once( 'dbdriver.class.php' );
require_once( 'dbexception.class.php' );

class mysql implements dbdriver
{
	protected $resource;

	public function __construct( $dns )
	{
		$return = $this->connect( $dns );
	}

	public function connect( $dns )
	{
		$this->resource = new mysqli( $dns[ 'host' ], $dns[ 'user' ], $dns[ 'password' ], $dns[ 'database' ] );

		if( !$this->resource )
		{
			throw new DbException( 'Error connecting mysql database.' );
		}
	}

	public function buildSchema( $table )
	{
		$query = "DESC `{$table}`";

		$result = $this->query( $query );

		$objects = $this->fetch( $result, 'stdClass' );

		if( $objects ) foreach( $objects as &$object )
		{
			if( strlen( $object->Field ) > 0 )
			{
				$schema[] = $object->Field;
			}
		}

		return $schema;
	}

	public function query( $query )
	{
		$result = $this->resource->query( $query );

		if( $this->resource->error )
		{
			$e = new DbException( $this->resource->error );
			$e->query = $query;
			throw $e;
		}

		return $result;
	}

	public function fetch( $result, $class = 'stdClass' )
	{
		$return = array();

		$i = 1;

		if( $result && $this->resource->affected_rows > 0 ) while( $row = $result->fetch_object( $class ) )
		{
			$return[] = $row;

			$i++;
			if( $i > $this->resource->affected_rows )
			{
				break;
			}
		}

		return $return;
	}

	public function escape( $string )
	{
		return $this->resource->real_escape_string( $string );
	}

	public function escapeTable( $string )
	{
		return "`{$string}`";
	}

	public function escapeColumn( $string )
	{
		return "`{$string}`";
	}

	public function escapeData( $string )
	{
		return "'{$string}'";
	}

	public function disconnect()
	{
		if( is_object( $this->resource ) )
		{
			$thread_id = $mysqli->thread_id;
			if( $thread_id )
			{
				$mysqli->kill( $thread_id );
			}
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}
}
