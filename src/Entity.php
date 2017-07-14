<?php

/**
 * Entity class with dynamic properties
 * Properties can include callable functions, which can `lazy load` properties, or return dynamic/complex/calculated properties
 */

namespace Twister;

class Entity implements \ArrayAccess
{
	protected $properties	=	null;

	public function __construct(array $properties = null)
	{
		$this->properties	=&	$properties;
	}

	/**
	 * Get entity field/property
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$value = $this->properties[$name];
		return is_callable($value) ? $value($this) : $value;
	}

	/**
	 * Set entity field/property
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->properties[$name] = $value;
	}


	public function __isset($name)
	{
		return isset($this->properties[$name]);
	}
	public function __unset($name)
	{
		unset($this->properties[$name]);
	}


	public function &all()
	{
		return $this->properties;
	}
	public function get($name)						//	alias for __get()
	{
		return $this->properties[$name];
	}
	public function set($name, $value)				//	alias for __set()
	{
		return $this->properties[$name] = $value;
	}
	public function has($name)						//	alias for __isset()
	{
		return isset($this->properties[$name]);
	}
	public function remove($name)					//	alias for __unset()
	{
		unset($this->properties[$name]);
	}


	public function __call($method, $args)
	{
		array_unshift($args, $this);
		return call_user_func_array($this->properties[$method], $args);
	}
	public function __invoke()
	{
		return $this->properties['__invoke']($this);
	}
	public function setMethod($method, callable $callable)
	{
		$this->properties[$method] = $callable;
		return $this;
	}


	/**
	 *	ArrayAccess interface
	 */
	public function offsetGet($name)			//	eg. var_dump($obj['two']);
	{
		return $this->properties[$name];
	}
	public function offsetSet($name, $value)	//	eg. $obj['two'] = 'A value';
	{
		$this->properties[$name] = $value;
	}
	public function offsetExists($name)			//	eg. isset($obj['two'])
	{
		return isset($this->properties[$name]);
	}
	public function offsetUnset($name)			//	eg. unset($obj['two']);
	{
		unset($this->properties[$name]);
	}
}
