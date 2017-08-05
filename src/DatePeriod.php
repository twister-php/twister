<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php

use IteratorAggregate;			//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.

class DatePeriod extends DatePeriod implements IteratorAggregate
{
	public function __construct($date)
	{
		
	}

	public function __construct()
	{
		$this->position = 0;
	}

	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return $this->array[$this->position];
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return isset($this->array[$this->position]);
	}
}
