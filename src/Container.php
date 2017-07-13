<?php

namespace Twister;

use ArrayAccess;

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
class Container implements ArrayAccess
{
	/**
	 * The current globally available container instance (if any).
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

		$this->bindings[self::class] = $this;
	}

	/**
	 * Get the globally available instance of the container.
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

	/**
	 * Call the given function using the given parameters.
	 *
	 * Missing parameters will be resolved from the container.
	 *
	 * @param callable $callable   Function to call.
	 * @param array    $params     Additional parameters to use. Can be indexed by the parameter names
	 *                             or not indexed (same order as the parameters).
	 *                             The array can also contain DI definitions, e.g. DI\get().
	 *
	 * @return mixed Result of the function.
	 */
	public function call(callable $callable, array $params = null)
	{
		$reflected = new \ReflectionFunction($callable);
		return $reflected->invokeArgs($this->_buildArgList($reflected, $params));
	}

	private function _buildArgList(\ReflectionFunctionAbstract $reflected, array &$params = null)
	{
		$args	=	[];
		foreach ($reflected->getParameters() as $index => $param)
		{
			if ($param->hasType() && isset($this->bindings[$param->getType()]))
			{
				$value = &$this->bindings[$param->getType()];
				$args[] = is_callable($value) ? $value($this) : $value;
			}
			else
			{
				$name = $param->name;
				if (isset($params[$name]))
				{
					$args[] = $params[$name];
				}
				else if (isset($params[$index]))
				{
					$args[] = $params[$index];
				}
				else
				{
					if ( ! $param->isOptional())
						throw new \Exception('Unable to find NON-optional parameter `' . $param->name . ($param->hasType() ? '` of type `' . $param->getType() : null) . '` for route controller/handler: ' . var_export($this->route, true));
					$args[] = $param->getDefaultValue();
				}
			}
		}
		return $args;
	}

	/**
	 * Build an entry of the container by its name.
	 *
	 * This method behaves like get() except resolves the entry again every time.
	 * For example if the entry is a class then a new instance will be created each time.
	 *
	 * This method makes the container behave like a factory.
	 *
	 * @param string $name       Entry name or a class name.
	 * @param array  $parameters Optional parameters to use to build the entry.
	 *                           Use this to force specific parameters to specific values.
	 *                           Parameters not defined in this array will be resolved using the container.
	 *
	 * @throws InvalidArgumentException The name parameter must be of type string.
	 * @throws DependencyException Error while resolving the entry.
	 * @throws NotFoundException No entry found for the given name.
	 * @return mixed
	 */
	public function make(string $name, array $parameters = null)
	{
		$definition = $this->definitionSource->getDefinition($name);
		if (! $definition) {
			// If the entry is already resolved we return it
			if (array_key_exists($name, $this->resolvedEntries)) {
				return $this->resolvedEntries[$name];
			}

			throw new NotFoundException("No entry or class found for '$name'");
		}

		return $this->resolveDefinition($definition, $parameters);
	}

	/**
	 * Define an object or a value in the container.
	 *
	 * @param string $name Entry name
	 * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
	 */
	public function set(string $name, $value)
	{
		if ($value instanceof DefinitionHelper) {
			$value = $value->getDefinition($name);
		} elseif ($value instanceof \Closure) {
			$value = new FactoryDefinition($name, $value);
		}

		if ($value instanceof Definition) {
			$this->setDefinition($name, $value);
		} else {
			$this->resolvedEntries[$name] = $value;
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
		return isset($this->bindings[$key]);
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
