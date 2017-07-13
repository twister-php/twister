<?php

namespace Twister;

interface ContainerInterface
{
	function __set($name, $value);
	function &__get($name);
	function __isset($name);
	function __unset($name);
	function &__call($method, $args);

	function set($key, $value);
	function &get($key);
	function has($key);
	function &merge($key, array $arr);
	function remove($key);
}
