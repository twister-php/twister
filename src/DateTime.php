<?php

namespace Twister;

use \DateTime as Dt;			//	http://php.net/manual/en/class.datetime.php
use \DateInterval;				//	http://php.net/manual/en/class.dateinterval.php
use \DateTimeZone;				//	http://php.net/manual/en/class.datetimezone.php

use \ArrayAccess;				//	http://php.net/manual/en/class.arrayaccess.php					Interface to provide accessing objects as arrays.
use \IteratorAggregate;			//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.
use \Closure;

use \InvalidArgumentException;	//	http://php.net/manual/en/class.invalidargumentexception.php		Exception thrown if an argument is not of the expected type.

/**
 *	@method int getDay() Get day of month.
 *	@method int getMonth() Get the month.
 *	@method int getYear() Get the year.
 *	@method int getHour() Get the hour.
 *	@method int getMinute() Get the minutes.
 *	@method int getSecond() Get the seconds.
 *	@method string getDayOfWeek() Get the day of the week, e.g., Monday.
 *	@method int getDayOfWeekAsNumeric() Get the numeric day of week.
 *	@method int getDaysInMonth() Get the number of days in the month.
 *	@method int getDayOfYear() Get the day of the year.
 *	@method string getDaySuffix() Get the suffix of the day, e.g., st.
 *	@method bool isLeapYear() Determines if is leap year.
 *	@method string isAmOrPm() Determines if time is AM or PM.
 *	@method bool isDaylightSavings() Determines if observing daylight savings.
 *	@method int getGmtDifference() Get difference in GMT.
 *	@method int getSecondsSinceEpoch() Get the number of seconds since epoch.
 *	@method string getTimezoneName() Get the timezone name.
 *	@method setDay(int $day) Set the day of month.
 *	@method setMonth(int $month) Set the month.
 *	@method setYear(int $year) Set the year.
 *	@method setHour(int $hour) Set the hour.
 *	@method setMinute(int $minute) Set the minutes.
 *	@method setSecond(int $second) Set the seconds.
 */

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

class DateTime extends Dt implements ArrayAccess, IteratorAggregate
{
	private static $date	=	null;	//	block it to catch errors!


	/**
	 *	Default DateTimeZone
	 *
	 *	@var	\DateTimeZone
	 */
	public static $dtz	=	null;


	/**
	 *	'UTC' DateTimeZone
	 *
	 *	@var	\DateTimeZone
	 */
	public static $utc	=	null;


	/**
	 *	'P1D' DateInterval
	 *
	 *	@var	\DateInterval
	 */
	public static $p1d	=	null;


	/**
	 *	Interval Cache
	 *
	 *	@var	\DateInterval[]
	 */
	public static $intervals	=	null;


	/**
	 *	@var	string
	 */
	const DEFAULT_FORMAT = 'Y-m-d H:i:s'; // 'jS F, Y \a\\t g:ia';

	/**
	 *	Default format used by the __toString() magic method
	 *
	 *	@var	string
	 */
	public static $format = self::DEFAULT_FORMAT;



	/**
	 *	Create a new Twister\DateTime instance.
	 *
	 *	@link	http://php.net/manual/en/datetime.construct.php
	 *
	 *	@param \DateTime|string|null     $date
	 *	@param \DateTimeZone|string|null $tz
	 */
	public function __construct(...$params)
	{
parent::__construct(...$params);
return;

		switch (count($params))
		{
			case 1: //	'2017-01-01'
			case 2:	//	'2017-01-01', $tz
			case 3:	//	$year, $month, $day
			case 4:	//	$year, $month, $day, $tz
			case 5:	//	$year, $month, $day, $hour, $minute							// not useful
			case 6:	//	$year, $month, $day, $hour, $minute, $second
			case 7:	//	$year, $month, $day, $hour, $minute, $second, $timezone
		}
		return new static(new \DateTime($time, $timezone === null ? self::$utc : (is_string($timezone) ? new \DateTimeZone($timezone) : $timezone)));


		if (is_string($date))
		{
		//	$this->___date = new \DateTime($date, self::$dtz);
		}
		else if ($date instanceof \DateTime)
		{
		//	$this->___date = $date;
		}
		else if (is_object($date))
		{
		//	$this->date = new \DateTime((string) $date, self::$utc);
		}
		else if (is_array($date))
		{
			throw new \InvalidArgumentException('Array constructor not implemented');
		}
		else
		{
			throw new \InvalidArgumentException(sprintf(
						'Invalid type passed to Date constructor; expecting a string or DateTime object, received "%s"; use normalize() to create Date objects from various formats',
						(is_object($date) ? get_class($date) : gettype($date))
					));
		}
	}


	/**
	 *	Returns the value in $str.
	 *
	 *	@return string The current value of the $str property
	 */
	public function __toString()
	{
		return parent::format(static::$format);
	}


	/**
	 *	Returns a formatted string
	 *
	 *	@return string
	 */
	public function __invoke($format = 'Y-m-d H:i:s')
	{
		return parent::format($format);
	}


	/**
	 *	Implements IteratorAggregate
	 *
	 *	@link	http://php.net/manual/en/class.iteratoraggregate.php
	 *
	 *	@return \Twister\DatePeriod
	 */
	public function getIterator()
	{
		return new \Twister\DatePeriod($this);
	}


	/**
	 *	Gets a date clone
	 *
	 *	@return static
	 */
	public function copy()
	{
		return clone $this;
	}


	/**
	 *	Gets a date clone
	 *
	 *	@alias	copy()
	 *
	 *	@return static
	 */
	public function clone()
	{
		return clone $this;
	}


