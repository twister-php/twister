<?php

namespace Twister\Schema;

abstract class Table
{
	protected $fields	=	null;

	public function __construct(array $fields = null)
	{
		$this->fields	=&	$fields;
	}

	public function __get($field)
	{
		return $this->fields[$field];
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


}
