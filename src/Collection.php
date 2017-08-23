<?php

/**
 *	Collection class with dynamic members
 *	Similar functionality to an Array or Dictionary class
 *	Typically holding a collection/array of Entity members
 *	This class is not particularly useful compared to a standard array,
 *		but it's used to `extend` the functionality of standard arrays.
 *
 *	@link	https://laravel.com/docs/5.4/eloquent-collections#available-methods
 *
 *	@author	Trevor Herselman <therselman@gmail.com>
 */

namespace Twister;

class Collection implements \Iterator, \Countable, \ArrayAccess
{
	protected $members	=	null;

	public function __construct(array $members = [])
	{
		$this->members	=&	$members;
	}


	/**
	 *	Countable interface
	 */
	public function find(...$params)
	{
		if (count($params) === 1)
			return count($this->members);
		
	}


	/**
	 *	return all the array keys
	 */
	public function keys()
	{
		return array_keys($this->members);
	}


	/**
	 *	
	 */
	public function isEmpty()
	{
		return count($this->members) === 0;
	}


	/**
	 *	Get member by id/index
	 *
	 *	Note: This is not very useful, because most members will be indexed by integer.
	 *
	 *	@param  string|int  $idx
	 *	@return mixed
	 */
	public function __get($idx)
	{
		return $this->members[$idx];
	}


	/**
	 *	Set member by id/index
	 *
	 *	@param  string|int  $idx
	 *	@param  mixed       $value
	 *	@return void
	 */
	public function __set($idx, $value)
	{
		$this->members[$idx] = $value;
	}


	function __isset($idx)
	{
		return isset($this->members[$idx]);
	}
	function __unset($idx)
	{
		unset($this->members[$idx]);
	}


	/**
	 *	Workaround for the `array access functions` eg. array_push($obj->toArray(), $value);
	 */
	public function &toArray()
	{
		return $this->members;
	}


	/**
	 *	
	 */
	public function toJson()
	{
		return json_encode($this->members);
	}


    public function __toString()
    {
        return json_encode($this->members);
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
	/**
	 *	@alias count()
	 */
	public function length()
	{
		return count($this->members);
	}


	/**
	 *	ArrayAccess interface
	 */
	public function offsetGet($idx)			//	eg. var_dump($obj['two']);
	{
		return $this->members[$idx];
	}
	public function offsetSet($idx, $value)	//	eg. $obj['two'] = 'A value';
	{
		$this->members[$idx] = $value;
	}
	public function offsetExists($idx)		//	eg. isset($obj['two'])
	{
		return isset($this->members[$idx]);
	}
	public function offsetUnset($idx)		//	eg. unset($obj['two']);
	{
		unset($this->members[$idx]);
	}
}
