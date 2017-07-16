<?php

namespace Twister\Schema\Types;

class StringType extends BaseType
{
	protected $properties	=	null;

	public	$required		=	false;	//	`required` is a publically changeable property (ie. we can override the default, unlike the other properties! moved here because of __set() restrictions)

	private static $isValid	=	null;	//	cache the default `isValid` function

	public function __construct(&$table, $name, $type, $default, $nullable, $length, $charset, $fixed = false)
	{
		$this->properties['table']		=	$table;
		$this->properties['name']		=	$name;
		$this->properties['type']		=	$type;
		$this->properties['default']	=	$default;
		$this->properties['nullable']	=	$nullable;
		$this->properties['length']		=	$length;
		$this->properties['charset']	=	$charset;
		$this->properties['fixed']		=	$fixed;		//	fixed length - from Doctrine: `fixed (boolean): Whether a string or binary Doctrine type column has a fixed length. Defaults to false.`

//	public $binary			= null; // needed? could be set by collation type: `utf8_bin` or data type `binary` ???

		$this->required					=	$default === null && ! $nullable;

		if (self::$isValid === null) {
			self::$isValid	=	function ($type, $value)
								{
									return $value !== null && is_string($value) && ($type->charset === 'latin1' ? strlen($value) : mb_strlen($value, 'utf8')) <= $this->maxlength || $value === null && ($type->nullable || $this->default);
								};	//	in_array(null, ['']) === true	... therefore we MUST test `$value !== null` before the in_array() or we might get false positives
		}
		$this->properties['isValid']	=	self::$isValid;
	}

	function isValid($value)
	{
		return parent::isValid($value) && is_scalar($value) && strlen($value) <= $length;
	}

	function toSQL($value)
	{
		return $value;
	}
}
