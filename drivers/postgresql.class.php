<?php

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
		$query = "\d {$table}";
		$result = $this->query( $query );
	}

	public function query( $query )
	{
		$result = pg_query( $this->resource, $query );

		if( pg_last_error( $this->resource ) )
		{
			throw new DbException( $this->resource->error );
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
			if($i > pg_num_rows( $result ) ) break;
		}
debug_print_backtrace();
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
