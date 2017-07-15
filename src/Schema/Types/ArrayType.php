<?php

namespace Twister\Schema\Types;

class ArrayType extends Type implements \Iterator, \Countable, \ArrayAccess
{
	protected $members	=	null;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
		$this->members		=&	$properties[3];
	}

	//	Get the Enum array as keys, values contains the index numbers
	public function asKeys()	//	AKA flip()
	{
		return array_flip($this->members);
	}

	//	Get the Enum array with both keys AND values
	public function asBoth()
	{
		return array_combine($this->members, $this->members);
	}

	//	pass null to the component for members to use
	public function combine(array $keys = null, array $values = null)
	{
		return array_combine($keys ?: $this->members, $values ?: $this->members);
	}

	//	Creates a new array with Enum as keys, and $arr as values
	public function combineWithValues(array $values)
	{
		return array_combine($this->members, $values);
	}

	//	Creates a new array with Enum as values, and $arr as keys
	public function combineWithKeys(array $keys)
	{
		return array_combine($keys, $this->members);
	}

	//	Passed array must contain the same keys as our member array, used to verify that our array contains valid Enum entries
	public function verifyKeys(array $arr)
	{
		//	TODO
	}

	//	Passed array must contain the same Values as our member array, used to verify that our array contains valid Enum entries
	public function verifyValues(array $arr)
	{
		//	TODO
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
		return in_array($value, $this->members);
	}
	/**
	 *	Test if a value is part of the Enum or Set
	 *	This function is case-sensitive!
	 *	TODO: Sets can include multiple values eg. VALUE1 | VALUE2 etc.
	 *		We need to validate the whole set!
	 */
	function isMember($value)
	{
		return in_array($value, $this->members);
	}
}
