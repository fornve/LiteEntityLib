<?php

	class Form
	{
		public $fields = array();
		public $submit = array( 'value' => 'Submit' );
		public $posted = false;

		function __construct( $action = "/", $method = 'get' )
		{
			$this->action = $action;
			$this->method = $method;

			if( $method == strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) )
				$this->posted = true;
		}

		function Validate()
		{
			$error = 0;

			if( !$this->posted )
				return false;

			if( $this->fields ) foreach( $this->fields as $field )
			{
				if( isset( $field->error ) )
					$error += count( $field->error );
			}

			if( $error > 0 )
				return false;
			else
				return true;
		}
	}
