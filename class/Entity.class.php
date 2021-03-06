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
/**
 * @package framework
 * @subpackage entity
 * @author Marek Dajnowski (first release 20080614)
 * @documentation http://dajnowski.net/wiki/index.php5/Entity
 * @latest http://github.com/fornve/LiteEntityLib/tree/master/class/Entity.class.php
 * @version 1.6.1
 * @License GPL v3
 */
class Entity
{
	/*
	 * Database driver link
	 */
	protected static $db;

	/*
	 * Table prefix - usefull for shared databases
	 */
	protected $prefix = null;

	/*
	 * Multi query switch - use very carefully
	 */
	protected $multi_query = false;

	/*
	 * Query counter for benchmarking
	 */
	public $db_query_counter = 0;

	/*
	 * Singleton holder
	 */
	protected static $instance = null;

	/*
	 * Schema - whoch represents column names in db
	 */
	protected $schema = array();

	/*
	 * Table name - if null, strtolower( __CLASS__ ) will be used as class name
	 */
	protected $table_name = null;

	/*
	 * Id name - 'id' as default
	 */
	protected $id_name = 'id';

	/*
	 * Nested multiple collection. As in kohana model - to be finished
	 */
	protected $has_many = array();

	/*
	 * Nested single collection. As in kohana model - to be finished
	 */
	protected $has_one = array();

	/*
	 * Determines wether object is going to be saved or not
	 */
	protected $updated = false;
	public $error;
	public $query;

	function __construct()
	{
		Entity::getDB();

		if( !$this->table_name )
		{
			$this->table_name = strtolower( get_class( $this ) );
		}
	}

	public static function getDB()
	{
		if( !is_object( self::$db ) )
		{
			self::$db = self::Connect();
		}

		return self::$db;
	}

	public static function &getInstance()
	{
		if( !is_object( self::$instance ) )
		{
			self::$instance = new Entity();
		}

		return self::$instance;
	}

	public function connect()
	{
		$driver = isset( self::$driver ) ? self::$driver : Config::get( 'DB_TYPE' );
		$dsn	= isset( self::$dsn ) ? self::$dsn : Config::get( 'DSN' );

		require_once( Config::get( 'include-path' ) ."/drivers/entity/{$driver}.class.php" );

		return new $driver( $dsn );
	}

	public function __destruct()
	{
		/*if ( is_object( $this->db ) )
		{
			@$this->db->close(); // sometimes throws warning - to be investigated - we don't like @
		}*/
	}

	/**
	 * Execute query on database - works exactly like Collection but won't return results
	 * @param string $query
	 * @param mixed $arguments
	 */
	public function query( $query, $arguments = null )
	{
		if( Config::get( 'db.table-prefix' ) )
		{
			$query = $this->prefix( $query );
		}

		$query = $this->arguments( $query, $arguments );

		$this->db_query_counter++;

		try
		{
			$result = self::$db->query( $query );

			$this->error = self::$db->error;
			$this->query = $query;

			if( !Config::get( 'production' ) )
			{
				$_SESSION[ 'entity_query' ][] = $query;
			}
		}
		catch( DbException $e )
		{
			$exception = new EntityException( $e->getMessage(), $arguments, $e );
			$exception->query = $query;
			throw $exception;
		}

		if( $result === null )
		{
			throw new EntityException( "Warning, query returned null. [ {$query} ] ", $arguments );
		}
	}

	/**
	 * Enity objects collection from table - rows => array( row => object of entity )
	 * @param string $query
	 * @param mixed $arguments
	 * @param string $class
	 * @return array
	 * Returns array of objects
	 */
	public function collection( $query, $arguments = null, $class = null, $limit = null, $offset = null )
	{
		if( !$class && !function_exists( 'get_called_class' ) )
		{
			throw new EntityException( 'In PHP < 5.3 $class must be specified in ::collection' );
		}
		elseif( !$class )
		{
			$class = get_called_class();
		}

		if( strtolower( $class ) == 'entity' )
		{
			throw new EntityException( 'Something went wrong, entity class can not do ::collection on itself.' );
		}

		if( $limit )
		{
			if( $limit )
			{
				$query .= " LIMIT ?";
			}

			if( $offset > 0 )
			{
				$query .= ", ?";
				$arguments[] = (int) $offset;
			}

			$arguments[] = (int) $limit;
		}

		$query = $this->Prefix( $query );
		$query = $this->Arguments( $query, $arguments );

		$this->query = $query;

		try
		{
			if( Config::get( 'production' ) === false )
			{
				$timer = microtime( true );
			}

			$result = self::$db->query( $query );

			if( Config::get( 'production' ) === false )
			{
				$timer = round( 1000 * ( microtime( true ) - $timer ), 2);
			}

			$this->db_query_counter++;

			if( $result )
			{
				$this->result = $this->buildResult( $result, $class );

				if( Config::get( 'production' ) === false )
				{
					$_SESSION[ 'entity_query' ][] = "[{$timer}] ". $query;
				}
			}
		}
		catch( DbException $e )
		{
			$exception = new EntityException( $e->getMessage(), $arguments, $e );
			$exception->query = $query;
			throw $exception;
		}

		if( $class && isset( $this->result ) )
		{
			$class = new $class();

			if( $class->getSchema() )
			{
				$this->result = Entity::stripslashes( $this->result, $class->schema );
			}
		}

		if( isset( $this->result ) )
		{
			return $this->result;
		}
	}

