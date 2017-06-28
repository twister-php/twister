<?php

namespace Twister;

abstract class Controller implements ContainerInterface
{
	use ContainerAwareTrait;

	/**
	 *	ContainerAwareTrait
	 */
	function __set($name, $value) { return $this->container->__set($name, $value); }
	function &__get($name) { return $this->container->__get($name); }
	function __isset($name) { return $this->container->__isset($name); }
	function __unset($name) { return $this->container->__unset($name); }
	function &__call($method, $args) { return $this->container->__call($method, $args); }

	function set($key, $value) { return $this->container->set($key, $value); }
	function &get($key) { return $this->container->get($key); }
	function has($key) { return $this->container->has($key); }
	function &merge($key, array $arr) { return $this->container->merge($key, $arr); }
	function remove($key) { return $this->container->remove($key); }
}
