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

class postgresql implements dbdriver
{
	protected $resource;

	public function __construct( $dns )
	{
		$this->connect( $dns );
	}

	public function connect( $dns )
	{
		$this->resource = pg_connect( $dns );

		if( !$this->resource )
		{
			throw new DbException( 'Error connecting postgresql database.' );
		}
	}

	public function buildSchema( $table )
	{
		$result = pg_meta_data( $this->resource, $table );
		return array_keys( $result );
	}

	public function query( $query )
	{
		$result = pg_query( $this->resource, $query );

		if( pg_last_error( $this->resource ) )
		{
			throw new DbException( pg_last_error( $this->resource ) );
		}

		return $result;
	}

	public function fetch( $result, $class = 'stdClass' )
	{
		$return = array();

		$i = 1;

		if( $result && pg_num_rows( $result ) ) while( $row = pg_fetch_object( $result, $class ) )
		{
			$return[] = $row;

			$i++;
			if( $i > pg_num_rows( $result ) )
			{
				break;
			}
		}

		return $return;
	}

	public function escape( $string )
	{
		return pg_escape_string( $this->resource, $string );
	}

	public function escapeTable( $string )
	{
		return '"'. $string .'"';
	}

	public function escapeColumn( $string )
	{
		return '"'. $string .'"';
	}

	public function escapeData( $string )
	{
		return "'". $string ."'";
	}

	public function disconnect()
	{
		if( is_object( $this->resource ) )
		{
			pg_close( $this->resource );
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}
}
