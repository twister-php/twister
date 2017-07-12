<?php

namespace Twister;

class Collection implements \Iterator, \Countable, \ArrayAccess
{
	protected $_	=	null;

	public function __construct(array $members = null)
	{
		$this->_	=&	$members;
	}

	function &__get($key)
	{
		return $this->_[$key] ?? null;
	}
	function __set($key, $value)
	{
		return $this->_[$key] = $value;
	}


	function __isset($key)
	{
		return isset($this->_[$key]);
	}
	function __unset($key)
	{
		unset($this->_[$key]);
	}


	function &all()
	{
		return $this->_;
	}
	function set($key, $value)	//	alias for __set()
	{
		return $this->_[$key] =& $value;
	}
	function &get($key, $default = null)	//	alias for __get()
	{
		return $this->_[$key] ?? $default;
	}
	function has($key)	//	alias for __isset()
	{
		return isset($this->_[$key]);
	}
	function &merge($key, array $arr)	//	we need this function because we cannot (re)`set` the arrays to new values without unsetting the old values first! ie. __set() will fail because it already exists!
	{
		//	TODO: Add is_array() checks to the container, and add variable number of array inputs!
		$this->_[$key] = array_merge($this->_[$key], $arr);
		return $this->_[$key];
	}
	function remove($key)	//	alias for __unset()
	{
		unset($this->_[$key]);
	}




	function &__call($method, $args)
	{
		if (isset($this->_[$method]))
			if (is_callable($this->_[$method]))
			{
				$result = call_user_func_array($this->_[$method], $args);
				return $result;
			}
			else
				return $this->_[$method];
		else
		{
			if (preg_match('/^([gs]et|has|isset|unset)([A-Z_])(.*)$/', $method, $match))
			{
				$property = strtolower($match[2]). $match[3];
				switch($match[1])
				{
					case 'get': return $this->_[$property] ?? $args[0] ?? null;
					case 'set': return $this->_[$property] = $args[0];
					case 'has': //	fallthrough vvv alias for `isset`
					case 'isset': $result = isset($this->_[$property]); return $result;
					case 'unset': $result = null; unset($this->_[$property]); return $result;
				}
				//throw new \InvalidArgumentException("Property {$property} doesn't exist");
			}
			throw new \InvalidArgumentException(__CLASS__ . "->{$method}() doesn't exist");
		}
	}

	//
	//	Workaround for the `array access functions` eg. array_push($obj->toArray(), 'Hello World!');
	//
	public function &toArray()
	{
		return $this->_;
	}
	public function &__invoke()
	{	//	TODO: What do you think about this technique? We could just leave it if we don't use it!
		//	Basically, we are calling an internal `__invoke` handler
		//	eg. $myCollection['__invoke'] = function($c) { return $c->all(); }
		return $this->_['__invoke']($this);
	}

	//
	//	Iterator interface
	//
	public function rewind()
	{
		return reset($this->_);
	}
	public function current()
	{
		return current($this->_);
	}
	public function key()
	{
		return key($this->_);
	}
	public function next()
	{
		return next($this->_);
	}
	public function valid()
	{
		return key($this->_) !== null;
	}


	//
	//	Countable interface
	//
	public function count()
	{
		return count($this->_);
	}


	//
	//	ArrayAccess interface
	//
	public function offsetSet($id, $value)	//	eg. $obj['two'] = 'A value';
	{
		$this->_[$id] = $value;
	}
	public function offsetExists($id)		//	eg. isset($obj['two'])
	{
		return isset($this->_[$id]);
	}
	public function offsetUnset($id)		//	eg. unset($obj['two']);
	{
		unset($this->_[$id]);
	}
	public function offsetGet($id)			//	eg. var_dump($obj['two']);
	{
		return $this->_[$id];
	}
}
