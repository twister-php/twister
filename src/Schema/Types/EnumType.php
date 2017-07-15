<?php

namespace Twister\Schema\Types;

class EnumType implements \Iterator, \Countable, \ArrayAccess
{
	private $properties	=	null;
	private $members	=	null;

	private static $isValid = null;	// cache the default isValid function

	public function __construct(&$table, $name, $default, $nullable, array $members)
	{
		$this->properties['table']		=	$table;
		$this->properties['name']		=	$name;
		$this->properties['type']		=	'enum';
		$this->properties['default']	=	$default;
		$this->properties['nullable']	=	$nullable;

		if (self::$isValid === null) {
			self::$isValid = function ($table, $type, $value) { $type };
		}
		$this->properties['isValid']	=	self::$isValid

		$this->members					=	$members;
	}

	/**
	 * Get table field/column property
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->properties[$name];
	}

	/**
	 * Set table field/column property
	 * Values cannot be set, only callables
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ( ! isset($this->properties[$name]) || is_callable($this->properties[$name]))
			$this->properties[$name] = $value;
		else
			throw new \Exception("Cannot set private property {$name}");
	}


	public function __call($method, $args)
	{
		return $this->properties[$method]($this, ...$args);		//	TEST	!!!
/*
		array_unshift($args, $this);
		return call_user_func_array($this->properties[$method], $args);
*/
	}
	public function __invoke($value)
	{
		return $this->properties['isValid']($this, $value);
	}
	public function setMethod($method, callable $callable)
	{
		$this->properties[$method] = $callable;
		return $this;
	}


	/**
	 *	Iterator interface
	 */
	public function rewind()
	{
		return reset($this->members);
	}
	public function current()
	{
		return current($this->members);
	}
	public function key()
	{
		return key($this->members);
	}
	public function next()
	{
		return next($this->members);
	}
	public function valid()
	{
		return key($this->members) !== null;
	}


	/**
	 *	Countable interface
	 */
	public function count()
	{
		return count($this->members);
	}


	//	WARNING: I think I should switch the definition of `get`,
	//	if we query $obj['my_enum_value'] I think it should return the Enum index number ?
	/**
	 *	ArrayAccess interface
	 */
	public function offsetGet($idx)			//	eg. var_dump($obj['two']);
	{
		return $this->members[$idx];
	}
	public function offsetSet($idx, $value)	//	eg. $obj['two'] = 'A value';
	{
		throw new \Exception('Cannot set an Array Type value! This object is Immutable!');
		$this->members[$idx] = $value;
	}
	public function offsetExists($idx)		//	eg. isset($obj['two'])
	{
		return isset($this->members[$idx]);
	}
	public function offsetUnset($idx)		//	eg. unset($obj['two']);
	{
		throw new \Exception('Cannot unset an Array Type value! This object is Immutable!');
		unset($this->members[$idx]);
	}


	/**
	 *	Test if a value is part of the Enum or Set
	 *	This function is case-sensitive!
	 *	TODO: Sets can include multiple values eg. VALUE1 | VALUE2 etc.
	 *		We need to validate the whole set!
	 */
	function isValid($value)
	{
		static $combined = null;
		if ($combined === null) {
			$combined = array_combine($this->members, $this->members);
		}
		return isset($combined[$value]);
	}

	function getArray()
	{
		return $this->members;
	}
	function toArray()
	{
		return $this->members;
	}

	function getIndex($needle)
	{
		if (is_scalar($needle))
			return array_search($needle, $this->members); // Note: `If needle is a string, the comparison is done in a case-sensitive manner.`
		else if (is_array($needle))
			return array_keys($this->members, $needle, $this->members); // Note: `To return the keys for all matching values, use array_keys() with the optional search_value parameter instead.`
		var_dump($needle);
		trigger_error('Invalid data type passed to getIndex()', E_USER_ERROR);
	}

	function toSQL($value)
	{
		return $value;
	}
}
