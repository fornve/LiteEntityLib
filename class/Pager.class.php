<?php

class Pager
{
	public $max = 0;		// number of elements
	public $elements = 10;	// elements on page
	public $page = 1;		// current page
	public $self = '/';		// base url	
	public $option = null;	// filter option
	public $order = null;	// result order option

	public $elements_loop = null; // read only

	function __construct( $self, $max, $page = 1 )
	{
		$this->self = $self;
		$this->max = $max;

		if( $page < 1 )
			$page = 1;

		$this->page = $page;

		$this->elements_loop = $this->elements + 1;
	}

	function CountPages()
	{
		return ceil( $this->max / $this->elements );
	}

}
