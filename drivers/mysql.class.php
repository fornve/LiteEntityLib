<?php

require_once( 'dbdriver.class.php' );
require_once( 'dbexception.class.php' );

class mysql implements dbdriver
{
	protected $resource;

	public function __construct( $dns )
	{
		$this->connect( $dns );
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
			throw new DbException( $this->resource->error );
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
			$this->resource->close();
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}
}