	/**
	 *	Wrapper around \DateTime->setDate() for the current date
	 *
	 *	@link http://php.net/manual/en/datetime.setdate.php
	 *
	 *	`Resets the current date of the DateTime object to a different date.`
	 *
	 *	In addition to the official params for setDate(),
	 *		this function will also extract a date from a string
	 *
     *	@param	int|string	$year    |  $date string
     *	@param	int|null	$month
     *	@param	int|null	$day
	 *
	 *	@throws \InvalidArgumentException
	 *
	 *	@return static
	 */
	public function setDateFromString(...$params)
	{
		switch (count($params))
		{
			case 3:
				return parent::setDate(...$params);
			case 1:
				$param = $params[0];
				if (is_string($param)) {
					if (preg_match('~(\d\d\d\d)-(\d\d)-(\d\d)~', $param, $values) === 1) {
						return parent::setDate($values[1], $values[2], $values[3]);	//	$values[0] is the initial preg_match() string
					}
					//	@todo ... support extracting dates from regular strings with getdate(), date_parse() etc.
					//	@todo ... support relative strings like '1 day' ... '+1 day', 'next monday' etc.
					throw new \InvalidArgumentException("Invalid string format `{$param}` passed to setDate(); expecting `YYYY-MM-DD`");
				}
				else if (is_object($param)) {
					if ($param instanceof \DateTime || $param instanceof \Twister\DateTime) {
						return parent::setDate(...explode('-', $param->format('Y-m-d')));
					} else {
					//	if ( ! method_exists($param1, '__toString'))
					//		throw new \InvalidArgumentException('Object passed to setDate() must have a __toString method');
						//	@todo ... currently not supported, I would have to extract the date from the object with (string)
					//	$this->date = new \DateTime((string) $param1, self::$utc);
					}
				}
				throw new \InvalidArgumentException(sprintf(
							'Invalid type passed to ->setDate(), received "%s"',
							(is_object($param) ? get_class($param) : gettype($param))
						));
		}
		throw new \InvalidArgumentException('Invalid number or type of params for setDate(); requires one Date string or 3x params for year, month, day');
	}


	/**
	 *	Wrapper around \DateTime->setTime() for the current time
	 *
	 *	@link	http://php.net/manual/en/datetime.settime.php
	 *
	 *	`Resets the current time of the DateTime object to a different time.`
	 *
     *	@param	int|string	$hour    |  $time string
     *	@param	int|null	$minute
     *	@param	int|null	$second
	 *
	 *	@throws \InvalidArgumentException
	 *
	 *	@return static
	 */
	public function setTimeFromString(...$params)
	{
		switch (count($params))
		{
			case 3:
				return parent::setTime(...$params);
			case 1:
				$param = $params[0];
				if (is_string($param))
				{
					if (preg_match('~([012]\d):([0-5]\d):([0-5]\d)~', $param, $values) === 1) {
						return parent::setTime(...array_slice($values, 1, 3));
					//	return parent::setTime($values[1], $values[2], $values[3]);		//	same thing as above
					}
					throw new \InvalidArgumentException("Invalid time format `{$param}` passed to setTime(); expecting `HH:MM:SS`");
				}
		}
		throw new \InvalidArgumentException('Invalid number or type of params for setTime(); requires one Time string or 3x params for hour, minute, second');
	}


	/**
	 *	Set the date and time all together
	 *
	 *	This function also supports sending a single DateTime string in `YYYY-MM-DD HH:MM:SS` format
	 *
	 *	@param	int|string  $year     |  $datetime string
	 *	@param	int         $month
	 *	@param	int         $day
	 *	@param	int         $hour
	 *	@param	int         $minute
	 *	@param	int         $second = 0
	 *
	 *	@throws \InvalidArgumentException
	 *
	 *	@return static
	 */
	public function setDateTime(...$params)
	{
		switch (count($params))
		{
			case 5:
				//	$second is optional in setTime()
			//	array_push($params, 0);		//	I don't think we need this ... array_slice should pass 2 params to setTime()!
				//	fallthrough
			case 6:
				return parent::setDate(...array_slice($params, 0, 3))->setTime(...array_slice($params, 3, 3));
			case 1:
				$param = $params[0];
				if (is_string($param))
				{
					if (preg_match('~(\d\d\d\d)-(\d\d)-(\d\d) ([012]\d):([0-5]\d):([0-5]\d)~', $param, $values) === 1) {
						return parent::setDate(...array_slice($values, 1, 3))->setTime(...array_slice($values, 4, 3));
					}
					throw new \InvalidArgumentException("Invalid DateTime format `{$param}` passed to setDateTime(); expecting `YYYY-MM-DD HH:MM:SS`");
				}
		}
		throw new \InvalidArgumentException('Invalid number or type of params for setDateTime(); requires one DateTime string or 6x params for year, month, day, hour, minute, second');
	}



	/**
	 *	Sets the date and time based on a Unix timestamp.
	 *
	 *	@link	http://php.net/manual/en/datetime.settimestamp.php
	 *
	 *	@param  int $unixtimestamp
	 *	@return $this
	 */
	public function setTimestamp($unixtimestamp)
	{
		return $this->setTimestamp($unixtimestamp);
	}


	/*****************************************************************\
	 *                    CREATE & CACHE INTERVALS                   *
	\*****************************************************************/


	/**
	 *	Create a DateInterval from ISO 8601 duration string OR relative parts!
	 *
	 *	This method does a small test on the $interval string for `[0] === 'P'`
	 *
	 *	@link	http://php.net/manual/en/dateinterval.construct.php
	 *	@link	http://php.net/manual/en/dateinterval.createfromdatestring.php
	 *	@link	http://php.net/manual/en/datetime.formats.relative.php
	 *
	 *	This function had a significant 30%~50% performance increase over ->modify()
	 *		still had about 20% increase over creating new DateInterval values in a loop
	 *
	 *	@param	string	$interval	Interval string in relative parts format or ISO 8601 duration
	 *
	 *	@return DateInterval
	 */
	public static function createInterval($interval)
	{
		return	isset(static::$intervals[$interval])
				?	static::$intervals[$interval]
				:	static::$intervals[$interval] = $interval[0] === 'P'
					?	new \DateInterval($interval)
					:	\DateInterval::createFromDateString($interval);
	}


	/**
	 *	Create a DateInterval from ISO 8601 duration string
	 *
	 *	@link	http://php.net/manual/en/dateinterval.construct.php
	 *
	 *	@return DateInterval
	 */
	public static function createIntervalFromSpec($interval_spec)
	{
		return	isset(static::$intervals[$interval_spec])
				?	static::$intervals[$interval_spec]
				:	static::$intervals[$interval_spec] = new \DateInterval($interval_spec);
	}


	/**
	 *	Create a DateInterval from a string of relative parts
	 *
	 *	`Uses the normal date parsers and sets up a DateInterval from the relative parts of the parsed string.`
	 *
	 *	@link	http://php.net/manual/en/dateinterval.createfromdatestring.php
	 *	@link	http://php.net/manual/en/datetime.formats.relative.php
	 *
	 *	The difference between `DateInterval::createFromDateString` and this method,
	 *		is that this method includes an interval cache.
	 *		Every interval string created is cached, so loops are faster!
	 *
	 *	@return DateInterval
	 */
	public static function createIntervalFromDateString($time)
	{
		return	isset(static::$intervals[$time])
				?	static::$intervals[$time]
				:	static::$intervals[$time] = \DateInterval::createFromDateString($time);
	}


