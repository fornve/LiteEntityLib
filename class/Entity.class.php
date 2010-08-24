<?php
/**
 * @package framework
 * @subpackage entity
 * @author Marek Dajnowski (first release 20080614)
 * @documentation http://dajnowski.net/wiki/index.php5/Entity
 * @latest http://github.com/fornve/LiteEntityLib/tree/master/class/Entity.class.php
 * @version 1.6.alpha - database specific drivers
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
	protected static $instance = null;
	protected $schema = array();
	protected $table_name = null;
	protected $id_name = 'id';
	protected $has_many = array();
	protected $has_one = array();
	public $error;
	public $query;

	function __construct()
	{
		$this->db = Entity::getDB();

		if( !$this->table_name )
		{
			$this->table_name = strtolower( get_class( $this ) );
		}
	}

	public static function &getDB()
	{
		if( !is_object( self::$dblink ) )
		{
			self::$dblink = self::Connect();
		}

		return self::$dblink;
	}

	public static function &getInstance()
	{
		if( !is_object( self::$instance ) )
		{
			self::$instance = new Entity();
		}

		return self::$instance;
	}

	function Connect()
	{
		$driver = isset( self::$driver ) ? self::$driver : Config::get( 'DB_TYPE' );
		$dsn	= isset( self::$dsn ) ? self::$dsn : Config::get( 'DSN' );

		require_once( INCLUDE_PATH ."/drivers/{$driver}.class.php" );

		return new $driver( $dsn );
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
		if( defined( 'DB_TABLE_PREFIX' ) && DB_TABLE_PREFIX )
		{
			$query = $this->Prefix( $query );
		}

		$query = $this->Arguments( $query, $arguments );

		$this->db_query_counter++;

		try
		{
			$result = $this->db->query( $query );

			$this->error = $this->db->error;
			$this->query = $query;

			if( defined( 'PRODUCTION' ) && PRODUCTION === false )
			{
				$_SESSION[ 'entity_query' ][] = $query;
			}
		}
		catch( DbException $e )
		{
			$this->Error( $e->getMessage(), $arguments, $e );
		}

		if( $result === null )
		{
			throw new EntityException( " Warning, query returned null. [ {$query} ] " );
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

		$this->query = $query;

		try
		{
			$timer = microtime( true );

			$result = $this->db->query( $query );

			$timer = round( 1000 * ( microtime( true ) - $timer ), 2);

			$this->db_query_counter++;

			if( $result )
			{
				$this->result = $this->BuildResult( $result, $class );

				if( defined( 'PRODUCTION' ) && PRODUCTION === false )
				{
					$_SESSION[ 'entity_query' ][] = "[{$timer}] ". $query;
				}
			}
		}
		catch( DbException $e )
		{
			$this->Error( $e->getMessage(), $arguments, $e );
		}

		if( $this->db->errno )
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
		{
			return $this->result;
		}
	}

	/*
	 * Builds DAO schema
	 */
	function BuildSchema()
	{
		if( $this->schema )
		{
			return $this->schema;
		}

		$result = $this->db->buildSchema( $this->table_name );

		$objects = $this->BuildResult( $result, 'stdClass' );

		$schema = array();

		if( $objects ) foreach( $objects as &$object )
		{
			if( strlen( $object->Field ) > 0 )
			{
				$schema[] = $object->Field;
			}
		}

		unset( $query, $result, $objects );

		return $this->schema = $schema;
	}

	/**
	 * Retrieve row from database where id = $id ( or id => $id_name  )
	 * @param int $id
	 * @param string $id_name
	 * @param string $class
	 * @return object
	 * Returns object type of entity
	 */
	static function Retrieve( $id, $class, $id_name = 'id' )
	{
		if( $id )
		{
			$object = new $class();
			$object->BuildSchema();
			$entity = Entity::getInstance();

			$query = "SELECT * FROM ". $entity->escapeTable( $object->table_name ) ." WHERE ". $entity->escapeColumn( $id_name ) ." = ? LIMIT 1";

			$object = $entity->GetFirstResult( $query, $id, $class );

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
				$object->$child_name = $child->Retrieve( $this->$child );
			}

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
	function retrieveFromQuery( $query, $arguments, $class = __CLASS__ )
	{
		//$object_name = get_class( $this );
		$object = new $class;
		$table = strtolower( $class );
		$entity = Entity::getInstance();
		$result = $object->GetFirstResult( $query, $arguments );

		if( $result ) foreach( $result as $key => &$value )
		{
			$object->$key = $value;
			return $object;
		}
		else
		{
			return false;
		}
	}

	function save()
	{
		$table = $this->table_name;
		$id = $this->id_name;
		$this->GetSchema(); // force to generate schema

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
					$query .= ', ';

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
			$query = "INSERT INTO ". $this->escapeTable( $this->table_name ) ." ( ". $this->escapeColumn( $this->id_name ). ", ". $this->escapeColumn( $column ) ." ) VALUES ( {$id_value}, '0' )";
		}
		else
		{
			$query = "INSERT INTO ". $this->escapeTable( $this->table_name ) ." ( ". $this->escapeColumn( $column ) ." ) VALUES ( '0' )";
		}

		$this->Query( $query );
		$result = $this->GetFirstResult( "SELECT ". $this->escapeColumn( $this->id_name ) ." FROM ". $this->escapeTable( $this->table_name ) ." WHERE ". $this->escapeColumn( $column ) ." = '0' ORDER BY ". $this->escapeColumn( $this->id_name ) ." DESC LIMIT 1", null, get_class( $this ) );
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
		{
			$this->Collection( $query, $arguments, $class );
		}

		if( isset( $this->result ) && isset( $this->result[ 0 ] ) )
		{
			return $this->result[ 0 ];
		}
	}

	function PreDelete() {}
	function FlushCache() {}

	function Delete()
	{
		$this->PreDelete();

		$query = "DELETE FROM ". $this->escapeTable( $this->table_name ) ." WHERE ". $this->escapeColumn( $this->id_name ) ." = ?";
		$this->query( $query, $this->id );
	}

	/**
	 * Gets all entries from database
	 * @param $class string class name
	 */
	static function GetAll( $class = null )
	{
		if( !$class )
		{
			throw new EntityException( "Entity::GetAll - class name cannot be null." );
		}

		$object = new $class;
		$entity = Entity::getInstance();
		$query = "SELECT * from ". $entity->escapeTable( $object->table_name ) ." ORDER BY ". $entity->escapeColumn( $object->id_name );
		return $entity->Collection( $query, null, $class );
	}

	public function escapeTable( $string )
	{
		return $this->db->escapeTable( $string );
	}

	public function escapeColumn( $string )
	{
		return $this->db->escapeColumn( $string );
	}

	/**
	 * Gets input and sets into object cproperties
	 * @param const $method
	 */
	public function SetProperties( $method = INPUT_POST )
	{
		$input = Common::Inputs( $this->GetSchema(), $method );

		foreach( $this->schema as &$property )
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
		if( $this->GetSchema() ) foreach( $this->schema as &$schema_key )
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
	public static function Array2Entity( $array, $class )
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

	private static function getClass()
	{
		$implementing_class = Entity::$__CLASS__;
		$original_class = __CLASS__;

		return $original_class;
	}

	private function BuildResult( $result, $class )
	{
		return $this->db->fetch( $result, $class );
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

		if( count( $arguments ) ) foreach( $arguments as &$argument )
		{
			if( is_object( $argument ) )
			{
				$argument = "'". $this->db->escape( $argument->id ) ."'";
			}
			elseif( !is_numeric( $argument ) and isset( $argument ) )
			{
				$argument = "'". $this->db->escape( $argument ) ."'";
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
	 * Frees result after multi query
	 */
	/* Deprectated
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
	*/

	function Stripslashes( $result, $schema )
	{
		foreach( $schema as &$key )
		{
			if( isset( $result[ 0 ]->$key ) )
				$result[ 0 ]->$key = stripslashes( $result[ 0 ]->$key );
		}

		return $result;
	}

	function GetPlural( $str )
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

	/**
	 * Email error detais to administrator
	 * @param db resource
	 * @param mixed $arguments
	 */
	private function Error( $message, $attributes = null, $exception )
	{
		if( defined( 'DEVELOPER_EMAIL' ) )
		{
			$headers = "From: Entity crash at {". PROJECT_NAME ."}! <". DEVELOPER_EMAIL .">";
			$message = "Entity object [". get_class( $this ) ."]: \n\n". var_export( $this, true ) ."\n\n{$break}\n\n".
			"Arguments:\n\n".  var_export( $attributes, true ) ."\n\n{$break}\n".
			"Error message:\n\n". $message ."\n\n{$break}\n\n".
			"Server:\n\n". var_export( $_SERVER, true ) ."\n\n{$break}\n\n".
			"POST:\n\n". var_export( $_POST, true ) ."\n\n{$break}\n\n".
			"Session:\n\n". var_export( $_SESSION, true ) ."\n\n".
			"Backrtace\n\n". var_export( $exception->trace );

			mail( DEVELOPER_EMAIL, 'Database entity Collection error', $message, $headers );
		}

		$e = new EntityException( $message, $exception );
		$e->attributes = array(
			'query' => $this->query,
			'attributes' => $attributes
		);

		throw $e;
	}
}
