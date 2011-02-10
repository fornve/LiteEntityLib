<?php

interface SessionDriver
{
	public function get( $name );
	public function set( $name, $value );
	public function is( $name );
	public function delete( $name );
}
