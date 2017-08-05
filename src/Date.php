<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php

use ArrayAccess;				//	http://php.net/manual/en/class.arrayaccess.php					Interface to provide accessing objects as arrays.
use IteratorAggregate;			//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.

/*
use ArrayAccess;				//	http://php.net/manual/en/class.arrayaccess.php					Interface to provide accessing objects as arrays.
use ArrayIterator;				//	http://php.net/manual/en/class.arrayiterator.php				This iterator allows to unset and modify values and keys while iterating over Arrays and Objects.
use Countable;					//	http://php.net/manual/en/class.countable.php					Classes implementing Countable can be used with the count() function.
use IteratorAggregate;			//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.
use Exception;					//	http://php.net/manual/en/class.exception.php					Exception is the base class for all Exceptions in PHP 5, and the base class for all user exceptions in PHP 7.
use InvalidArgumentException;	//	http://php.net/manual/en/class.invalidargumentexception.php		Exception thrown if an argument is not of the expected type.
use OutOfBoundsException;		//	http://php.net/manual/en/class.outofboundsexception.php			Exception thrown if a value is not a valid key. This represents errors that cannot be detected at compile time.
use OutOfRangeException;		//	http://php.net/manual/en/class.outofrangeexception.php			Exception thrown when an illegal index was requested. This represents errors that should be detected at compile time.
use BadMethodCallException;		//	http://php.net/manual/en/class.badmethodcallexception.php		Exception thrown if a callback refers to an undefined method or if some arguments are missing.
use LengthException;			//	http://php.net/manual/en/class.lengthexception.php				Exception thrown if a length is invalid.
use LogicException;				//	http://php.net/manual/en/class.logicexception.php				Exception that represents error in the program logic. This kind of exception should lead directly to a fix in your code.
use DomainException;			//	http://php.net/manual/en/class.domainexception.php				Exception thrown if a value does not adhere to a defined valid data domain.
use RangeException;				//	http://php.net/manual/en/class.rangeexception.php				Exception thrown to indicate range errors during program execution. Normally this means there was an arithmetic error other than under/overflow. This is the runtime version of DomainException.
use UnexpectedValueException;	//	http://php.net/manual/en/class.unexpectedvalueexception.php		Exception thrown if a value does not match with a set of values. Typically this happens when a function calls another function and expects the return value to be of a certain type or value not including arithmetic or buffer related errors.
use OverflowException;			//	http://php.net/manual/en/class.overflowexception.php			Exception thrown when adding an element to a full container.
use UnderflowException;			//	http://php.net/manual/en/class.underflowexception.php			Exception thrown when performing an invalid operation on an empty container, such as removing an element.
*/

class Date implements IteratorAggregate, ArrayAccess
{
	protected $str = null;

	public static $utc = null;

