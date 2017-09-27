<?php

namespace Twister\ORM;

abstract class SimpleMap extends EntityMap
{
	function __construct()
	{

	}

	function build()
	{
		return new SimpleMapper(new static());
	}
}
