<?php

namespace Twister\Schema;

abstract class Table
{
	protected	$name	=	null;
	protected	$hash	=	null;
	protected	$fields	=	null;

	public function __construct(array $fields = null)
	{
		$this->fields	=&	$fields;
	}

	public function __get($field)
	{
		static $tr = null;
		static $cache = null;

		if (isset($this->fields[$field]))
			return $this->fields[$field];

		$old_field = $field;
/*

		if ( ! isset($cache[$field]))
		{
			if ($tr === null)
			{
				foreach (range('A', 'Z') as $char)
					$tr[$char] = '_' . strtolower($char);
			}
			$cache[$old_field] = $field = strtr(lcfirst($field), $tr);
		}

		if (isset($this->fields[$field]))
			return $this->fields[$field];
*/
		throw new \Exception("Table `{$this->name}` doesn't contain a field called `$old_field`");
	}


	public function tableName()
	{
		return $this->name;
	}
	public function tableHash()
	{
		return $this->hash;
	}


	//	$statement = 'UPDATE' | 'INSERT' ... UPDATE = partial check, INSERT = FULL (required non-nullable fields) check!
	public function validate($statement)
	{
		return $this->fields[$field];
	}

	public function fields()
	{
		return $this->fields;
	}
	public function getFields()
	{
		return $this->fields;
	}
	public function fieldNames()
	{
		return array_keys($this->fields);
	}
	public function getFieldNames()
	{
		return array_keys($this->fields);
	}

	//	get an array, with field names as keys, but ALL values set to NULL
	//	This can be useful for array_merge() to test if ALL the fields in an INSERT statement will be met, including those fields we don't explicitly set! like timestamp = CURRENT_TIMESTAMP / on update CURRENT_TIMESTAMP
	public function emptyFields()
	{
		return array_fill_keys(array_keys($this->fields), null);
	}
	public function getEmptyFields()
	{
		return array_fill_keys(array_keys($this->fields), null);
	}


}
