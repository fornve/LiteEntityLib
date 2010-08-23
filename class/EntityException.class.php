<?php

class EntityException extends Exception
{
	public $backtrace = null;

	public function __construct  ( $message, $code = 0, Exception $previous = null )
	{
		$this->backtrace = debug_backtrace( true );
		parent::__construct( $message, $code, $previous );
	}
}
