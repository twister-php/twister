<?php

/**
 *	Immutable!
 */

namespace Twister\DBAL\Types;

class ArrayType extends BaseType
{
	public $min				=	null;
	public $max				=	null;

//	public $auto_increment	=	null;

	public function __construct($type, $default, $nullable, $min, $max)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;

		$this->min			=	$min;
		$this->max			=	$max;
	}

	public function isUnsigned()
	{
		return $this->min >= 0;
	}

	public function filter($value)
	{
		return isset($value) && is_numeric($value) ? min(max($value, $this->min), $this->max) : ($this->nullable ? $this->default : (is_null($this->default) ? 0 : $this->default));
	}

	//	MAIN function of integers!
	//	This function will ALWAYS return a VALID value for the field!
	//	Either a NULL (if valid for the field), or ZERO or clamped to range!
	public function clamp($value)
	{
		return isset($value) && is_numeric($value) ? min(max($value, $this->min), $this->max) : ($this->default ?: ($this->nullable ? null : 0));
	}

	public function getMin()
	{
		return $this->min;
	}

	public function getMax()
	{
		return $this->max;
	}

	public function isUnsigned()
	{
		return $this->min >= 0;
	}

	public function getRange()
	{
		return [$this->min, $this->max];
	}
	/*
	function isAutoIncrement()
	{
		return $this->flags & MYSQLI_AUTO_INCREMENT_FLAG;
	}
	*/

	public function __invoke($value)
	{
		return $this->is($value);
	}
	public function is()
	{
		return is_null($value) && $this->nullable || is_numeric($value) && $value >= $this->min && $value <= $this->max;
	}
	function isValid($value)
	{
		return $this->is($value);
	}
}
