<?php

/**
 * @package framework
 * @subpackage entity
 * @author Marek Dajnowski (first release 20080614)
 * @documentation http://dajnowski.net/wiki/index.php5/Entity
 * @latest http://github.com/fornve/LiteEntityLib/tree/master/class/Entity.class.php
 * @version 1.4-build
 * @License GPL v3
 */

class Entity
{
	private static $dblink;
	protected $db;
	protected $prefix = null;
	protected $multi_query = false;
	public $db_query_counter = 0;
	protected static $__CLASS__ = __CLASS__;
	protected $schema = array();
	protected $table_name = null;
	protected $id_name = 'id';

	function __construct()
	{
		$this->db = Entity::Instance();
		
		if( !$this->table_name )
		{
			$this->table_name = strtolower( get_class( $this ) );
		}
	}

	public static function &Instance()
	{
		if( !is_object( self::$dblink ) )
		{
			self::$dblink = self::Connect();
		}

		return self::$dblink;
	}

	function Connect()
	{
		switch( DB_TYPE )
		{
			case 'mysql':
			{
				$db = new mysqli( DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME );
				break;
			}
			case 'sqlite':
			{
				$db = new SQLite3( DB_FILE );
				break;
			}
			default:
				die( 'Configuration error. Database unknown.' );
		}

		if( !$db )
			die( 'Database connection failed.' );

		return $db;
	}

