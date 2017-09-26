<?php

namespace Twister\ORM;

/**
 *	Similar Examples
 *	----------------
 *	@link	https://github.com/analogueorm/analogue/blob/5.5/src/Entity.php
 *
 *	Interfaces
 *	----------
 *	@link	http://php.net/JsonSerializable
 */
abstract class Entity implements \JsonSerializable
{
	protected	$properties;

	protected	$map;	//	AKA config; can include relations, factory methods for relations etc.

	function __construct(array $properties = null)
	{
		$this->properties	=&	$properties;
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
		{
			if (isset($this->map[$name]))
			{
				
			}
		}
		return $this->properties[$name];
	}

//	abstract private relation($name);
//	abstract private __get__();

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

	function jsonSerialize()
	{
		return $this->properties;
	}
}
