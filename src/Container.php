<?php

namespace Twister;

class Container implements ContainerInterface
{
	protected $_container	=	[];

	function __set($name, $value)
	{
		if (isset($this->_container[$name]))
		{
			$trace = debug_backtrace();
			trigger_error(__CLASS__ . " container property `{$name}` has already been set and cannot be changed in {$trace[0]['file']} on line {$trace[0]['line']}. Please unset() and re-set the value!", E_USER_ERROR);
		}
		else
			$this->_container[$name] = $value;
	}

	function &__get($name)
	{
		if (isset($this->_container[$name]))
			return $this->_container[$name];

		//	Examples of official error messages
		//	Notice: Undefined index: muscle in C:\...\app.php on line 34
		//	Undefined property: app::$conf in <b>C:\...\app.php</b> on line <b>111</b><br />
		$trace = debug_backtrace();
		trigger_error('Undefined container property: ' . __CLASS__ . "->{$name} in <b>{$trace[0]['file']}</b> on line <b>{$trace[0]['line']}</b>; thrown", E_USER_ERROR);
		return null;
	}

	function __isset($name)
	{
		return isset($this->_container[$name]);
	}

	function __unset($name)
	{
		unset($this->_container[$name]);
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


		//	old method
		if (isset(self::$_[$method]) && is_callable(self::$_[$method]))
			return call_user_func_array(self::$_[$method], $args);
		if ( ! empty($args))
			self::$_[$method] = $args[0];
		return self::$_[$method];
/*			//	old method
		if ( ! empty($args))
			self::$_[$method] = $args[0];
		if (isset(self::$_[$method]))
			return self::$_[$method];
		else
			throw new InvalidArgumentException("_::{$method}() doesn't exist");
		/*
		Taken from: https://stackoverflow.com/questions/1279382/magic-get-getter-for-static-properties-in-php
		static public function __callStatic($method, $args)
		{
			if (preg_match('/^([gs]et)([A-Z])(.*)$/', $method, $match))
			{
				$reflector = new \ReflectionClass(__CLASS__);
				$property = strtolower($match[2]). $match[3];
				if ($reflector->hasProperty($property))
				{
					$property = $reflector->getProperty($property);
					switch($match[1])
					{
						case 'get': return self::${$property->name};
						case 'set': return self::${$property->name} = $args[0];
					}     
				}
				else throw new InvalidArgumentException("Property {$property} doesn't exist");
			}
		}
		*/
	}


	function set($key, $value)	//	alias for __set()
	{
		return $this->_container[$key] =& $value;
	}
	function &get($key)	//	alias for __get()
	{
		return $this->_container[$key];
	}
	function has($key)	//	alias for __isset()
	{
		return isset($this->_container[$key]);
	}
	function &merge($key, array $arr)	//	we need this function because we cannot (re)`set` the arrays to new values without unsetting the old values first! ie. __set() will fail because it already exists!
	{
		//	TODO: Add is_array() checks to the container, and add variable number of array inputs!
		$this->_container[$key] = array_merge($this->_container[$key], $arr);
		return $this->_container[$key];
	}
	function remove($key)	//	alias for __unset()
	{
		unset($this->_container[$key]);
	}
}