	/**
	 *
	 *
	 *	@link http://php.net/manual/en/datetime.construct.php
	 *
	 *
	 *	@param  mixed  $str      Value to modify, after being cast to string
	 *	@param  string $encoding The character encoding
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public function __construct($date)
	{
		//	I will need a FAST constructor if I want to return new Date objects from functions like 'add', 'sub', 'next' etc.
		$this->str = $date;
		return;

		if (is_string($date))
		{
			if (strlen($date) === 10) // 1234-67-90
			{
				if (preg_match('/\d\d\d\d-\d\d-\d\d/', $date) === 1) // '~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~'
				{
					$this->str = $date;
					return;
				}
				throw new InvalidArgumentException('Date string must have the following format: "YYYY-MM-DD"');
			}
			throw new InvalidArgumentException('Date string must have 10 characters');
		}
		else
		{
			if (is_array($date))
			{
				if (count($date) === 3)
				{
					if (isset($date['year']) && isset($date['month']) && isset($date['day']))
					{
						
					}
					else if (isset($date[0]) && isset($date[1]) && isset($date[2]) &&
							is_numeric($date[0]) && is_numeric($date[0]) && is_numeric($date[0]) &&
							$date[0] >= 0 && $date[0] <= 9999 && is_numeric($date[0]) && is_numeric($date[0]))
					{
					}
				}
				else
				{
					throw new InvalidArgumentException('Invalid date array `' . print_r($date, true) . '` passed to Twister\Date, array must have 3 members!');
				}
			}
			else if ($date instanceof DateTime)
			{
			
			}
			else if (is_object($date))
			{
				if ( ! method_exists($date, '__toString'))
					throw new InvalidArgumentException('Passed object must have a __toString method');
				$this->str = (string) $date;
			}
		}
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

	/**
	 *	Create a Twister\Date object or returns null
	 *
	 *	@link http://php.net/manual/en/datetime.construct.php
	 *
	 *
	 *	@param  mixed  $str      Value to modify, after being cast to string
	 *	@param  string $encoding The character encoding
	 *	@return new Twister\Date isntance or false on failure
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public static function create($time = 'now', $timezone = null)
	{
		return new static($time, $timezone);
	}

	/**
	 *	Check if a given value or values are valid
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	Date::check('0000-00-00')								=== false
	 *	Date::check('2017-08-05')								=== true
	 *	Date::check('2017', '08, '05')							=== true
	 *	Date::check(['2017', '08, '05'])						=== true
	 *	Date::check([2017, 8, 5])								=== true
	 *	Date::check(['year' => 2017, 'month' => 8, 'day' => 5])	=== true
	 *	Date::check([8, 5, 2017])								=== true	- year is 3rd array member, same as `checkdate` and MUST be over 100
	 *	Date::check([30, 4, 2017])								=== false	- year is 3rd array member, same as `checkdate` BUT `day` MUST be 2nd param and `month` MUST be 3rd param like `checkdate()`
	 *
	 *	@param  mixed  $str      Value to modify, after being cast to string
	 *	@return new Twister\Date isntance or false on failure
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public static function check(...$params)
	{
		return self::normalize(...$params) !== false;
	}

	/**
	 *	Normalize a given value or values to MySQL date format (YYYY-MM-DD)
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	Date::check('0000-00-00')								=== false
	 *	Date::check('2017-08-05')								=== true
	 *	Date::check('2017', '08, '05')							=== true
	 *	Date::check(['2017', '08, '05'])						=== true
	 *	Date::check([2017, 8, 5])								=== true
	 *	Date::check(['year' => 2017, 'month' => 8, 'day' => 5])	=== true
	 *	Date::check([8, 5, 2017])								=== true	- year is 3rd array member, same as `checkdate` and MUST be over 100
	 *	Date::check([30, 4, 2017])								=== false	- year is 3rd array member, same as `checkdate` BUT `day` MUST be 2nd param and `month` MUST be 3rd param like `checkdate()`
	 *
	 *	@param  mixed  $str      Value to modify, after being cast to string
	 *	@return new Twister\Date isntance or false on failure
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public static function normalize(...$params)
	{
		switch (count($params))
		{
			case 2:
				if ( ! is_bool($params[1]))
				{
					if (is_string($params[1]))	//	$params[1] can be a format like 'Y-m-d h:i' or whatever, we need to `extract` the date component to match '~^...$~' below
					{
						// TODO: extract date from format
					}
					return false;
				}
				else
				{
					if ($params[1] === true)
					{
						$date = $params[0];
						if (is_string($date) && $date === '0000-00-00')
						{
							return $date;	// `true` allows 0000-00-00
						}
					}
				}
				//	fallthrough
			case 1:
				$date = $params[0];
				if (is_string($date))
				{
					if (preg_match('~^([1-9]\d\d\d)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$~', $date, $matches) === 1)
					{
						return checkdate($matches[2], $matches[3], $matches[1]) ? $date : false;	//	checkdate(month, day, year)
					}
					else if (preg_match('~([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])~', $date, $matches) === 1)		//	removed the ~^ ... $~ first & last matches! Also added more delimeters
					{
						$date = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
						return checkdate($matches[2], $matches[3], $matches[1]) ? $date : false;	//	checkdate(month, day, year)
					}
					else
					{
						//	... can we match other date formats?
						//	http://php.net/manual/en/function.strtotime.php
						return false;
					}
				}
				else if (is_array($date))
				{
					if (isset($date[0]) && isset($date[1]) && isset($date[2]))
					{
						$year	=	$date[0];	//	$date[0] could be month
						$month	=	$date[1];	//	$date[1] could be day
						$day	=	$date[2];	//	$date[2] could be year
						if (is_numeric($year) && is_numeric($month) && is_numeric($day))
						{
							if ($day >= 1000 && $year <= 12 && $month <= 31)
							{
								//	We switch the year, month, day to month, day, year ... ie. US format
								$year	=	$date[2];
								$month	=	$date[0];
								$day	=	$date[1];
							}
						}
						else
						{
							return false;
						}
					}
					else if (isset($date['year']) && isset($date['month']) && isset($date['day']))
					{
						$year	=	$date['year'];
						$month	=	$date['month'];
						$day	=	$date['day'];
						if ( ! is_numeric($year) || ! is_numeric($day))
						{
							return false;
						}
						if ( ! is_numeric($month))
						{
							if (is_string($month))
							{
								switch (strtolower($month))
								{
									case 'jan'; $month =  1; break;
									case 'feb'; $month =  2; break;
									case 'mar'; $month =  3; break;
									case 'apr'; $month =  4; break;
									case 'may'; $month =  5; break;
									case 'jun'; $month =  6; break;
									case 'jul'; $month =  7; break;
									case 'aug'; $month =  8; break;
									case 'sep'; $month =  9; break;
									case 'oct'; $month = 10; break;
									case 'nov'; $month = 11; break;
									case 'dec'; $month = 12; break;
									case 'january';		$month =  1; break;
									case 'february';	$month =  2; break;
									case 'march';		$month =  3; break;
									case 'april';		$month =  4; break;
								//	case 'may';			$month =  5; break;	//	same
									case 'june';		$month =  6; break;
									case 'july';		$month =  7; break;
									case 'august';		$month =  8; break;
									case 'september';	$month =  9; break;
									case 'october';		$month = 10; break;
									case 'november';	$month = 11; break;
									case 'december';	$month = 12; break;
									default: return false;
								}
							}
							else
							{
								return false;
							}
						}
					}
					else if (isset($date['tm_year']) && isset($date['tm_mon']) && isset($date['tm_mday']))
					{
						//	extract a date from strptime()
						//	http://php.net/manual/en/function.strptime.php
						$year	=	$date['tm_year'];						//	"tm_year"	Years since 1900
						$month	=	$date['tm_mon'];						//	"tm_mon"	Months since January (0-11)
						$day	=	$date['tm_mday'];						//	"tm_mday"	Day of the month (1-31)
						if ( ! is_numeric($year) || ! is_numeric($month) || ! is_numeric($day))
						{
							return false;
						}
						$year += 1900;					//	WARNING: untested!
						$month++;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
				break;

			case 3:

				//	because the 2nd param (month) might be a string, I don't want to copy the string matching months code!
				return self::normalize($params);
		}

		$year	=	(int) $year;
		$month	=	(int) $month;
		$day	=	(int) $day;

		if ($year < 1000 || $month < 1 || $day < 1 || $day > 31 || $month > 12 || $year > 9999)
			return false;

		if (checkdate($month, $day, $year))
			return str_pad($year, 4, '0', STR_PAD_LEFT) . str_pad($month, 3, '-0', STR_PAD_LEFT) . str_pad($day , 3, '-0', STR_PAD_LEFT);

		return false;
	}

	/**
	 * Returns the value in $str.
	 *
	 * @return string The current value of the $str property
	 */
	public function __toString()
	{
		return $this->str;
	}