	/*
	 * Builds DAO schema
	 * @return array
	 */
	public function buildSchema()
	{
		if( count( $this->schema ) > 0 )
		{
			return $this->schema;
		}

		try
		{
			if( Config::get( 'production' ) === false )
			{
				$timer = microtime( true );
			}

			$this->schema = self::$db->buildSchema( $this->getTableName() );

			if( count( $this->schema ) > 0 )
			{
				if( Config::get( 'production' ) === false )
				{
					$timer = round( 1000 * ( microtime( true ) - $timer ), 2);
					$_SESSION[ 'entity_query' ][] = "[{$timer}] Schema build for '{$this->table_name}'";
				}
			}

		}
		catch( DbException $e )
		{
			throw new EntityException( $e->getMessage(), null, $e );
		}

		return $this->schema;
	}

	public function getDataObject()
	{
		$data = new stdClass();

		foreach( $this->getSchema() as $key )
		{
			$data->$key = $this->$key;
		}

		return $data;
	}

	public function getTableName()
	{
		if( !$this->table_name )
		{
			$this->table_name = get_class();
		}

		return $this->table_name;
	}

	/**
	 * Retrieve row from database where id = $id ( or id => $id_name  )
	 * @param int $id
	 * @param string $class
	 * @param string
	 * @return object
	 * Returns object type of entity
	 */
	public static function retrieve( $id, $class = null, $by_field = null )
	{
		if( !$class && !function_exists( 'get_called_class' ) )
		{
			throw new EntityException( 'In PHP < 5.3 $class must be specified in ::collection' );
		}
		elseif( !$class )
		{
			$class = get_called_class();
		}

		if( $id )
		{
			$object = new $class();
			$object->buildSchema();
			$entity = Entity::getInstance();

			if( $by_field === null )
			{
				$by_field = $object->id_name;
			}
			else
			{
				if( !in_array( $by_field, $object->getSchema() ) )
				{
					throw new EntityException( "Column {$by_field} does not exist." );
				}
			}

			$query = "SELECT * FROM ". $entity->escapeTable( $object->table_name ) ." WHERE ". $entity->escapeColumn( $by_field ) ." = ? LIMIT 1";

			$object = $entity->getFirstResult( $query, $id, $class );

			if( !$object )
			{
				return null;
			}

			if( isset( $object->has_many ) && count( $object->has_many ) ) foreach( $object->has_many as &$child )
			{
				$child_name = strtolower( self::GetPlural( $child ) );
				$child_object = new $child();
				$object->$child_name = $child_object->ChildCollection( $class, $object->id );
			}

			if( isset( $object->has_one ) && count( $object->has_one ) ) foreach( $object->has_one as &$child )
			{
				$child_object = new $child();
				$child_name = strtolower( $child );
				$object->$child_name = self::Retrieve( $object->$child_name, $child );
			}

			$object->updated = false;

			return $object;
		}

	}

    /**
	 * Gets kids collection
	 * @param   string  $child_class        Child class name
	 * @param   string  $parent_class       Parent class name
	 * @param   int     $parent_id          Parent id
	 * @return  array                       Returns array of objects
	 */
	protected final function childCollection( $parent_class, $parent_id )
    {
        $query = "SELECT * FROM ". $this->escapeTable( $this->table_name )." WHERE ". $this->escapeColumn( strtolower( $parent_class ) ) ." = ?";
        $entity = Entity::getInstance();
        return $entity->Collection( $query, array( $parent_id ), get_class( $this ) );
    }

