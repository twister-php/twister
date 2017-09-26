<?php

namespace Twister\ORM;

abstract class SimpleMap
{
	function __construct()
	{

	}

	function build()
	{
		return new SimpleMapper(new static());
	}
}