	/*****************************************************************\
	 *                  ADDITIONS AND SUBTRACTIONS                   *
	\*****************************************************************/


	/**
	 *	Wrapper for DateTime::add()
	 *
	 *	`Adds an amount of days, months, years, hours, minutes and seconds to a DateTime object`
	 *
	 *	NOTE: This function ALSO supports DateInterval strings based on relative parts!
	 *		ie. the same as ->modify()
	 *		eg. ->add('1 day')
	 *			->add('1 month')
	 *			->add('1 year + 4 days')
	 *			->add('next Monday 2012-04-01')
	 *			->add('last day of +1 month')
	 *
	 *	@link	http://php.net/manual/en/datetime.formats.relative.php
	 *
	 *	Notes on performance benchmarks vs. modify()
	 *		->add() & ->sub() are +20% faster than ->modify() with new \DateInterval()
	 *		->add() & ->sub() are +50% faster than ->modify() with existing/cached \DateInterval()
	 *		Conclusion: ->modify() is slower than ->add() & ->sub() under both conditions
	 *
	 *	@link	http://php.net/manual/en/datetime.add.php
	 *	@link	http://php.net/manual/en/class.dateinterval.php
	 *	@link	http://php.net/manual/en/dateinterval.construct.php
	 *	@link	https://en.wikipedia.org/wiki/Iso8601#Durations
	 *
	 *	Simple examples:
	 *		Two days is P2D.
	 *		Two seconds is PT2S.
	 *		Six years and five minutes is P6YT5M.
	 *
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  DateInterval|string  $interval
	 *
	 *	@return static
	 */
	public function add($interval)
	{
		return $this->add($interval instanceof \DateInterval ? $interval : static::createInterval($interval));
	}


	/**
	 *	Wrapper for DateTime::sub()
	 *
	 *	`Subtracts an amount of days, months, years, hours, minutes and seconds from a DateTime object`
	 *
	 *	NOTE: This function ALSO supports DateInterval strings based on relative parts!
	 *		ie. the same as ->modify()
	 *		eg. ->sub('1 day')
	 *			->sub('1 month')
	 *			->sub('1 year + 4 days')
	 *			->sub('next Monday 2012-04-01')
	 *			->sub('last day of +1 month')
	 *
	 *	Notes on performance benchmarks vs. modify()
	 *		->add() & ->sub() are +20% faster than ->modify() with new \DateInterval()
	 *		->add() & ->sub() are +50% faster than ->modify() with existing/cached \DateInterval()
	 *		Conclusion: ->modify() is slower than ->add() & ->sub() under both conditions
	 *
	 *	@link	http://php.net/manual/en/datetime.sub.php
	 *	@link	http://php.net/manual/en/class.dateinterval.php
	 *	@link	http://php.net/manual/en/dateinterval.construct.php
	 *	@link	https://en.wikipedia.org/wiki/Iso8601#Durations
	 *
	 *	Simple examples:
	 *		Two days is P2D.
	 *		Two seconds is PT2S.
	 *		Six years and five minutes is P6YT5M.
	 *
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  DateInterval|string  $interval
	 *
	 *	@return static
	 */
	public function sub($interval)
	{
		return $this->sub($interval instanceof \DateInterval ? $interval : static::createInterval($interval));
	}


	/**
	 *	Wrapper around \DateTime->format()
	 *
	 *	@link	http://php.net/manual/en/datetime.format.php
	 *
	 *	@return string
	 */
	public function __format($format = 'Y-m-d')
	{
		return parent::format($format);
	}


	/**
	 *	Wrapper around \DateTime->modify()
	 *
	 *	->modify() is slower than ->add() & ->sub() under all conditions!
	 *
	 *	@link	http://php.net/manual/en/datetime.modify.php
	 *
	 *	`Alter the timestamp of a DateTime object by incrementing or decrementing in a format accepted by strtotime().`
	 *
	 *	@param  string $modify A date/time string.
	 *	@return $this
	 */
	public function __modify($modify = '+1 day')
	{
		$this->modify($modify);
		return $this;
	}


	/**
	 *	Create a new Twister\DateTime instance from a specific date and time.
	 *
	 *
	 *	If any of $year, $month or $day are set to null their now() values will be used.
	 *
	 *	If $hour is null it will be set to its now() value and the default
	 *	values for $minute and $second will be their now() values.
	 *
	 *	If $hour is not null then the default values for $minute and $second will be 0.
	 *
	 *	@link http://php.net/manual/en/datetime.construct.php
	 *
	 *	@param int|null                  $year
	 *	@param int|null                  $month
	 *	@param int|null                  $day
	 *	@param int|null                  $hour
	 *	@param int|null                  $minute
	 *	@param int|null                  $second
	 *	@param \DateTimeZone|string|null $tz
	 *
	 *	@return static
	 */
	public static function create(...$params)
	{
		switch (count($params))
		{
			case 1: //	'2017-01-01'
			case 2:	//	'2017-01-01', $tz
			case 3:	//	$year, $month, $day
			case 4:	//	$year, $month, $day, $tz
			case 5:	//	$year, $month, $day, $hour, $minute							// not useful
			case 6:	//	$year, $month, $day, $hour, $minute, $second
			case 7:	//	$year, $month, $day, $hour, $minute, $second, $timezone
		}

		return new static(new \DateTime($time, $timezone === null ? self::$utc : (is_string($timezone) ? new \DateTimeZone($timezone) : $timezone)));
	}