	/**
	 *
	 * @param  string $start start date
	 * @param  string $end    end date
	 * @return true/false if the date is between a date range ...
	 */
	public function between($start, $end)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	/**
	 *	Convert any date format into valid MySQL date format 'YYYY-MM-DD'
	 *	eg. YYYY.MM.DD -> YYYY-MM-DD
	 *
	 * @return 
	 */
	public function dasherize()
	{
		return $this->delimit('-');
	}

	/**
	 *
	 * @param  string $delimiter 
	 * @return string
	 */
	public function delimit($delimiter)
	{
		return implode($delimiter, explode('-', $this->str));
	}

	/**
	 *
	 * @return string[]|null Returns an array with 'year', 'month' and 'day'
	 *                       from a matching date in the format 'YYYY-MM-DD', or null on failure
	 */
	public function format($format = 'Y-m-d', $tz = 'UTC')
	{
		return (new \DateTime($this->str, new \DateTimeZone($tz)))->format($format);
	}

	/**
	 *	PHP version of the MySQL FROM_DAYS() function
	 *
	 *	@link	https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_from-days
	 *
	 * @return string[]|null Returns an array with 'year', 'month' and 'day'
	 *                       from a matching date in the format 'YYYY-MM-DD', or null on failure
	 */
	public static function from_days($days)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	/**
	 *	PHP version of the MySQL FROM_DAYS() function
	 *
	 *	@link	https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_from-days
	 *
	 * @return string[]|null Returns an array with 'year', 'month' and 'day'
	 *                       from a matching date in the format 'YYYY-MM-DD', or null on failure
	 */
	public static function from_unixtime($time)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	/**
	 * Gets a hash code of the internal date.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function hash($algo = 'md5', $raw_output = false)
	{
		return hash($algo, $this->str, $raw_output);
	}

	/**
	 * Gets a hash code of the internal string.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function getHash($algo = 'md5', $raw_output = false)
	{
		return hash($algo, $this->str, $raw_output);
	}

	/**
	 *
	 * @return Human readable date format, eg. 24 October 1977
	 */
	public function humanize()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	/**
	 *
	 * @return
	 */
	public function isBlank()
	{
		return $this->str === null || $this->str === '0000-00-00';
	}

