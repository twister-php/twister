<?php

namespace Twister\DBAL;

class Type
{
	protected $properties	=	null;

	public function __construct(array $properties)
	{
		$properties['type']		=	&$properties[0];
		$properties['default']	=	&$properties[1];
		$properties['nullable']	=	&$properties[2];

		$this->properties		=&	$properties;
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
			throw new \Exception("Cannot set protected property {$name}");
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
		return $this->properties[$method]($this, ...$args);		//	TEST	!!!
/*
		array_unshift($args, $this);
		return call_user_func_array($this->properties[$method], $args);
*/
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