	/**
	 *	Wrapper around date_parse
	 *
	 *	@link	http://php.net/manual/en/function.date-parse.php
	 *
	 *
	 *	@param  mixed  $str      Value to modify, after being cast to string
	 *	@param  string $encoding The character encoding
	 *
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 *
	 *	@return static|null
	 */
	public static function __parse($date, &$result = null)							//	WARNING I don't think I  need this ...
	{
		$result = date_parse($date);

		if ($result['warning_count'] === 0 && $result['error_count'] === 0)			//	halt, I think there is a faster way to build the DateTime ... just parse it here, check for errors ... then just build it ....
		{
			$year		=	$result['year'];
			$month		=	$result['month'];
			$day		=	$result['day'];
			$hour		=	$result['hour'];
			$minute		=	$result['minute'];
			$second		=	$result['second'];
			$fraction	=	$result['fraction'];

			$format		=	null;
			$value		=	null;

			if (is_numeric($year) && is_numeric($month) && is_numeric($day))
			{
				$value	= str_pad($year, 4, '0', STR_PAD_LEFT) . str_pad($month, 3, '-0', STR_PAD_LEFT) . str_pad($day, 3, '-0', STR_PAD_LEFT);
				$format = 'Y-m-d';
			}

			if (is_numeric($hour) && is_numeric($minute) && is_numeric($second))
			{
				$value	.= str_pad($hour, 2, '0', STR_PAD_LEFT) . str_pad($minute, 3, ':0', STR_PAD_LEFT) . str_pad($second, 3, ':0', STR_PAD_LEFT);
				$format	.= 'H:i:s';
			}

			$is_localtime	=	$result['is_localtime'];

			if ($is_localtime)
			{
				
			}
		}

		return null;
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
	 *	@return new Twister\Date instance or false on failure
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public static function __check(...$params)
	{
		return self::normalize(...$params) !== false;
	}


	public static function check(...$params)					//	dirty check
	{
		
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
	 *	@return new Twister\Date instance or false on failure
	 *	@throws \InvalidArgumentException if an array or object without a
	 *					__toString method is passed as the first argument
	 */
	public static function __normalize(...$params)									//		I DON'T NEED THIS !?!?
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
	 *
	 * @param  string $start start date
	 * @param  string $end    end date
	 *
	 * @return true/false if the date is between a date range ...
	 */
	public function between($start, $end)
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
	public function hash($algo = 'md5', $format = null, $raw_output = false)
	{
		return hash($algo, parent::format($format === null ? static::$format : $format), $raw_output);
	}


	/**
	 * Gets a hash code of the internal string.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function getHash($algo = 'md5', $format = null, $raw_output = false)
	{
		return hash($algo, parent::format($format === null ? static::$format : $format), $raw_output);
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
		return $this->date === null || $this->date === '0000-00-00';
	}

	/**
	 *	Gets an MD5 hash code of the internal date. Return result can be raw binary or hex by default
	 *
	 *	@param  bool|null $raw_output return the raw binary bytes or hex values of the md5 hash
	 *	@return string
	 */
	public function md5($format = null, $raw_output = false)
	{
		return hash('md5', parent::format($format === null ? static::$format : $format), $raw_output);
	}

	/**
	 *
	 * @param  mixed   $offset The index to check
	 * @return boolean Whether or not the index exists
	 */
	public function offsetExists($offset)
	{
		switch($offset)
		{
			case 0:			return true;
			case 1:			return true;
			case 2:			return true;
			case 'year':	return true;
			case 'month':	return true;
			case 'day':		return true;
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
		switch ($name)
		{
			case 'year':	return $this->date->format('Y');
			case 'month':	return $this->date->format('m');
			case 'day':		return $this->date->format('d');
			case 0:			return $this->date->format('Y');
			case 1:			return $this->date->format('m');
			case 2:			return $this->date->format('d');
		}

		if (strlen($name) === 1)
			return $this->date->format($name);

		/*
			//	http://php.net/manual/en/function.date.php
			case 'd':	return ;				//	Day of the month, 2 digits with leading zeros										eg. 01 to 31
			case 'D':	return ;				//	A textual representation of a day, three letters									eg. Mon through Sun
			case 'j':	return ;				//	Day of the month without leading zeros												eg. 1 to 31
			case 'l':	return ;				//	A full textual representation of the day of the week								eg. Sunday through Saturday
			case 'N':	return ;				//	ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0			eg. 1 (for Monday) through 7 (for Sunday)
			case 'S':	return ;				//	English ordinal suffix for the day of the month, 2 characters						eg.  	st, nd, rd or th. Works well with j
			case 'w':	return ;				//	Numeric representation of the day of the week										eg. 0 (for Sunday) through 6 (for Saturday)
			case 'z':	return ;				//	 	The day of the year (starting from 0)											eg. 0 through 365
			case 'W':	return ;				//	ISO-8601 week number of year, weeks starting on Monday								eg. Example: 42 (the 42nd week in the year)
			case 'F':	return ;				//	A full textual representation of a month, such as January or March					eg. January through December
			case 'm':	return ;				//	Numeric representation of a month, with leading zeros								eg. 01 through 12
			case 'M':	return ;				//	A short textual representation of a month, three letters							eg. Jan through Dec
			case 'n':	return ;				//	Numeric representation of a month, without leading zeros							eg. 1 through 12
			case 't':	return ;				//	Number of days in the given month													eg. 28 through 31
			case 'L':	return ;				//	Whether it's a leap year															eg. 1 if it is a leap year, 0 otherwise.
			case 'o':	return ;				//	ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)			eg. Examples: 1999 or 2003
			case 'Y':	return ;				//	A full numeric representation of a year, 4 digits									eg. Examples: 1999 or 2003
			case 'y':	return ;				//	A two digit representation of a year												eg. Examples: 99 or 03
			case 'U':	return ;				//	Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)							eg. See also time()
		*/

		if ( ! ctype_lower($name))
			$name = strtolower($name);

		//	@link	http://www.tutorialspoint.com/mysql/mysql-date-time-functions.htm
		//	@link	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html

		switch ($name)
		{
			case 'dayname':			return $this->date->format('l');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayname			MySQL: Returns the name of the weekday for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'dayofweek':		return $this->date->format('w') + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofweek			MySQL: 1 = Sunday, 2 = Monday, …, 7 = Saturday		'w' = 0 (for Sunday) through 6 (for Saturday)
			case 'dayofmonth':		return $this->date->format('j');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofmonth		MySQL: Returns the day of the month for date, in the range 1 to 31, or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.
			case 'dayofyear':		return $this->date->format('z') + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofyear			MySQL: Returns the day of the year for date, in the range 1 to 366.
			case 'monthname':		return $this->date->format('F');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_monthname			MySQL: Returns the full name of the month for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'timestamp':		return $this->date->format('U');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_timestamp			MySQL: With a single argument, this function returns the date or datetime expression expr as a datetime value. With two arguments, it adds the time expression expr2 to the date or datetime expression expr1 and returns the result as a datetime value.
			case 'unix_timestamp':	return $this->date->format('U');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_unix-timestamp	MySQL: If called with no argument, returns a Unix timestamp (seconds since '1970-01-01 00:00:00' UTC).
			case 'to_days':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_to-days			MySQL: Given a date date, returns a day number (the number of days since year 0).
			case 'utc_date':		return $this->date->format('Y-m-d');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-date			MySQL: Returns the current UTC date as a value in 'YYYY-MM-DD' or YYYYMMDD format, depending on whether the function is used in a string or numeric context.
			case 'utc_time':		return $this->date->format('H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-time			MySQL: Returns the current UTC time as a value in 'HH:MM:SS'
			case 'utc_timestamp':	return $this->date->format('Y-m-d H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-timestamp	MySQL: Returns the current UTC date and time as a value in 'YYYY-MM-DD HH:MM:SS' or YYYYMMDDHHMMSS format, depending on whether the function is used in a string or numeric context.
			case 'quarter':			return $this->date->format('m') / 4 + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_quarter		MySQL: Returns the quarter of the year for date, in the range 1 to 4.
			case 'week':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_week				MySQL: This function returns the week number for date. The two-argument form of WEEK() enables you to specify whether the week starts on Sunday or Monday and whether the return value should be in the range from 0 to 53 or from 1 to 53. If the mode argument is omitted, the value of the default_week_format system variable is used. See Section 5.1.5, “Server System Variables”.
			case 'weekday':			return $this->date->format('N') - 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekday			MySQL: Returns the weekday index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
			case 'weekofyear':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekofyear		MySQL: Returns the calendar week of the date as a number in the range from 1 to 53. WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).
			case 'yearweek':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_yearweek			MySQL: Returns year and week for a date. The year in the result may be different from the year in the date argument for the first and the last week of the year.
			case 'date':			return $this->date->format('Y-m-d');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_date				MySQL: Extracts the date part of the date or datetime expression expr.

				throw new \InvalidArgumentException('TODO: Property offsetGet["' . $name . '"] not implemented yet');

			//	strtolower($name) versions
			case 'year':		return $this->date->format('Y');
			case 'month':		return $this->date->format('m');
			case 'day':			return $this->date->format('d');

			case 'hour':		return $this->date->format('G');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour				MySQL: Returns the hour for time. The range of the return value is 0 to 23 for time-of-day values. However, the range of TIME values actually is much larger, so HOUR can return values greater than 23.
			case 'minute':		return $this->date->format('i');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_minute			MySQL: Returns the minute for time, in the range 0 to 59.
			case 'second':		return $this->date->format('s');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour				MySQL: Returns the second for time, in the range 0 to 59.
		}

		throw new \InvalidArgumentException('Property offsetGet["' . $name . '"] does not exist!');
	}

	/**
	 *	Implements part of the ArrayAccess interface
	 *
	 *	@param  mixed      $offset The index of the character
	 *	@param  mixed      $value  Value to set
	 *	@throws \Exception When called
	 */
	public function offsetSet($offset, $value)
	{
		throw new \Exception('Cannot set array indexes!');
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
		throw new \Exception('Cannot unset array indexes!');
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
		return (int) ceil($this->month / 3);
	}

	/**
	 * Gets a SHA1 (160-bit) hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA1 hash
	 * @return string
	 */
	public function sha1($format = null, $raw_output = false)
	{
		return hash('sha1', parent::format($format === null ? static::$format : $format), $raw_output);
	}

	/**
	 * Gets a SHA-256 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-256 hash
	 * @return string
	 */
	public function sha256($format = null, $raw_output = false)
	{
		return hash('sha256', parent::format($format === null ? static::$format : $format), $raw_output);
	}

	/**
	 * Gets a SHA-384 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-384 hash
	 * @return string
	 */
	public function sha384($format = null, $raw_output = false)
	{
		return hash('sha384', parent::format($format === null ? static::$format : $format), $raw_output);
	}

	/**
	 * Gets a SHA-512 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-512 hash
	 * @return string
	 */
	public function sha512($format = null, $raw_output = false)
	{
		return hash('sha512', parent::format($format === null ? static::$format : $format), $raw_output);
	}


	/**
	 *
	 * @return array
	 */
	public function toArray($format = 'Y-m-d H:i:s e')
	{
		return array_merge(getdate($this->getTimestamp()), date_parse(parent::format($format)));
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


	/**************************************************************************\
	 ***                         GETTERS AND SETTERS                        ***
	\**************************************************************************/


	function __get($name)
	{
		static $formats	=	[	'year'				=>	'Y',
								'yearIso'			=>	'o',
								'month'				=>	'n',
								'day'				=>	'j',
								'hour'				=>	'G',
								'minute'			=>	'i',
								'second'			=>	's',

								'micro'				=>	'u',
								'microsecond'		=>	'u',
								'microseconds'		=>	'u',
							//	case 'microsecond':		return parent::format('u');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Microseconds. Note that date() will always generate 000000 since it takes an integer parameter, whereas DateTime::format() does support microseconds if DateTime was created with microseconds.

								'dayOfWeek'			=>	'w',
								'dayofweek'			=>	'w',	//	MySQL + 1 ???
								'day_of_week'		=>	'w',	//	MySQL + 1 ???
							//	case 'dayofweek':		return parent::format('w') + 1;			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofweek			MySQL: 1 = Sunday, 2 = Monday, …, 7 = Saturday		'w' = 0 (for Sunday) through 6 (for Saturday)

								'dayOfYear'			=>	'z',
								'dayofyear'			=>	'z',	//	MySQL + 1 ???
								'day_of_year'		=>	'z',	//	MySQL + 1 ???
							//	case 'dayofyear':		return parent::format('z') + 1;			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofyear			MySQL: Returns the day of the year for date, in the range 1 to 366.

								'weekOfYear'		=>	'W',
								'weekofyear'		=>	'W',
								'week_of_year'		=>	'W',
							//	case 'weekofyear':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekofyear		MySQL: Returns the calendar week of the date as a number in the range from 1 to 53. WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).

								'daysInMonth'		=>	't',
								'daysinmonth'		=>	't',
								'days_in_month'		=>	't',
							//	case 'daysinmonth':		return parent::format('t');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Number of days in the given month

								'monthName'			=>	'F',
								'monthname'			=>	'F',
								'month_name'		=>	'F',
							//	case 'monthname':		return parent::format('F');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_monthname			MySQL: Returns the full name of the month for date. The language used for the name is controlled by the value of the lc_time_names system variable

								'dayOfMonth'		=>	'j',
								'dayofmonth'		=>	'j',
								'day_of_month'		=>	'j',
							//	case 'dayofmonth':		return parent::format('j');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofmonth		MySQL: Returns the day of the month for date, in the range 1 to 31, or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.

								'timestamp'			=>	'U',
								'TIMESTAMP'			=>	'U',
							//	case 'timestamp':		return parent::format('Y-m-d H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_timestamp			MySQL: With a single argument, this function returns the date or datetime expression expr as a datetime value. With two arguments, it adds the time expression expr2 to the date or datetime expression expr1 and returns the result as a datetime value.

								'UNIX_TIMESTAMP'	=>	'U',
								'unix_timestamp'	=>	'U',
								'unixTimestamp'		=>	'U',
							//	case 'unix_timestamp':	return parent::format('U');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_unix-timestamp	MySQL: If called with no argument, returns a Unix timestamp (seconds since '1970-01-01 00:00:00' UTC).

								'date'				=>	'Y-m-d',
							//	case 'date':			return parent::format('Y-m-d');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_date				MySQL: Extracts the date part of the date or datetime expression expr.

								'dayname'			=>	'l',
							//	case 'dayname':			return parent::format('l');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayname			MySQL: Returns the name of the weekday for date. The language used for the name is controlled by the value of the lc_time_names system variable

								'dayname'			=>	'Y-m-t',
							//	case 'last_day':		return parent::format('Y-m-t');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_last-day			MySQL: eg. '2003-02-28' Takes a date or datetime value and returns the corresponding value for the last day of the month. Returns NULL if the argument is invalid.

								'isLeapYear'		=>	'Y-m-t',
								'isleapyear'		=>	'Y-m-t',
								'is_leap_year'		=>	'Y-m-t',
							//	case 'isleapyear':		return parent::format('L');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Whether it's a leap year

								'daySuffix'			=>	'S',
								'suffix'			=>	'S',									//	the term `suffix` is used in some classes
								'ordinal'			=>	'S',
							//	case 'ordinal':			return parent::format('S');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								English ordinal suffix for the day of the month, 2 characters

								//	Also in MySQL! We need to test this!
								'week'				=>	'W',
							//	case 'week':			return parent::format('W');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								ISO-8601 week number of year, weeks starting on Monday

								//	@aliases
								'TimeZone'			=>	'P',
								'timeZone'			=>	'P',
								'timezone'			=>	'P',
								'time_zone'			=>	'P',
								'tz'				=>	'P',
							//	case 'timezone':		return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)
							//	case 'time_zone':		return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)
							//	case 'tz':				return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)

								//	@link	http://php.net/manual/en/function.date.php
								'd'					=>	'd',
								'D'					=>	'D',
								'j'					=>	'j',
								'l'					=>	'l',
								'N'					=>	'N',
								'S'					=>	'S',
								'w'					=>	'w',
								'z'					=>	'z',
								'W'					=>	'W',
								'F'					=>	'F',
								'm'					=>	'm',
								'M'					=>	'M',
								'n'					=>	'n',
								't'					=>	't',
								'L'					=>	'L',
								'o'					=>	'o',
								'Y'					=>	'Y',
								'y'					=>	'y',
								'a'					=>	'a',
								'A'					=>	'A',
								'B'					=>	'B',
								'g'					=>	'g',
								'G'					=>	'G',
								'h'					=>	'h',
								'H'					=>	'H',
								'i'					=>	'i',
								's'					=>	's',
								'u'					=>	'u',
								'v'					=>	'v',
								'e'					=>	'e',
								'I'					=>	'I',
								'O'					=>	'O',
								'P'					=>	'P',
								'T'					=>	'T',
								'Z'					=>	'Z',
								'c'					=>	'c',
								'r'					=>	'r',
								'U'					=>	'U',
							];

		if (isset($formats[$name]))
			return $this->format($formats[$name]);

	//	if (strlen($name) === 1)
	//		return parent::format($name);

		/*
			//	http://php.net/manual/en/function.date.php
			case 'd':	return ;				//	Day of the month, 2 digits with leading zeros										eg. 01 to 31
			case 'D':	return ;				//	A textual representation of a day, three letters									eg. Mon through Sun
			case 'j':	return ;				//	Day of the month without leading zeros												eg. 1 to 31
			case 'l':	return ;				//	A full textual representation of the day of the week								eg. Sunday through Saturday
			case 'N':	return ;				//	ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0			eg. 1 (for Monday) through 7 (for Sunday)
			case 'S':	return ;				//	English ordinal suffix for the day of the month, 2 characters						eg.	st, nd, rd or th. Works well with j
			case 'w':	return ;				//	Numeric representation of the day of the week										eg. 0 (for Sunday) through 6 (for Saturday)
			case 'z':	return ;				//	The day of the year (starting from 0)												eg. 0 through 365
			case 'W':	return ;				//	ISO-8601 week number of year, weeks starting on Monday								eg. Example: 42 (the 42nd week in the year)
			case 'F':	return ;				//	A full textual representation of a month, such as January or March					eg. January through December
			case 'm':	return ;				//	Numeric representation of a month, with leading zeros								eg. 01 through 12
			case 'M':	return ;				//	A short textual representation of a month, three letters							eg. Jan through Dec
			case 'n':	return ;				//	Numeric representation of a month, without leading zeros							eg. 1 through 12
			case 't':	return ;				//	Number of days in the given month													eg. 28 through 31
			case 'L':	return ;				//	Whether it's a leap year															eg. 1 if it is a leap year, 0 otherwise.
			case 'o':	return ;				//	ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)			eg. Examples: 1999 or 2003
			case 'Y':	return ;				//	A full numeric representation of a year, 4 digits									eg. Examples: 1999 or 2003
			case 'y':	return ;				//	A two digit representation of a year												eg. Examples: 99 or 03
			case 'U':	return ;				//	Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)							eg. See also time()
		*/

		if ( ! ctype_lower($name))
			$name = strtolower($name);

		//	@link	http://www.tutorialspoint.com/mysql/mysql-date-time-functions.htm
		//	@link	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html

		switch ($name)
		{
			case 'dayname':			return parent::format('l');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayname			MySQL: Returns the name of the weekday for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'dayofweek':		return parent::format('w') + 1;			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofweek			MySQL: 1 = Sunday, 2 = Monday, …, 7 = Saturday		'w' = 0 (for Sunday) through 6 (for Saturday)
			case 'dayofmonth':		return parent::format('j');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofmonth		MySQL: Returns the day of the month for date, in the range 1 to 31, or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.
			case 'dayofyear':		return parent::format('z') + 1;			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofyear			MySQL: Returns the day of the year for date, in the range 1 to 366.
			case 'monthname':		return parent::format('F');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_monthname			MySQL: Returns the full name of the month for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'timestamp':		return parent::format('Y-m-d H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_timestamp			MySQL: With a single argument, this function returns the date or datetime expression expr as a datetime value. With two arguments, it adds the time expression expr2 to the date or datetime expression expr1 and returns the result as a datetime value.
			case 'unix_timestamp':	return parent::format('U');				//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_unix-timestamp	MySQL: If called with no argument, returns a Unix timestamp (seconds since '1970-01-01 00:00:00' UTC).
			case 'to_days':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_to-days			MySQL: Given a date date, returns a day number (the number of days since year 0).
			case 'quarter':			return (int) ceil(parent::format('m') / 3);		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_quarter	MySQL: Returns the quarter of the year for date, in the range 1 to 4.
			case 'week':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_week				MySQL: This function returns the week number for date. The two-argument form of WEEK() enables you to specify whether the week starts on Sunday or Monday and whether the return value should be in the range from 0 to 53 or from 1 to 53. If the mode argument is omitted, the value of the default_week_format system variable is used. See Section 5.1.5, “Server System Variables”.
			case 'weekday':			return parent::format('N') - 1;			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekday			MySQL: Returns the weekday index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
			case 'weekofyear':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekofyear		MySQL: Returns the calendar week of the date as a number in the range from 1 to 53. WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).
			case 'yearweek':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_yearweek			MySQL: Returns year and week for a date. The year in the result may be different from the year in the date argument for the first and the last week of the year.
			case 'date':			return parent::format('Y-m-d');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_date				MySQL: Extracts the date part of the date or datetime expression expr.

			case 'utc_date':												//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-date			MySQL: Returns the current UTC date as a value in 'YYYY-MM-DD' or YYYYMMDD format, depending on whether the function is used in a string or numeric context.
					//	@todo
					//	create a new UTC datetime/timestamp based on this
					//	if ($this->getTimezone() !== self::$utc) ...
					return parent::format('Y-m-d');

			case 'utc_time':												//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-time			MySQL: Returns the current UTC time as a value in 'HH:MM:SS'
					//	@todo
					return parent::format('H:i:s');

			case 'utc_timestamp':											//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-timestamp		MySQL: Returns the current UTC date and time as a value in 'YYYY-MM-DD HH:MM:SS' or YYYYMMDDHHMMSS format, depending on whether the function is used in a string or numeric context.
					//	@todo
					return parent::format('Y-m-d H:i:s');

			case 'weekOfMonth':
			case 'weekofmonth':
			case 'week_of_month':
					return (int) ceil(parent::format('d') / 7);				//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php

			case 'gmt':				return $this->getOffset() === 0;		//	my idea
			case 'utc':				return $this->getOffset() === 0;		//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'dst':				return $this->format('I') === '1';		//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'age':				return $this->diffInYears();			//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'offset':			return $this->getOffset();				//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'offsetHours':
					return $this->getOffset() / 60 / 60;					//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php

			case 'last_day':		return parent::format('Y-m-t');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_last-day			MySQL: eg. '2003-02-28' Takes a date or datetime value and returns the corresponding value for the last day of the month. Returns NULL if the argument is invalid.
			case 'daysinmonth':		return parent::format('t');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Number of days in the given month

			case 'isleapyear':		return parent::format('L');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Whether it's a leap year
			case 'microsecond':		return parent::format('u');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								Microseconds. Note that date() will always generate 000000 since it takes an integer parameter, whereas DateTime::format() does support microseconds if DateTime was created with microseconds.
			case 'ordinal':			return parent::format('S');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								English ordinal suffix for the day of the month, 2 characters

			//	Also in MySQL! We need to test this!
			case 'week':			return parent::format('W');				//	https://github.com/joomla-framework/date/blob/master/src/Date.php								ISO-8601 week number of year, weeks starting on Monday

			case 'tz':				// @alias 	timezone					//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'timezone':		return parent::getTimezone();			//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'timezoneName':	// @alias 	tzName						//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
			case 'tzName':			return $this->getTimezone()->getName();	//	Taken from: https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php

			case 'timezone':		return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)
			case 'time_zone':		return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)
			case 'tz':				return parent::format('P');				//	http://php.net/manual/en/function.date.php														Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)

				throw new \InvalidArgumentException('TODO: Property ->' . $name . ' not implemented yet');

			//	strtolower($name) versions
			case 'year':			return parent::format('Y');
			case 'month':			return parent::format('m');
			case 'day':				return parent::format('d');

			case 'hour':			return parent::format('G');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour				MySQL: Returns the hour for time. The range of the return value is 0 to 23 for time-of-day values. However, the range of TIME values actually is much larger, so HOUR can return values greater than 23.
			case 'minute':			return parent::format('i');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_minute			MySQL: Returns the minute for time, in the range 0 to 59.
			case 'second':			return parent::format('s');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour				MySQL: Returns the second for time, in the range 0 to 59.
		}

		throw new \InvalidArgumentException('Property ->' . $name . ' does not exist!');
	}


	/**
	 *	Check if an attribute exists on the object
	 *
	 *	@param string $name
	 *
	 *	@return bool
	 */
	public function __isset($name)
	{
		try {
			$this->__get($name);
		} catch (\InvalidArgumentException $e) {
			return false;
		}
		return true;
	}


	/**
	 *	Set a part of the Carbon object
	 *
	 *	@param string                   $name
	 *	@param string|int|\DateTimeZone $value
	 *
	 *	@throws \InvalidArgumentException
	 *
	 *	@returns value ??? do we need to do this?
	 *		PHP seems to return the $value automatically!
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'year':
			case 'month':
			case 'day':
				list($year, $month, $day) = explode('-', parent::format('Y-n-j'));
				$$name = $value;
				$this->setDate($year, $month, $day);
				break;
			case 'hour':
			case 'minute':
			case 'second':
				list($hour, $minute, $second) = explode('-', parent::format('G-i-s'));
				$$name = $value;
				$this->setTime($hour, $minute, $second);
				break;
			case 'timestamp':								//	doesn't work!	Never gets here! I believe `timezone` is an internal property?
				parent::setTimestamp($value);
				break;
			case 'timezone':
			case 'tz':
			case 'timeZone':
			case 'time_zone':
				$this->setTimezone($value);
				break;
			default:
				throw new \InvalidArgumentException("Property ->{$name} cannot be set!");
		}
	}

	/**
	 * Set the instance's year
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function year($value)
	{
		$this->year = $value;
		return $this;
	}
	/**
	 * Set the instance's month
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function month($value)
	{
		$this->month = $value;
		return $this;
	}
	/**
	 * Set the instance's day
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function day($value)
	{
		$this->day = $value;
		return $this;
	}
	/**
	 * Set the instance's hour
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function hour($value)
	{
		$this->hour = $value;
		return $this;
	}
	/**
	 * Set the instance's minute
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function minute($value)
	{
		$this->minute = $value;
		return $this;
	}
	/**
	 * Set the instance's second
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function second($value)
	{
		$this->second = $value;
		return $this;
	}

	/**
	 * Set the instance's timestamp
	 *
	 * @param int $value
	 *
	 * @return static
	 */
	public function timestamp($value)
	{
		return parent::setTimestamp($value);
	}


	/**
	 * Alias for setTimezone()
	 *
	 * @param \DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function timezone($value)
	{
		return $this->setTimezone($value);
	}
	/**
	 * Alias for setTimezone()
	 *
	 * @param \DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function tz($value)
	{
		return $this->setTimezone($value);
	}
	/**
	 * Set the instance's timezone from a string or object
	 *
	 * @param \DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function __setTimezone__Carbon($value)
	{
		return parent::setTimezone(static::safeCreateDateTimeZone($value));
	}

	/**
	 *	Sets the current date timezone
	 *
	 *	@link	http://php.net/manual/en/datetime.settimezone.php
	 *
	 *	@param  string|DateTimeZone $timezone
	 *	@return $this
	 */
	public function setTimezone($timezone = null)
	{
		return $this->setTimezone($timezone === null ? self::$utc : is_string($timezone) ? ($timezone === 'UTC' ? self::$utc : new \DateTimeZone($timezone)) : ($timezone instanceof \DateTimeZone ? $timezone : $timezone->getTimezone()));
	}


	/**
	 * Creates a DateTimeZone from a string, DateTimeZone or integer offset.
	 *
	 * @param \DateTimeZone|string|int|null $timezone
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return \DateTimeZone
	 */
	protected static function createDateTimeZone($timezone = null, $sanitized = true)
	{
		if ($timezone === null) {
			return self::$dtz;
		}
		if (is_string($timezone))
		{
			return new \DateTimeZone($sanitized ? $timezone : self::getSanitizedTimeZoneString());
		}
		if ($timezone instanceof \DateTimeZone) {
			return $timezone;
		}
		if (is_numeric($timezone))
		{
			$tzName = timezone_name_from_abbr(null, $timezone < 15 && $timezone > -13 ? $timezone * 3600 : $timezone, true);	//	Timezone range is -12:00 ~ +14:00
			if ($tzName === false)
			{
				throw new \InvalidArgumentException("Unknown or bad timezone ({$timezone})");
			}
			return new \DateTimeZone($tzName);
		}
		$tz = @timezone_open((string) $timezone);
		if ($tz === false) {
			throw new \InvalidArgumentException("Unknown or bad timezone ({$timezone})");
		}
		return $tz;
	}


	/**
	 * Creates a DateTimeZone from a string, DateTimeZone or integer offset.
	 *
	 * @param \DateTimeZone|string|int|null $timezone
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return \DateTimeZone
	 */
	protected static function getSanitizedTimeZoneString($timezone = null)	//	AKA sanitizeDateTimeZone / getSanitizedTimeZoneString / getValidDateTimeZoneString
	{
		static $timezones	=	null;
		static $abbr		=	null;
		static $dtz			=	null;

		if ($timezone === null)
		{
			return $dtz === null ? $dtz = date_default_timezone_get() : $dtz;
		}
		if (is_string($timezone))
		{
			if ($timezones === null) {
				$timezones = array_flip(\DateTimeZone::listIdentifiers());
			}
			if (isset($timezones[$timezone]))
				return $timezone;

			if ($abbr === null) {
				$timezones = \DateTimeZone::listAbbreviations();
			}
			if (isset($timezones[strtolower($timezone)]))
				return $timezone;

			if (preg_match('(?:GMT)?[-+][01]\d:?\d\d', $timezone) === 1)
				return $timezone;

			return $dtz === null ? $dtz = date_default_timezone_get() : $dtz;
		}
		if ($timezone instanceof \DateTimeZone)
		{
			return $timezone->getName();
		}
		if (is_numeric($timezone))
		{
			$tzName = timezone_name_from_abbr(null, $timezone, true);
			if ($tzName === false)
			{
				throw new \InvalidArgumentException("Unknown or bad timezone ({$timezone})");
			}
			$timezone = $tzName;
		}
		$tz = @timezone_open((string) $timezone);
		if ($tz === false) {
			throw new \InvalidArgumentException("Unknown or bad timezone ({$timezone})");
		}
		return $tz;
	}





	public function getYear()
	{
		return parent::format('Y');
	}

	public function getMonth()
	{
		return parent::format('m');
	}

	public function getDay()
	{
		return parent::format('d');
	}

	public function getHour()
	{
		return parent::format('H');
	}

	public function getMinute()
	{
		return parent::format('i');
	}

	public function getSecond()
	{
		return parent::format('s');
	}

	public function getHours()		//	@alias getHour()
	{
		return parent::format('H');
	}

	public function getMinutes()	//	@alias getMinute()
	{
		return parent::format('i');
	}

	public function getSeconds()	//	@alias getSecond()
	{
		return parent::format('s');
	}

	/**
	 *	'D' == A textual representation of a day, three letters									Mon through Sun
	 *	'l' == A full textual representation of the day of the week								Sunday through Saturday
	 *	'N' == ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)		1 (for Monday) through 7 (for Sunday)
	 *	'w' == Numeric representation of the day of the week									0 (for Sunday) through 6 (for Saturday)
	 */
	public function getDayOfWeek($format = 'D')
	{
		return parent::format($format);
	}


	//	CURDATE includes timezone, use UTC_DATE() for UTC date
	public static function curdate($tz = null)
	{
		return new static(date('Y-m-d'), $tz);
	}


	public static function utc_date()
	{
		return new static(gmdate('Y-m-d'), self::$utc);
	}


	public static function utcDate()
	{
		return new static(gmdate('Y-m-d'), self::$utc);
	}


    /**
     *	Create a Twister\DateTime instance from the current date and time.
     *
     *	@param \DateTimeZone|string|null $tz
     *
     *	@return static
     */
    public static function now($tz = null)
    {
        return new static(null, $tz);
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

\Twister\DateTime::$dtz = new \DateTimeZone(date_default_timezone_get());
\Twister\DateTime::$utc = new \DateTimeZone('UTC');
\Twister\DateTime::$p1d = new \DateInterval('P1D');
