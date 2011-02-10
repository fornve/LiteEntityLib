<?php

interface dbdriver
{
	public function connect( $dsn );

	public function buildSchema( $table );

	public function query( $query );

	public function fetch( $result, $class = 'stdClass');

	public function escape( $string );

	public function escapeTable( $string );

	public function escapeColumn( $string );

	public function escapeData( $string );

	public function disconnect();
}
