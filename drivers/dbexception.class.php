<?php

class DbException extends Exception
{
	public $trace = null;

	public function __construct( $message = null, $code = 0 )
	{
		$this->trace = debug_backtrace();
		parent::__construct( $message, $code );
	}
}
