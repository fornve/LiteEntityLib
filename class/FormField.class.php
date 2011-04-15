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
class FormField
{
	public $value = null;
	public $checked = null;
	public $error = null;

	/*
	 * Name of field - <input name="{$field->name}" ...
	 */
	public $name = null;

	/*
	 * Label text for field
	 */
	public $label = null;

	/*
	 * Field type, can be one of: text, textarea, select, checkbox, radio, hidden
	 */
	public $type = 'text';

	/*
	 * Field description text
	 */
	public $description = null;

	/*
	 * Options for select field. Must be associative array with key as option value, and value as option text.
	 */
	public $options = array();

	function __construct( $name, $label, $type = 'text', $default = null )
	{
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;

		/*
		 * Problem with checkboxes? Set $form->fields[ 'something' ]->checked after if( $form->posted ) {}
		 */
		if( $type == 'checkbox' )
		{
			$this->value = $default;
			$this->SetCheckbox();
		}
		else
		{
			$input = FormField::GetInput( $name );

			if( $input !== null )
			{
				$this->value = $input;
			}
			else
			{
				$this->value = $default;
			}
		}
	}

	static function GetInput( $name )
	{
		if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
			$method = INPUT_POST;
		else
			$method = INPUT_GET;

		return filter_input( $method, $name );
	}

	protected function SetCheckbox()
	{
		if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
		{
			if( isset( $_POST[ $this->name ] ) && $_POST[ $this->name ] == $this->value )
			{
				$this->checked = 'checked';
			}
		}
		else
		{
			if( isset( $_GET[ $this->name ] ) && $_GET[ $this->name ] == $this->value )
			{
				$this->checked = 'checked';
			}
		}
	}

	function Validation( $validation, $p1 = null, $p2 = null, $p3 = null, $p4 = null )
	{
		$this->$validation( $p1, $p2, $p3, $p4 );
	}

	// validators

	protected function Required( $error_text = null )
	{
		if( strlen( $this->value ) < 1 )
		{
			$this->error[] = !is_null( $error_text ) ? $error_text : "This field is required." ;
		}
	}

	protected function Match( $error_text, $value )
	{
		if( $this->value != $value )
		{
			if( !$error_text )
				$error_text = "Does not match.";

			$this->error[] = $error_text;
		}
	}

	protected function Length( $error_text = null, $min_length = null, $max_length = null )
	{
		if( $min_length && !$max_length )
		{
			if( strlen( $this->value ) < $min_length )
			{
				if( !$error_text )
					$error_text = "Must be at least {$min_length} characters long.";

				$this->error[] = $error_text;
			}
		}
		elseif( $min_length && !$max_length )
		{
			if( strlen( $this->value ) > $max_length )
			{
				if( !$error_text )
					$error_text = "Must be up to {$max_length} characters long.";

				$this->error[] = $error_text;
			}
		}
		else
		{
			if( strlen( $this->value ) > $max_length &&  strlen( $this->value ) < $min_length )
			{
				if( !$error_text )
					$error_text = "Must be between {$min_length} and {$max_length} characters.";

				$this->error[] = $error_text;
			}
		}
	}

	protected function Email( $error_text = null )
	{
		if( !preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $this->value ) )
		{
			if( !$error_text )
				$error_text = "Must be valid email address.";

			$this->error[] = $error_text;
		}
	}

	protected function InDatabase( $error_text, $table, $column )
	{
		if( !$this->value )
			return false;

		$entity = Entity::getInstance();
		$query = "SELECT * FROM ". $entity->escapeTable( $table ) ." WHERE ". $entity->escapeColumn( $column ) ." = ?";

		if( $entity->getFirstResult( $query, trim( $this->value ), ucfirst( $table ) ) )
		{
			if( !$error_text )
			{
				$error_text = 'Taken.';
			}

			$this->error[] = $error_text;
		}
	}

	protected function NotInDatabase( $error_text, $table, $column )
	{
		if( !$this->value )
			return false;

		$entity = Entity::getInstance();
		$query = "SELECT * FROM ". $entity->escapeTable( $table ) ." WHERE ". $entity->escapeColumn( $column ) ." = ?";

		if( !$entity->GetFirstResult( $query, $this->value, $table ) )
		{
			if( !$error_text )
				$error_text = 'Not in database';

			$this->error[] = $error_text;
		}

	}

	protected function Checked( $error_text = null )
	{
		if( !isset( $this->checked ) || $this->checked != 'checked' )
		{
			$this->error[] = !is_null( $error_text ) ? $error_text : "This field is required." ;
		}
	}

	public function __toString()
	{
		return $this->value;
	}

	// end of validators
}
