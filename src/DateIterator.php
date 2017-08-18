<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php
use \DateInterval;				//	http://php.net/manual/en/class.dateinterval.php
use \DateTimeZone;				//	http://php.net/manual/en/class.datetimezone.php
use \DatePeriod;				//	http://php.net/manual/en/class.dateperiod.php

use \Iterator;					//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.

class DateIterator implements Iterator
{
	private $start		= null;
	private $current	= null;
	private $interval	= null;

	public static $utc	= null;
	public static $p1d	= null;

	public function __construct($date, $interval_spec = null)
	{
		$this->start	= (is_string($date) ? new \DateTime($date, self::$utc) : $date);
		$this->current	= clone $this->start;
		$this->interval	= $interval_spec === null ? self::$p1d : (is_string($interval_spec) ? new \DateInterval($interval_spec) : $interval_spec);
	}

	/**
	 * Returns the $current value as MySQL Date format ('YYYY-MM-DD')
	 *
	 * @return string The $current property
	 */
	public function __toString()
	{
		return $this->current->format('Y-m-d');
	}

	public function rewind()
	{
		$this->current = clone $this->start;
	}

	public function current()
	{
		return $this->current->format('Y-m-d');
	}

	public function key()
	{
		return (int) $this->start->diff($this->current)->format('%r%a');
	}

	public function next()
	{
		$this->current->add($this->interval);
		return $this;
	}

	public function prev()
	{
		$this->current->sub($this->interval);
		return $this;
	}

	public function valid()
	{
		return true;	// basically allows the DateIterator to be infinite
	}

	//	Sets the `current` value!
	public function toDate($date)
	{
		$this->current = (is_string($date) ? new \DateTime($date, self::$utc) : $date);
	}

	public function getInterval()
	{
		return $this->interval;
	}

	public function setInterval($interval_spec = 'P1D')
	{
		$this->interval = new \DateInterval($interval_spec);
		return $this;
	}

	/**
	 *	Wrapper around DateTime::modify()
	 *
	 *	`Alter the timestamp of a DateTime object by incrementing or decrementing in a format accepted by strtotime().`
	 *
	 *	@link	http://php.net/manual/en/datetime.modify.php
	 *	@link	http://php.net/manual/en/function.strtotime.php
	 *
	 *	$return $this
	 */
	public function modify($modify = '+1 day')
	{
		$this->current->modify($modify);
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->add()
	 *
	 *	@link http://php.net/manual/en/datetime.add.php
	 *	@link http://php.net/manual/en/class.dateinterval.php
	 *	@link http://php.net/manual/en/dateinterval.construct.php
	 *	@link https://en.wikipedia.org/wiki/Iso8601#Durations
	 *
	 *	Simple examples:
	 *		Two days is P2D.
	 *		Two seconds is PT2S.
	 *		Six years and five minutes is P6YT5M.
	 *
	 *	Formats are based on the » ISO 8601 duration specification. 
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return new Twister\Date isntance or false on failure
	 */
	public function add($interval_spec = 'P1D')
	{
		$this->str = (new \DateTime($this->str, new \DateTimeZone('UTC')))->add(new \DateInterval($interval_spec))->format('Y-m-d');
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->sub()
	 *
	 *	@link http://php.net/manual/en/datetime.sub.php
	 *	@link http://php.net/manual/en/class.dateinterval.php
	 *	@link http://php.net/manual/en/dateinterval.construct.php
	 *	@link https://en.wikipedia.org/wiki/Iso8601#Durations
	 *
	 *	Simple examples:
	 *		Two days is P2D.
	 *		Two seconds is PT2S.
	 *		Six years and five minutes is P6YT5M.
	 *
	 *	Formats are based on the » ISO 8601 duration specification. 
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return new Twister\Date isntance or false on failure
	 */
	public function sub($interval_spec = 'P1D')
	{
		$this->str = (new \DateTime($this->str, new \DateTimeZone('UTC')))->add(new \DateInterval($interval_spec))->format('Y-m-d');
		return $this;
	}

	function __get($name)
	{
		switch ($name)
		{
			case 'year':	return $this->current->format('Y');
			case 'month':	return $this->current->format('m');
			case 'day':		return $this->current->format('d');

			//	http://php.net/manual/en/function.date.php
			case 'd':		return $this->current->format('d');				//	Day of the month, 2 digits with leading zeros									eg. 01 to 31
			case 'D':		return $this->current->format('D');				//	A textual representation of a day, three letters								eg. Mon through Sun
			case 'j':		return $this->current->format('j');				//	Day of the month without leading zeros											eg. 1 to 31
			case 'l':		return $this->current->format('l');				//	A full textual representation of the day of the week							eg. Sunday through Saturday
			case 'N':		return $this->current->format('N');				//	ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0		eg. 1 (for Monday) through 7 (for Sunday)
			case 'S':		return $this->current->format('S');				//	English ordinal suffix for the day of the month, 2 characters					eg. st, nd, rd or th. Works well with j
			case 'w':		return $this->current->format('w');				//	Numeric representation of the day of the week									eg. 0 (for Sunday) through 6 (for Saturday)
			case 'z':		return $this->current->format('z');				//	The day of the year (starting from 0)											eg. 0 through 365
			case 'W':		return $this->current->format('W');				//	ISO-8601 week number of year, weeks starting on Monday							eg. Example: 42 (the 42nd week in the year)
			case 'F':		return $this->current->format('F');				//	A full textual representation of a month, such as January or March				eg. January through December
			case 'm':		return $this->current->format('m');				//	Numeric representation of a month, with leading zeros							eg. 01 through 12
			case 'M':		return $this->current->format('M');				//	A short textual representation of a month, three letters						eg. Jan through Dec
			case 'n':		return $this->current->format('n');				//	Numeric representation of a month, without leading zeros						eg. 1 through 12
			case 't':		return $this->current->format('t');				//	Number of days in the given month												eg. 28 through 31
			case 'L':		return $this->current->format('L');				//	Whether it's a leap year														eg. 1 if it is a leap year, 0 otherwise.
			case 'o':		return $this->current->format('o');				//	ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)			eg. Examples: 1999 or 2003
			case 'Y':		return $this->current->format('Y');				//	A full numeric representation of a year, 4 digits								eg. Examples: 1999 or 2003
			case 'y':		return $this->current->format('y');				//	A two digit representation of a year											eg. Examples: 99 or 03

			//	`time` related formats are missing!

			case 'U':		return $this->current->format('U');				//	Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)						eg. See also time()

			case 'quarter':	return $this->current->format('m') / 4 + 1;

			default:

				if ( ! ctype_lower($name))
					$name = strtolower($name);

				if (self::$hashAlgos === null)
					self::$hashAlgos = array_flip(hash_algos());	//	set the hash algorithms as keys for faster lookup with isset() instead of in_array()!

				if (isset(self::$hashAlgos[$name]))					//	we converted the hash name to lowercase above so we can safely support this: $this->Sha256
					return hash($name, $this->str);

				//--- Start of alias and mixed-case properties ---//

				switch ($name)
				{
					//	possible mixed-case variants `normalized` to lowercase. eg. `Scheme` => `scheme`
					case 'length':		return \strlen($this->str);
				}
		}
	}

	function __set($name, $value)
	{
		switch ($name)
		{
			case 'year':	return $this->encoding	=	mb_internal_encoding();
			case 'month':	return strlen($this->_str);
			case 'day':		return strlen($this->_str);
		}
	}

}

DateIterator::$utc = new \DateTimeZone('UTC');
