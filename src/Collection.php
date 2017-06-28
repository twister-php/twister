<?php

namespace Twister;

class Collection implements \Iterator, \Countable, \ArrayAccess
{
	protected $_	=	null;

	public function __construct(array $members = [])
	{
		$this->_	=&	$members;
	}

	//
	//	Workaround for the `array access functions` eg. array_push($obj->toArray(), 'Hello World!');
	//
	public function &toArray()
	{
		return $this->_;
	}
	public function &__invoke()
	{
		return $this->_;
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
