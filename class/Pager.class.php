<?php
/*
 * Copyright (C) 2009 Marek Dajnowski <marek@dajnowski.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class Pager
{
	public $max = 0;		// number of elements (limit)
	public $elements = 10;	// elements on page
	public $page = 1;		// current page
	public $self = '/';		// base url
	public $option = null;	// filter option
	public $order = null;	// result order option
	public $offset;			// Element offset = ( page - 1 ) * elements

	public $elements_loop = null; // read only

	function __construct( $self, $max, $page = 1, $elements = 10 )
	{
		$this->self = $self;
		$this->max = $max;
		$this->elements = $elements;

		if( $page < 1 )
		{
			$page = 1;
		}

		$this->page = $page;
		$this->offset = ( $this->page - 1 ) * $this->elements;

		$this->elements_loop = $this->elements + 1;
	}

	function countPages()
	{
		return ceil( $this->max / $this->elements );
	}

}
