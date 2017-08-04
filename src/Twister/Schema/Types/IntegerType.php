<?php

namespace Twister\Schema\Types;

class IntegerType extends BaseType
{
	protected $properties	=	null;

	public	$required		=	false;	//	`required` is a publically changeable property (ie. we can override the default, unlike the other properties! moved here because of __set() restrictions)

	private static $isValid =	null;	//	cache the default `isValid` function
/*
	private $clamp			=	function ($type, $value) {};	//	callback function for `clamp`
	private $__invoke		=	function ($type, $value) {};	//	callback function for `__invoke`
	private $toPHP			=	function ($type, $value) {};	//	callback function for toPHP
	private $toSQL			=	function ($type, $value) {};	//	callback function for toSQL
	private $valid			=	function ($type, $value) {};	//	callback function for isValid
*/
	public function __construct(&$table, $name, $type, $default, $nullable, $min, $max, $auto = false)
	{
		$this->properties['table']			=	$table;
		$this->properties['name']			=	$name;
		$this->properties['type']			=	$type;
		$this->properties['default']		=	$default;
		$this->properties['nullable']		=	$nullable;
		$this->properties['min']			=	$min;
		$this->properties['max']			=	$max;
		$this->properties['autoincrement']	=	$auto;
		$this->properties['unsigned']		=	$min === 0;

		$this->required						=	$default === null && ! $nullable && ! $auto;

		if (self::$isValid === null) {
			self::$isValid	=	function ($type, $value)
								{
									return $value !== null && is_string($value) && ($type->charset === 'latin1' ? strlen($value) : mb_strlen($value, 'utf8')) <= $this->maxlength || $value === null && ($type->nullable || $this->default);
								};	//	in_array(null, ['']) === true	... therefore we MUST test `$value !== null` before the in_array() or we might get false positives
		}
		$this->properties['isValid']	=	self::$isValid;
	}

	public function unsigned()
	{
		return $this->min === 0;
	}
	public function isUnsigned()
	{
		return $this->min === 0;
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
		return isset($value) && is_numeric($value) ? min(max($value, $this->min), $this->max) : $this->default ?: ($this->nullable ? null : min(max(0, $this->min), $this->max));
	}

	public function getMin()
	{
		return $this->min;
	}

	public function getMax()
	{
		return $this->max;
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