	/**
	 *	Gets an MD5 hash code of the internal date. Return result can be raw binary or hex by default
	 *
	 *	@param  bool|null $raw_output return the raw binary bytes or hex values of the md5 hash
	 *	@return string
	 */
	public function md5($raw_output = false)
	{
		return hash('md5', $this->str, $raw_output);
	}

	/**
	 *
	 * @param  mixed   $offset The index to check
	 * @return boolean Whether or not the index exists
	 */
	public function offsetExists($offset)
	{
		throw new Exception('TODO');

		switch($offset)
		{
			case 0:	return (bool) substr($this->str, 0, 4);	//	0123-56-89
			case 1:	return (bool) substr($this->str, 5, 2);
			case 2:	return (bool) substr($this->str, 8, 2);
			case 'year':	return (bool) substr($this->str, 0, 4);	//	0123-56-89
			case 'month':	return (bool) substr($this->str, 5, 2);
			case 'day':		return (bool) substr($this->str, 8, 2);
		}
		return false;
	}

	/**
	 *
	 * @param  mixed $offset         The index from which to retrieve the char
	 * @return mixed                 The character at the specified index
	 * @throws \OutOfBoundsException If the positive or negative offset does
	 *                               not exist
	 */
	public function offsetGet($offset)
	{
		throw new Exception('TODO');

		switch($offset)
		{
			case 'year':	return substr($this->str, 0, 4);	//	0123-56-89
			case 'month':	return substr($this->str, 5, 2);
			case 'day':		return substr($this->str, 8, 2);
			case 0:	return substr($this->str, 0, 4);	//	0123-56-89
			case 1:	return substr($this->str, 5, 2);
			case 2:	return substr($this->str, 8, 2);
		}
		return false;
	}

	/**
	 * Implements part of the ArrayAccess interface, but throws an exception
	 * when called. This maintains the immutability of Stringy objects.
	 *
	 * @param  mixed      $offset The index of the character
	 * @param  mixed      $value  Value to set
	 * @throws \Exception When called
	 */
	public function offsetSet($offset, $value)
	{
		throw new Exception('TODO');

		switch($offset)
		{
			case 'year':	return $value;	//	0123-56-89
			case 'month':	return $value;
			case 'day':		return $value;
			case 0:	return $value;	//	0123-56-89
			case 1:	return $value;
			case 2:	return $value;
		}
		throw new Exception('TODO');
	}