	function __destruct()
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
	function Query( $query, $arguments = null )
	{
		if( DB_TABLE_PREFIX )
			$query = $this->Prefix( $query );
			
		$query = $this->Arguments( $query, $arguments );

		if( $this->multi_query )
		{
			$result = $this->db->multi_query( $query );
			$this->db_query_counter++;
			$this->freeResult();
			$this->multi_query = false;
		}
		else
		{
			$this->db_query_counter++;

			$result = $this->db->query( $query );
		}

		$this->error = $this->db->error;
		$this->query = $query;
		$_SESSION[ 'entity_query' ][] = $query;

		if( $this->db->errno && !PRODUCTION )
		{
			echo 'Database entity Collection error: ';
			var_dump( $this );
			var_dump( $arguments );
			exit;
		}
		elseif( $this->db->errno )
		{
			$this->Error( $this->db->errno, $arguments );
		}

		if( $result === null )
		{
			echo " Warning, query returned null. [ {$query} ] ";
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
	function Collection( $query, $arguments = null, $class = __CLASS__, $limit = null, $offset = null )
	{
		if( $limit )
		{
			if( $limit )
			{
				$query .= " LIMIT ?";
			}

			if( $offset > 0 )
			{
				$query .= ", ?";
				$arguments[] = $offset;
			}

			$arguments[] = $limit;
		}
		
		$query = $this->Prefix( $query );
		$query = $this->Arguments( $query, $arguments );

		unset( $this->result ); // for object reuse
		$result = $this->db->query( $query );
		$this->db_query_counter++;

		if( $result )
		{
			$this->BuildResult( $result, $class );

			$_SESSION[ 'entity_query' ][] = $query;
		}

		$this->error = $this->db->error;
		$this->query = $query;
		
		if( $this->db->errno && !PRODUCTION )
		{
			echo 'Database entity Collection error: ';
			var_dump( $this );
			var_dump( $arguments );
			exit;
		}
		elseif( $this->db->errno )
		{
			$this->Error( $this->db->error, $arguments );
		}

		if( $class && isset( $this->result ) )
		{
			$class = new $class;

			if( $class->GetSchema() )
			{
				$this->result = Entity::Stripslashes( $this->result, $class->schema );
			}
		}

		if( isset( $this->result ) )
			return $this->result;
	}

	/*
	 * Builds DAO schema
	 */
	function BuildSchema()
	{
		$query = "DESC {$this->table_name}";
	
		$result = $this->db->query( $query );
		$objects = $this->BuildResult( $result, 'stdClass' );

		if( $objects ) foreach( $objects as $object )
		{
			if( strlen( $object->Field ) > 0 )
			{
				$schema[] = $object->Field;
			}
		}
		
		return $this->schema = $schema;
	}
	

	/**
	 * Retrieve column group results
	 * @param int $column
	 * @return object
	 * Returns array of objects type of entity
	 */	
	function TypeCollection( $type )
	{
		if( !in_array( $type, $this->GetSchema() ) )
			return false;

		$table_name = strtolower( get_class( $this ) );
		$query = "SELECT {$type} FROM {$table_name} GROUP BY {$type}";
		return $this->Collection( $query, null, 'Entity' );
	}

	/**
	 * Retrieve row from database where id = $id ( or id => $id_name  )
	 * @param int $id
	 * @param string $id_name
	 * @param string $class
	 * @return object
	 * Returns object type of entity
	 */
	static function Retrieve( $id, $id_name = 'id', $class = __CLASS__ )
	{
		if( is_int( $id ) )
		{
			$object = new $class;
			$object->BuildSchema();
			$entity = new Entity();
			$query = "SELECT * FROM `{$object->table_name}` WHERE `{$id}` = ? LIMIT 1";
			$object = $entity->GetFirstResult( $query, $id, $class );

			if( !$object )
				return null;

			return $object;
		}
		
	}

	/**
	 * Returns DAO object of first result (row) in given query
	 * @param string $query
	 * @param mixed $arguments
	 * @param string $class
	 * @return object
	 */
	function RetrieveFromQuery( $query, $arguments, $class = __CLASS__ )
	{
		//$object_name = get_class( $this );
		$object = new $class;
		$table = strtolower( $class );
		$entity = new Entity();
		$result = $object->GetFirstResult( $query, $arguments );

		if( $result ) foreach( $result as $key => $value )
		{
			$object->$key = $value;
			return $object;
		}
		else
			return $false;
	}

	function Save()
	{
		$table = $this->table_name;
		$id = $this->id_name;
		$this->GetSchema(); // force to generate schema

		if( !$this->$id )
			$this->$id = $this->Create( $table );

		$query = "UPDATE `{$table}` SET ";

		foreach( $this->schema as $property )
		{
			if( $property != $this->schema[ 0 ] )
			{
				if( $notfirst )
					$query .= ', ';

				$query .= " `{$property}` = ?";
					
				if( is_object( $this->$property ) )
					$arguments[] = $this->$property->id;
				else
					$arguments[] = $this->$property;

				$notfirst = true;
			}
		}

		$query .= " WHERE {$this->id_name} = ?";
		$arguments[] = $this->{$id};

		$this->Query( $query, $arguments );
	}

	//function Update() { $this->Save(); }

	/**
	 * Creates new entry in $table and returns id
	 * @param string $table
	 * @return  int
	 */
	function Create( $table, $id_value = null )
	{
		$this->GetSchema(); 
		$id = & $this->id_name;
		$column = $this->schema[ 1 ];

		if( $id_value )
		{
			$query = "INSERT INTO `{$this->table_name}` ( `{$this->id_name}`, `{$column}` ) VALUES ( {$id_value}, 0 )";
		}
		else
		{
			$query = "INSERT INTO `{$this->table_name}` ( `{$column}` ) VALUES ( 0 )";
		}

		$this->Query( $query );
		$result = $this->GetFirstResult( "SELECT {$this->id_name} FROM `{$this->table_name}` WHERE `{$column}` = 0 ORDER BY `{$this->id_name}` DESC LIMIT 1", null, get_class( $this ) );
		return $result->$id;
	}

	/**
	 *
	 * @param string $query
	 * @param mixed $arguments
	 * @param string $class
	 * @return object
	 */
	function GetFirstResult( $query, $arguments = null, $class = __CLASS__ )
	{
		if( $query )
			$this->Collection( $query, $arguments, $class );

		return $this->result[ 0 ];
	}

	function PreDelete() {}
	function FlushCache() {}

	function Delete()
	{
		$this->PreDelete();

		$query = "DELETE FROM `{$this->table_name}` WHERE {$this->id_name} = ?";
		$this->query( $query, $this->id );
	}

	/**
	 * Gets all entries from database
	 * @param $class string class name
	 */
	static function GetAll( $class = null )
	{
		if( !$class )
			die( "Entity::GetAll - class name cannot be null." );

		$object = new $class;
		$query = "SELECT * from `{$object->table_name}`";
		$entity = new Entity();
		return $entity->Collection( $query, null, $class );
	}

	/**
	 * Gets input and sets into object cproperties
	 * @param const $method
	 */
	public function SetProperties( $method = INPUT_POST )
	{
		$input = Common::Inputs( $this->GetSchema(), $method );

		foreach( $this->schema as $property )
		{
			$this->$property = $input->$property;
		}
	}

	/**
	 * Returns schema
	 * @return array
	 */
	public function GetSchema()
	{
		if( count( $this->schema ) < 1 )
		{
			$this->BuildSchema();
		}
	
		return $this->schema;
	}

	public function InSchema( $key )
	{
		if( $this->GetSchema() ) foreach( $this->schema as $schema_key )
		{
			if( $key == $schema_key )
				return true;
		}
	}

	/**
	 * Converts array into object
	 * @return object
	 */ 
	public static function Array2Entity( $array, $class )
	{
		if( $array ) 
		{
			$object = new $class();	

			foreach ( $array as $key => $value )
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

	private static function getClass()
	{
		$implementing_class = Entity::$__CLASS__;
		$original_class = __CLASS__;

		return $original_class;
	}

	private function BuildResult( $result, $class )
	{
		if( DB_TYPE == 'mysql' )
		{
			if( $result ) while( $row = mysqli_fetch_object( $result, $class ) )
			{
				$this->result[] = $row;
			}
		}
		else
		{
			while( $row = $result->fetchArray() )
			{
				$this->result[] = Entity::Array2Entity( $row, $class );
			}
		}

		return $this->result;
	}

	/**
	 * Parse prefix - useful if shared database
	 * @param string $query
	 * @return string
	 */
	private function Prefix( $query )
	{
		//global $mosConfig_dbprefix; // joomla 1.0
		if( defined( 'DB_TABLE_PREFIX' ) )
		{
			$exp = explode( '#__', $query );
			$query = implode( DB_TABLE_PREFIX, $exp );
		}

		return $query;
	}

	/**
	 * Injects escaped arguments into query
	 * @param string $query
	 * @param mixed $arguments
	 * @return string
	 */
	private function Arguments( $query, $arguments = null )
	{
		$query = explode( '?', $query );
		$i = 0;

		if( !is_array( $arguments ) and $arguments !== null )
		{
			$arguments = array( $arguments );
		}

		$new_query = '';

		if( count( $arguments ) ) foreach( $arguments as $argument )
		{
			if( is_object( $argument ) )
			{
				$argument = "'". $this->Escape( $argument->id ) ."'";
			}
			elseif( !is_numeric( $argument ) and isset( $argument ) )
			{
				$argument = "'". $this->Escape( $argument ) ."'";
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

	/*
	 * Performs argument escape
	 */
	private function Escape( $string )
	{
		if( DB_TYPE == 'mysql' )
			return $this->db->escape_string( $string );
		elseif( DB_TYPE == 'sqlite' )
			return $this->db->escapeString( $string );
	}

	/*
	 * Frees result after multi query
	 */
	private function freeResult()
	{
		do
		{
			// store first result set
			if ($result = $this->db->store_result()) 
			{
				$result->free();
			}
			
			// print divider
			$this->db->more_results();
		}
		while ( $this->db->next_result() );
	}

	function Stripslashes( $result, $schema )
	{
		foreach( $schema as $key )
		{
			 $result[ 0 ]->$key = stripslashes( $result[ 0 ]->$key );

		}

		return $result;
	}

	/**
	 * Email error detais to administrator
	 * @param mixed $arguments 
	 */
	private function Error( $db, $arguments )
	{
		if( defined( PRODUCTION ) && defined( ADMIN_EMAIL ) )
		{
			$break = "=================================================================";
			$headers = "From: Entity crash bum bum at {". PROJECT_NAME ."}! <www@". PROJECT_NAME .">";
			$message = "Entity object: \n\n". var_export( $this, true ) ."\n\n{$break}\n\nArguments:\n\n".  var_export( $this, true ) ."\n\n{$break}\n\Database error:\n\n". var_export( $db, true ) ."\n\n{$break}\n\nServer:\n\n". var_export( $_SERVER, true ) ."\n\n{$break}\n\nPOST:\n\n". var_export( $_POST, true ) ."\n\n{$break}\n\nSession:\n\n". var_export( $_SESSION, true );

			mail( ADMIN_EMAIL, 'Database entity Collection error', $message, $headers );
		
			header( "Location: /Error/Database" );
			exit;
		}
		else
		{
			var_dump( $this->error, $this->query );
		}
		
	}

}
