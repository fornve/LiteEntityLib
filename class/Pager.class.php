<?php

class Pager
{
	public $max = 0;		// number of elements
	public $elements = 10;	// elements on page
	public $offset = 1;		// offset ( page ) - not elements
	public $self = '/';		// base url	
	public $option = null;	// filter option
	public $order = null;	// result order option

	public $elements_loop = null; // read only

	function __construct( $self, $max, $offset = 1 )
	{
		$this->self = $self;
		$this->max = $max;

		if( $offset < 1 )
			$offset = 1;

		$this->offset = $offset;

		$this->elements_loop = $this->elements + 1;
	}

	function CountPages()
	{
		return ceil( $this->max / $this->elements );
	}

}
