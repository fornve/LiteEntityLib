<?php

class EntityException extends Exception
{
	public $trace = null;
	public $arguments = null;

	public function __construct( $message, $arguments = null, $previous = null )
	{
		$this->arguments = $arguments;		

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
