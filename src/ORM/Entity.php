<?php

namespace Twister\ORM;

abstract class Entity
{
	protected	$properties;
	public		$id;

	function __construct(array $properties = null)
	{
		$this->properties	=&	$properties;
		$this->id			=	$properties['id'] ?? null;
	}

	/**
	 * Get entity field/property
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	function __get($name)
	{
		if ( ! isset($this->properties[$name]))
			$this->load($name);

		return $this->properties[$name];
	}

	/**
	 * Set entity field/property
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	function __set($name, $value)
	{
		$this->properties[$name] = $value;
	}

	function __isset($name)
	{
		return isset($this->properties[$name]);
	}
	function __unset($name)
	{
		unset($this->properties[$name]);
	}

	abstract function load(...$args);
}
