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

class sqlite implements dbdriver
{
	protected $resource;

	public function __construct( $file )
	{
		$this->connect( $file );
	}

	public function connect( $file )
	{
		$this->resource = new sqlite3( $file );

		if( !$this->resource )
		{
			throw new DbException( 'Error connecting sqlite database.' );
		}
	}

	public function query( $query )
	{
		$result = $this->resource->query( $query );

		if( $this->resource->error )
		{
			throw new DbException( $this->resource->error );
		}
	}

	public function fetch( $result, $class = 'stdClass' )
	{
		$result = array();

		if( $result )
		{
			foreach( $row = $result->fetchArray() )
			{
				$object = new $class();

				foreach( $row as $key => $value )
				{
					$object->$key = $value;
				}

				$result[] = $object;
			}
		}

		return $result;
	}

	public function escape( $string )
	{
		return $this->resource->escapeString( $string );
	}

	public function escapeTable( $string )
	{
		// To be fixed
		return $string;
	}

	public function escapeColumn( $string )
	{
		// To be fixed
		return $string;
	}

	public function disconnect()
	{
		if( is_object( $this->resource ) )
		{
			$this->resource->close();
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}
}
