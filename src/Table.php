<?php

namespace Twister\DBAL;

use Twister\DBAL\Types;

class Table
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

	public static function build($db, string $table)
	{

	}
}
