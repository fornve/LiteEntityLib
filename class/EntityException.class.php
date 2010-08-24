<?php

class EntityException extends Exception
{
	public $trace = null;

	public function __construct( $message, $previous = null )
	{

		if( $previous )
		{
			$this->previous = $previous;
		}
		else
		{
			$this->trace = debug_backtrace( true );
		}

		parent::__construct( $message, 0, $previous );
	}
}
