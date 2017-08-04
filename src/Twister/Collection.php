<?php

/**
 * Collection class with dynamic members and methods
 * Similar functionality to an Array or Dictionary class
 * Typically holding a collection/array of Entity members
 */

namespace Twister;

class Collection implements \Iterator, \Countable, \ArrayAccess
{
	protected $members	=	null;
	protected $methods	=	null;

	public function __construct(array $members = null, array $methods = null)
	{
		$this->members	=&	$members;
		$this->methods	=&	$methods;
	}


	/**
	 * Get member by id/index
	 *
	 * @param  string|int  $idx
	 * @return mixed
	 */
	public function __get($idx)
	{
		return $this->members[$idx];
	}

	/**
	 * Set member by id/index
	 *
	 * @param  string|int  $idx
	 * @param  mixed       $value
	 * @return void
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


	public function &all()
	{
		return $this->members;
	}
	public function get($name)						//	alias for __get()
	{
		return $this->members[$name];
	}
	public function set($name, $value)				//	alias for __set()
	{
		return $this->members[$name] = $value;
	}
	public function has($name)						//	alias for __isset()
	{
		return isset($this->members[$name]);
	}
	public function remove($name)					//	alias for __unset()
	{
		unset($this->members[$name]);
	}


	/**
	 *	Workaround for the `array access functions` eg. array_push($obj->toArray(), $value);
	 */
	public function &toArray()
	{
		return $this->members;
	}


	public function __call($method, $args)
	{
		array_unshift($args, $this);
		return call_user_func_array($this->methods[$method], $args);
	}
	public function __invoke()
	{
		return $this->methods['__invoke']($this);
	}
	public function setMethod($method, callable $callable)
	{
		$this->methods[$method] = $callable;
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