	/**
	 * Returns DAO object of first result (row) in given query
	 * @param string $query
	 * @param mixed $arguments
	 * @param string $class
	 * @return object
	 */
	public function retrieveFromQuery( $query, $arguments, $class = __CLASS__ )
	{
		//$object_name = get_class( $this );
		$object = new $class;
		$table = strtolower( $class );
		$entity = Entity::getInstance();
		$result = $object->GetFirstResult( $query, $arguments );

		if( $result )
		{
			foreach( $result as $key => &$value )
			{
				$object->$key = $value;
			}

			$object->updated = false;

			return $object;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Save object
	 * Will update all properties in database as defined in $this->schema
	 */
	public function save()
	{
		$id = $this->id_name;

		/* Needs finish
		if( !$this->updated )
		{
			return $this->$id;
		}
		*/

		$table = $this->table_name;
		$this->getSchema(); // force to generate schema

		if( !$this->$id )
		{
			$this->$id = $this->Create( $table );
		}

		$query = "UPDATE ". $this->escapeTable( $table ) ." SET ";

		$notfirst = false;

		foreach( $this->schema as &$property )
		{
			if( $property != $this->schema[ 0 ] )
			{
				if( $notfirst )
				{
					$query .= ', ';
				}

				$query .= $this->escapeColumn( $property ) ."= ?";

				if( is_object( $this->$property ) )
				{
					$arguments[] = $this->$property->id;
				}
				else
				{
					$arguments[] = $this->$property;
				}

				$notfirst = true;
			}
		}

		$query .= " WHERE ". $this->escapeColumn( $this->id_name ) ." = ?";
		$arguments[] = $this->{$id};

		$this->Query( $query, $arguments );

		$this->updated = false;
	}

	//function Update() { $this->Save(); }

	/**
	 * Creates new entry in $table and returns id
	 * @param string $table
	 * @return  int
	 */
	public function create( $table, $id_value = null )
	{
		$this->getSchema();
		$id = & $this->id_name;
		$column = $this->schema[ 1 ];

		if( $id_value )
		{
			$query = "INSERT INTO ". $this->escapeTable( $this->table_name ) ." ( ". $this->escapeColumn( $this->id_name ). ", ". $this->escapeColumn( $column ) ." ) VALUES ( {$id_value}, '0' )";
		}
		else
		{
			$query = "INSERT INTO ". $this->escapeTable( $this->table_name ) ." ( ". $this->escapeColumn( $column ) ." ) VALUES ( '0' )";
		}

		$this->Query( $query );
		$result = $this->GetFirstResult( "SELECT ". $this->escapeColumn( $this->id_name ) ." FROM ". $this->escapeTable( $this->table_name ) ." WHERE ". $this->escapeColumn( $column ) ." = '0' ORDER BY ". $this->escapeColumn( $this->id_name ) ." DESC LIMIT 1", null, get_class( $this ) );

	$this->updated = false;

		return $result->$id;
	}

	/**
	 * Get first result of query
	 *
	 * @param string $query
	 * @param mixed $arguments
	 * @param string $class
	 * @return object
	 */
	public function getFirstResult( $query, $arguments = null, $class = null )
	{
		if( $query )
		{
			$this->collection( $query, $arguments, $class );
		}

		if( isset( $this->result ) && isset( $this->result[ 0 ] ) )
		{
			return $this->result[ 0 ];
		}
	}

	public function preDelete() {}

	public static function flatQuery( $query )
	{
		return str_replace( '  ', ' ', str_replace( "\n", ' ', $query ) );
	}

	public function flushCache() {}

	public function delete()
	{
		$this->PreDelete();

		$id_name = $this->id_name;

		$query = "DELETE FROM ". $this->escapeTable( $this->table_name ) ." WHERE ". $this->escapeColumn( $id_name ) ." = ?";
		$id_name = $this->id_name;
		$this->query( $query, $this->$id_name );
	}

	/**
	 * Gets all entries from database
	 * @param $class string class name
	 */
	public static function getAll( $class, $limit = null, $offset = null )
	{
		if( !$class )
		{
			throw new EntityException( "Entity::getAll - class name cannot be null." );
		}

		$object = new $class;
		$entity = Entity::getInstance();
		$query = "SELECT * from ". $entity->escapeTable( $object->table_name ) ." ORDER BY ". $entity->escapeColumn( $object->id_name );

		return $entity->Collection( $query, null, $class, $limit, $offset );
	}

	public function getId()
	{
		$id_name = $this->id_name;
		return $this->$id_name;
	}

	public function escapeTable( $string )
	{
		return self::$db->escapeTable( $string );
	}

	public function escapeColumn( $string )
	{
		return self::$db->escapeColumn( $string );
	}

	/**
	 * Gets input and sets into object cproperties
	 * @param const $method
	 */
	public function setProperties( $method = INPUT_POST )
	{
		$input = Common::inputs( $this->getSchema(), $method );

		foreach( $this->schema as &$property )
		{
			$this->$property = $input->$property;
		}
	}

	/**
	 * Returns schema
	 * @return array
	 */
	public function getSchema()
	{
		if( count( $this->schema ) < 1 )
		{
			$this->schema = $this->buildSchema();
		}

		return $this->schema;
	}

	public function inSchema( $key )
	{
		if( $this->getSchema() ) foreach( $this->schema as &$schema_key )
		{
			if( $key == $schema_key )
			{
				return true;
			}
		}
	}

	/**
	 * Converts array into object
	 * @return object
	 */
	public static function array2Entity( $array, $class )
	{
		if( $array )
		{
			$object = new $class();

			foreach ( $array as $key => &$value )
			{
				if( !is_numeric( $key ) )
				{
					$object->$key = $value;
				}
			}
		}

		return $object;
	}

	/* BIG FAT WARNING! VERY DANGEROUS!!! */
	/*function multiQuery( $query, $arguments = null )
	{
		$this->multi_query = true;
		$this->Query( $query, $arguments );
	}*/

	protected function buildResult( $result, $class )
	{
		return self::$db->fetch( $result, $class );
	}

	/**
	 * Parse prefix - useful if shared database
	 * @param string $query
	 * @return string
	 */
	protected function prefix( $query )
	{
		//global $mosConfig_dbprefix; // joomla 1.0
		if( Config::get( 'db.table-prefix' ) )
		{
			$exp = explode( '#__', $query );
			$query = implode( Config::get( 'db.table-prefix' ), $exp );
		}

		return $query;
	}

	/**
	 * Injects escaped arguments into query
	 * @param string $query
	 * @param mixed $arguments
	 * @return string
	 */
	protected function arguments( $query, $arguments = null )
	{
		$query = explode( '?', $query );
		$i = 0;

		if( !is_array( $arguments ) and $arguments !== null )
		{
			$arguments = array( $arguments );
		}

		$new_query = '';

		if( count( $arguments ) ) foreach( $arguments as &$argument )
		{
			if( is_object( $argument ) )
			{
				$argument = self::$db->escapeData( self::$db->escape( $argument->id ) );
			}
			elseif( !is_numeric( $argument ) and isset( $argument ) )
			{
				$argument = self::$db->escapeData( self::$db->escape( $argument ) );
			}
			elseif( !isset( $argument ) )
			{
				$argument = 'NULL';
			}

			$new_query .= $query[ $i++ ] . $argument;
		}
		$new_query .= $query[ $i ];

		return $new_query;
	}

	public function stripslashes( $result, $schema )
	{
		foreach( $schema as &$key )
		{
			if( isset( $result[ 0 ]->$key ) )
				$result[ 0 ]->$key = stripslashes( $result[ 0 ]->$key );
		}

		return $result;
	}

	public function getPlural( $str )
	{
		if( preg_match( '/[sxz]$/', $str ) OR preg_match( '/[^aeioudgkprt]h$/', $str ) )
		{
			$str .= 'es';
		}
		elseif( preg_match( '/[^aeiou]y$/', $str ) )
		{
			// Change "y" to "ies"
			$str = substr_replace( $str, 'ies', -1 );
		}
		else
		{
			$str .= 's';
		}

		return $str;
	}

	/*
	 * Return limited array - something like SQL's LIMIT
	 */
	public static function limitArray( $array, $limit = null, $offset = null )
	{
		if( $limit === null && $offset === null )
		{
			return $array;
		}

		if( !is_array( $array ) || empty( $array ) )
		{
			return $array;
		}

		$offset_counter = 0;
		$limit_counter = 1;

		$result_array = array();

		foreach( $array as $index => $item )
		{
			if( $offset_counter >= $offset || $offset_counter === null )
			{
				if( $limit_counter <= $limit || $limit === null )
				{
					$result_array[ $index ] = $item;
					$limit_counter++;
				}
			}

			$offset_counter++;
		}

		unset( $offset_counter, $limit_counter );

		return $result_array;
	}

	public function __set( $variable, $value )
	{
		if( !isset( $this->$variable ) )
		{
			 $this->$variable = null;
		}

		if( !in_array( $variable, $this->schema ) && $value !== $this->$variable )
		{
			$this->updated = true;
		}

		$this->$variable = $value;
	}
}
