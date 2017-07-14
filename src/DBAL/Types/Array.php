<?php

/**
 *	Immutable!
 */

namespace Twister\DBAL\Types;

class Array extends Type implements \Iterator, \Countable, \ArrayAccess
{
	protected $members	=	null;

	function __construct($type, $default, $nullable, array $members)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;

		$this->members		=	&$members;	//	array_combine($a, $b)

		//	eg. enum('','collection','macrolanguage','ancient','family')
	//	$this->array = explode('\',\'', substr($fd['Type'], strlen($intrinsic) + 2, -2)); // plus 2 because we need to skip `('` and minus 2 because we have to skip the last 2 chars: `')`

		//	RegExp versions:
		//	preg_match_all('/\'(.*?)\'/', $matches);
		//	preg_match_all('/(?<=[(,])([^,)]+)(?=[,)])/', $string, $matches);
		//	$enumArray = explode(",", str_replace("'", "",preg_replace("/enum\((.+)\)/", "\\1", $enumString))); 
		//	$this->array = $matches[1];
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
	function isValid(string $value, bool $casesensitive = true)
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

	function toSQL(&$value)
	{
		return value;
	}
}
