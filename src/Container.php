<?php

namespace Twister;

/**
 *	Multi-purpose IoC/DI Container
 *	==============================
 *	This is a powerful, flexible, yet light-weight, simple and easy-to-use multi-purpose Container;
 *		it includes the ability to be a IoC/DI Container, a Service Locator,
 *		a dynamic function library, factory object builder and general purpose data/object/array storage.
 *	This Container essentially takes the place of an application `Kernel` or `App` class,
 *		all global variables/object instances, app configurations, environment variables,
 *		`microservices`, object (factory) builders etc. are all contained within it.
 *	Many of the services, capabilities and functionality are provided by the various anonymous functions contained within.
 *		Along with the closures, it also contains arrays and instanciated objects.
 *	All these capabilities are accessed in the form of a dynamic property (using __get and __set);
 *		eg. '$c->db' gives you the current database class; you can also use '$c->db()' if you prefer.
 */
class Container
{
	protected $_container	=	null;

	function __construct(array $c = [])
	{
		$this->_container = $c;
	}

	function __set($key, $value)
	{
		/**
		 *	do we really need to stop the variables from being set???
		 *	currently we are protecting anything that is not a callable function.
		 *		the reason why I don't protect a callable, is because many of the callables
		 *		will set the same value to an instantaited object. It just saves us using `unset()` first
		 *	The alternative is to do something like Symfony or other frameworks,
		 *		where we call a `protect()` or `singleton()` methods etc.
		 *	I just hate calling yet another method for every occasion or fringe case!
		 */
		if (isset($this->_container[$key]) && ! is_callable($this->_container[$key]))
		{
			$trace = debug_backtrace();
			trigger_error(__CLASS__ . " container property `{$key}` has already been set and cannot be changed in {$trace[0]['file']} on line {$trace[0]['line']}. Please unset() and re-set the value!", E_USER_ERROR);
		}
		else
			return $this->_container[$key] = $value;
	}

	function __get($key)
	{
		if (isset($this->_container[$key]))
		{
			$value = $this->_container[$key];
			return is_callable($value) ? $value($this) : $value;
		}

		/**
		 *	Examples of official PHP error messages when a property cannot be found
		 *		Notice: Undefined index: config in C:\...\app.php on line 34
		 *		Undefined property: Container::$config in <b>C:\...\app.php</b> on line <b>111</b><br />
		 */
		$trace = debug_backtrace();
		trigger_error('Undefined container property: ' . __CLASS__ . "->{$key} in <b>{$trace[0]['file']}</b> on line <b>{$trace[0]['line']}</b>; thrown", E_USER_ERROR);
		return null;
	}

	function __isset($key)
	{
		return isset($this->_container[$key]);
	}

	function __unset($key)
	{
		unset($this->_container[$key]);
	}

	function &__call($method, $args)
	{
		if (isset($this->_container[$method]))
			if (is_callable($this->_container[$method]))
			{
				array_unshift($args, $this);	//	Prepend $this container to the beginning of the function call arguments!
				$result = call_user_func_array($this->_container[$method], $args);
				return $result;
			}
			else
				return $this->_container[$method];
		else
		{
			if (preg_match('/^([gs]et|has|isset|unset)([A-Z])(.*)$/', $method, $match))
			{
				$property = strtolower($match[2]). $match[3];
				if (isset($this->_container[$property]))
				{
					switch($match[1])
					{
						case 'get': return $this->_container[$property];
						case 'set': return $this->_container[$property] = $args[0];
						case 'has': //	fallthrough vvv alias for `isset`
						case 'isset': $result = isset($this->_container[$property]); return $result;
						case 'unset': $result = null; unset($this->_container[$property]); return $result;
					}
				}
				throw new \InvalidArgumentException("Property {$property} doesn't exist");
			}
			throw new \InvalidArgumentException(__CLASS__ . "->{$method}() doesn't exist");
		}
	}


	function set($key, $value)				//	alias for __set()
	{
		return $this->__set($key, $value);
	}
	function &get($key, $default = null)	//	similar to __get()
	{
		return $this->_container[$key] ?? $default;
	}
	function has($key)						//	alias for __isset()
	{
		return isset($this->_container[$key]);
	}
	function &merge($key, array $arr)		//	we need this function because we cannot (re)`set` the arrays to new values without unsetting the old values first! ie. __set() will fail because it already exists!
	{
		//	TODO: Add is_array() checks to the container, and add variable number of array inputs!
		$this->_container[$key] = array_merge($this->_container[$key], $arr);
		return $this->_container[$key];
	}
	function remove($key)					//	alias for __unset()
	{
		unset($this->_container[$key]);
	}
}
