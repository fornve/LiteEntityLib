<?php

class Filelog
{
	function Write( $contents )
	{
		$file = LOG_DIRECTORY .'/'. date( "Y-m-d" ) .'.log';
		$contents = date( "Y-m-d H:i:s - " ) ."{$contents}\n";
		file_put_contents( $file, $contents, FILE_APPEND );
	}
}