	/**
	 * Implements part of the ArrayAccess interface, but throws an exception
	 * when called. This maintains the immutability of Stringy objects.
	 *
	 * @param  mixed      $offset The index of the character
	 * @throws \Exception When called
	 */
	public function offsetUnset($offset)
	{
		throw new Exception('TODO');
	}

	/**
	 *	PHP equivalent of the LAST_DAY() MySQL function
	 *
	 *	`Takes a date or datetime value and returns the corresponding value for the last day of the month. Returns NULL if the argument is invalid.`
	 *
	 *	@link	https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_last-day
	 *
	 *	@return int
	 */
	public function last_day()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	/**
	 *	PHP equivalent of the QUARTER() MySQL function
	 *
	 *	`Returns the quarter of the year for date, in the range 1 to 4.`
	 *
	 *	@link	https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_quarter
	 *
	 *	@return int
	 */
	public function quarter()
	{
		return (int) (($this->month / 4) + 1);
	}

	/**
	 * Gets a SHA1 (160-bit) hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA1 hash
	 * @return string
	 */
	public function sha1($raw_output = false)
	{
		return hash('sha1', $this->str, $raw_output);
	}

	/**
	 * Gets a SHA-256 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-256 hash
	 * @return string
	 */
	public function sha256($raw_output = false)
	{
		return hash('sha256', $this->str, $raw_output);
	}

	/**
	 * Gets a SHA-384 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-384 hash
	 * @return string
	 */
	public function sha384($raw_output = false)
	{
		return hash('sha384', $this->str, $raw_output);
	}

	/**
	 * Gets a SHA-512 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-512 hash
	 * @return string
	 */
	public function sha512($raw_output = false)
	{
		return hash('sha512', $this->str, $raw_output);
	}

	/**
	 *
	 * @return array
	 */
	public function toArray()
	{
		return explode('-', $this->str);
	}

