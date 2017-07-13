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
 *	Laravel Container: https://laravel.com/docs/5.4/container
 *	Symfony Container: http://symfony.com/doc/current/service_container.html
 */
class Container implements \ArrayAccess
{
	/**
	 * The current globally available container (if any).
	 *
	 * @var static
	 */
	protected static $instance = null;

	/**
	 * The container's bindings.
	 *
	 * @var array
	 */
	protected $bindings = null;

	/**
	 * The registered type aliases.
	 *
	 * @var array
	 */
	protected $aliases = null;

	function __construct(array $c = [])
	{
		if (static::$instance)
			throw new \Exception('Cannot create another Container instance!');
		static::$instance = $this;

		$this->bindings = $c;
		if (isset($c['aliases']))
		{
			$this->aliases = $c['aliases'];
			unset($c['aliases']);
		}
		$this->aliases[__CLASS__] = $this;
	}

	/**
	 * Set the globally available instance of the container.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		return static::$instance;
	}

	function &__call($method, $args)
	{
		if (isset($this->bindings[$method]))
			if (is_callable($this->bindings[$method]))
			{
				array_unshift($args, $this);	//	Prepend $this container to the beginning of the function call arguments!
				$result = call_user_func_array($this->bindings[$method], $args);
				return $result;
			}
			else
				return $this->bindings[$method];
		else
		{
			if (preg_match('/^([gs]et|has|isset|unset)([A-Z])(.*)$/', $method, $match))
			{
				$property = strtolower($match[2]). $match[3];
				if (isset($this->bindings[$property]))
				{
					switch($match[1])
					{
						case 'get': return $this->bindings[$property];
						case 'set': return $this->bindings[$property] = $args[0];
						case 'has': //	fallthrough vvv alias for `isset`
						case 'isset': $result = isset($this->bindings[$property]); return $result;
						case 'unset': $result = null; unset($this->bindings[$property]); return $result;
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
		return $this->bindings[$key] ?? $default;
	}
	function has($key)						//	alias for __isset()
	{
		return isset($this->bindings[$key]);
	}
	function &merge($key, array $arr)		//	we need this function because we cannot (re)`set` the arrays to new values without unsetting the old values first! ie. __set() will fail because it already exists!
	{
		//	TODO: Add is_array() checks to the container, and add variable number of array inputs!
		$this->bindings[$key] = array_merge($this->bindings[$key], $arr);
		return $this->bindings[$key];
	}
	function remove($key)					//	alias for __unset()
	{
		unset($this->bindings[$key]);
	}



	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->__isset($key);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->__get($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->__set($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->bindings[$key]);
	}

	/**
	 * Dynamically access container services.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (isset($this->bindings[$key]))
		{
			$value = $this->bindings[$key];
			return is_callable($value) ? $value($this) : $value;	//	$value instanceof Closure
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

	/**
	 * Dynamically set container services.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
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
		if (isset($this->bindings[$key]) && ! is_callable($this->bindings[$key]))
		{
			$trace = debug_backtrace();
			trigger_error(__CLASS__ . " container property `{$key}` has already been set and cannot be changed in {$trace[0]['file']} on line {$trace[0]['line']}. Please unset() and re-set the value!", E_USER_ERROR);
		}
		else
			return $this->bindings[$key] = $value;
	}

	function __isset($key)
	{
		return isset($this->bindings[$key]);
	}

	function __unset($key)
	{
		unset($this->bindings[$key]);
	}

}
