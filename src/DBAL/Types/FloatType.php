<?php

/**
 *	Immutable!
 */

namespace Twister\DBAL\Types;

class FloatType extends BaseType
{
	public $decimals		= null;
//	public $unsigned		= null; // do we really need this ??? Could be implemented inside the entity classes! I think MySQL STILL accepts negatives anyway!
									// we will likely only make a generic `float` wrapper, which wouldn't know that this value is unsigned !?!? But the entities WILL!

	function __construct($type, $default, $nullable, $decimals)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;

		$this->decimals		=	$decimals;
	}

	function isValid($value)
	{
		return parent::isValid($value) && is_numeric($value);
	}
}