	/**
	 *
	 * @return int
	 */
	public function toUnixTimestamp()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}

	function is_leap_year()
	{
		return $this->year % 100 == 0 ? $this->year % 400 == 0 : $this->year % 4 == 0;
	}

	function isLeapYear()
	{
		return $this->year % 100 == 0 ? $this->year % 400 == 0 : $this->year % 4 == 0;
	}

	function __get($name)
	{
		switch ($name)
		{
			case 'year':	return substr($this->str, 0, 4);
			case 'month':	return substr($this->str, 5, 2);
			case 'day':		return substr($this->str, 8, 2);
			case 'days':	return ;

			//	http://php.net/manual/en/function.date.php
			case 'd':	return ;				//	Day of the month, 2 digits with leading zeros										eg. 01 to 31
			case 'D':	return ;				//	A textual representation of a day, three letters									eg. Mon through Sun
			case 'j':	return ;				//	Day of the month without leading zeros												eg. 1 to 31
			case 'l':	return ;				//	A full textual representation of the day of the week								eg. Sunday through Saturday
			case 'N':	return ;				//	ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0			eg. 1 (for Monday) through 7 (for Sunday)
			case 'S':	return ;				//	English ordinal suffix for the day of the month, 2 characters			eg.  	st, nd, rd or th. Works well with j
			case 'w':	return ;				//	Numeric representation of the day of the week			eg. 0 (for Sunday) through 6 (for Saturday)
			case 'z':	return ;				//	 	The day of the year (starting from 0)			eg. 0 through 365
			case 'W':	return ;				//	ISO-8601 week number of year, weeks starting on Monday			eg. Example: 42 (the 42nd week in the year)
			case 'F':	return ;				//	A full textual representation of a month, such as January or March			eg. January through December
			case 'm':	return ;				//	Numeric representation of a month, with leading zeros			eg. 01 through 12
			case 'M':	return ;				//	A short textual representation of a month, three letters			eg. Jan through Dec
			case 'n':	return ;				//	Numeric representation of a month, without leading zeros			eg. 1 through 12
			case 't':	return ;				//	Number of days in the given month			eg. 28 through 31
			case 'L':	return ;				//	Whether it's a leap year			eg. 1 if it is a leap year, 0 otherwise.
			case 'o':	return ;				//	ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)			eg. Examples: 1999 or 2003
			case 'Y':	return ;				//	A full numeric representation of a year, 4 digits			eg. Examples: 1999 or 2003
			case 'y':	return ;				//	A two digit representation of a year			eg. Examples: 99 or 03

			//	`time` related formats are missing!

			case 'U':	return ;				//	Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)			eg. See also time()

			case 'quarter':	return $this->month / 4 + 1;

			default:

				if ( ! ctype_upper($name))
					$name = strtoupper($name);

				//	MySQL related values:
				switch (strtoupper($name))
				{
					case 'DAYNAME':		//	https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_dayname
					case 'DAYOFWEEK':
					case 'DAYOFMONTH':
					case 'DAYOFYEAR':

					//--- Start of alias or mixed-case properties ---//
					case 'YEAR':		return substr($this->str, 0, 4);
					case 'MONTH':		return substr($this->str, 5, 2);
					case 'DAY':			return substr($this->str, 8, 2);
				}

				$name = strtolower($name);

				if (self::$hashAlgos === null)
					self::$hashAlgos = array_flip(hash_algos());	//	set the hash algorithms as keys for faster lookup with isset() instead of in_array()!

				if (isset(self::$hashAlgos[$name]))					//	we converted the hash name to lowercase above so we can safely support this: $this->Sha256
					return hash($name, $this->str);
		}
	}

	public function getIterator()
	{
		return new \Twister\DateIterator($this->str);
	}

	public function getYear()
	{
		return substr($this->str, 0, 4);
	}

	public function getMonth()
	{
		return substr($this->str, 5, 2);
	}

	public function getDay()
	{
		return substr($this->str, 8, 2);
	}

	static function curdate($format = 'Y-m-d')
	{
		//static $curdate = new static(date('Y-m-d'));
		//return $curdate;

		//	alternative
		return new static(date($format));
	}

	/**
	 *	Returns true if the string contains a date in the format 'YYYY-MM-DD' AND is a valid Gregorian date
	 *
	 *	All date patterns MUST have 3x (..)
	 *
	 *	@alias isValid()
	 *	@alias validate()
	 *
	 *	Alternative patterns:
	 *		'/^(\d\d\d\d)-(\d\d)-(\d\d)$/'
	 *		'/^(\d{4})-(\d{2})-(\d{2}) [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 *		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	@return bool
	 */
	public static function isDate($date, $pattern = '~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~')
	{
		return preg_match($pattern, $date, $matches) === 1 && checkdate($matches[2], $matches[3], $matches[1]);	//	checkdate(month, day, year)
	}

	/**
	 *	Returns true if the string contains a date in the format 'YYYY-MM-DD' AND is a valid Gregorian date
	 *
	 *	@alias isDate()
	 *	@alias validate()
	 *
	 *	Alternative patterns:
	 *		'/^(\d\d\d\d)-(\d\d)-(\d\d)$/'
	 *		'/^(\d{4})-(\d{2})-(\d{2}) [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 *		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	@return bool
	 */
	public static function isValid($date, $pattern = '~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~')
	{
		return preg_match($pattern, $date, $matches) === 1 && checkdate($matches[2], $matches[3], $matches[1]);	//	checkdate(month, day, year)
	}

	/**
	 *	Returns true if the string contains a date in the format 'YYYY-MM-DD' AND is a valid Gregorian date
	 *
	 *	@alias isDate()
	 *	@alias isValid()
	 *
	 *	Alternative patterns:
	 *		'/^(\d\d\d\d)-(\d\d)-(\d\d)$/'
	 *		'/^(\d{4})-(\d{2})-(\d{2}) [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 *		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	@return bool
	 */
	public static function validate($date, $pattern = '~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~')
	{
		return preg_match($pattern, $date, $matches) === 1 && checkdate($matches[2], $matches[3], $matches[1]);	//	checkdate(month, day, year)
	}

}

\Twister\Date::$utc = new \DateTimeZone('UTC');
