<?php

/**
 * Entity class with dynamic properties
 * Properties can include callable functions, which can `lazy load` properties, or return dynamic/complex/calculated properties
 */

namespace Twister;

class Entity
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
}
