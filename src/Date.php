<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php

use Countable;					//	http://php.net/manual/en/class.countable.php					Classes implementing Countable can be used with the count() function.
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

class Date implements Countable, IteratorAggregate, ArrayAccess
{
	protected $str = null;

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
	public function __construct($date = 'now')
	{
		if (is_string($date))
		{
			if (strlen($date) === 10) // 1234-67-90
			{
				if (preg_match('/\d\d\d\d-\d\d-\d\d/', $date) === 1)
				{
					
				}
			}
		}
		if ( ! is_string($date)) {
			if (is_array($date)) {
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
			} elseif (is_object($str) && !method_exists($str, '__toString')) {
				throw new InvalidArgumentException('Passed object must have a __toString method');
			}
		}

		$this->str = (string) $str;
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
			case 1:
				$date = $params[0];
				if (is_string($date))
				{
					if (preg_match('~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~', $date, $matches) === 1)
					{
						return checkdate($matches[2], $matches[3], $matches[1]) ? $date : false;	//	checkdate(month, day, year)
					}
					else
					{
						//	... can we match other date formats?
						//	http://php.net/manual/en/function.strtotime.php
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
		{
			return false;
		}

		if (checkdate($month, $day, $year))
		{
			return $year . '-' . ($month < 10 ? '0' : null) . $month . '-' . ($day < 10 ? '0' : null) . $day;
		}

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
	 * Returns the timestamp
	 *
	 * @return int The unix timestamp ... or number of days ???
	 */
	public function count()
	{
		return strlen($this->str);
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
	 * @return 
	 */
	public function delimit($delimiter)
	{
		$str = \preg_replace('~([^A-Z\b])([A-Z])~', '\1-\2', \trim($this->str));
		$str = \preg_replace('~[-_\s]+~', $delimiter, \strtolower($str));

		return new static($str);
	}

	/**
	 * Returns true if the string contains a date in the format 'YYYY-MM-DD'
	 * Alternative patterns:
	 *		'/^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 * 		'/^\d\d\d\d-\d\d-\d\d$/'
	 * 		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 * @return string[]|null Returns an array with 'year', 'month' and 'day'
	 *                       from a matching date in the format 'YYYY-MM-DD', or null on failure
	 */
	public function getDate($pattern = null)
	{
		$pattern = $pattern ?? '/^(?P<year>[12]\d\d\d)-(?P<month>0[1-9]|1[0-2])-(?P<day>0[1-9]|[1-2][0-9]|3[0-1])$/';
		return preg_match($pattern, $this->str, $matches) === false ? null : $matches;
	}

	/**
	 * Gets a hash code of the internal string.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function hash($algo = 'md5', $raw_output = false)
	{
		return new static(hash($algo, $this->str, $raw_output));
	}

	/**
	 * Gets a hash code of the internal string.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function getHash($algo = 'md5', $raw_output = false)
	{
		return new static(hash($algo, $this->str, $raw_output));
	}

	/**
	 *
	 * @return Human readable string format, eg. 24 October 1977
	 */
	public function humanize()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		return static::create(
					str_replace(['_id', '_'], ['', ' '], $this->str),
					$this->encoding)->trim()->upperCaseFirst();
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
	 *	Returns true if the string contains a date in the format 'YYYY-MM-DD' AND is a valid Gregorian date
	 *
	 *	All date patterns MUST have 3x (..)
	 *
	 *	Alternative patterns:
	 *		'/^(\d\d\d\d)-(\d\d)-(\d\d)$/'
	 *		'/^(\d{4})-(\d{2})-(\d{2}) [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 *		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 *	@link http://php.net/manual/en/function.checkdate.php
	 *
	 *	@return bool Whether or not $str contains a matching date in the format 'YYYY-MM-DD'
	 */
	public static function isDate($pattern = '~^([1-9]\d\d\d)[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$~')
	{
		return preg_match($pattern, $this->str, $matches) === 1 && checkdate($matches[2], $matches[3], $matches[1]);	//	checkdate(month, day, year)
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
		throw new Exception('');
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

	/*
	 * A str_shuffle() wrapper function.
	 *
	 * @return static Object with a shuffled $str
	 */
	public function shuffle()
	{
		return new static(str_shuffle($this->str));
	}

	/**
	 * Converts the string into an URL slug. This includes replacing non-ASCII
	 * characters with their closest ASCII equivalents, removing remaining
	 * non-ASCII and non-alphanumeric characters, and replacing whitespace with
	 * $replacement. The replacement defaults to a single dash, and the string
	 * is also converted to lowercase. The language of the source string can
	 * also be supplied for language-specific transliteration.
	 *
	 * @param  string $replacement The string used to replace whitespace
	 * @param  string $language    Language of the source string
	 * @return static Object whose $str has been converted to an URL slug
	 */
	public function slugify($replacement = '-', $language = 'en')
	{
		trigger_error('Function not implemented yet');

		$stringy = $this->toAscii($language);

		$stringy->str = str_replace('@', $replacement, $stringy);
		$quotedReplacement = preg_quote($replacement);
		$pattern = "/[^a-zA-Z\d\s-_$quotedReplacement]/u";
		$stringy->str = preg_replace($pattern, '', $stringy);

		return $stringy->toLowerCase()->delimit($replacement)
					   ->removeLeft($replacement)->removeRight($replacement);
	}

	/**
	 * Returns true if the string begins with $substring, false otherwise.
	 * By default, the comparison is case-sensitive,
	 * but can be made insensitive by setting $caseSensitive to false.
	 *
	 * @param  string $substring     The substring to look for
	 * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool   Whether or not $str starts with $substring
	 */
	public function startsWith($substring, $caseSensitive = true)
	{
		return ($caseSensitive ? strpos($this->str, $substring) : stripos($this->str, $substring)) === 0;
	}

	/**
	 * Returns true if the string begins with any of $substrings, false
	 * otherwise. By default the comparison is case-sensitive, but can be made
	 * insensitive by setting $caseSensitive to false.
	 *
	 * @param  string[] $substrings    Substrings to look for
	 * @param  bool     $caseSensitive Whether or not to enforce
	 *                                 case-sensitivity
	 * @return bool     Whether or not $str starts with $substring
	 */
	public function startsWithAny($substrings, $caseSensitive = true)
	{
		trigger_error('Function not implemented yet');

		if (empty($substrings)) {
			return false;
		}

		foreach ($substrings as $substring) {
			if ($this->startsWith($substring, $caseSensitive)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the substring beginning at $start, and up to, but not including
	 * the index specified by $end. If $end is omitted, the function extracts
	 * the remaining string. If $end is negative, it is computed from the end
	 * of the string.
	 *
	 * @param  int    $start Initial index from which to begin extraction
	 * @param  int    $end   Optional index at which to end extraction
	 * @return static Object with its $str being the extracted substring
	 */
	public function slice($start, $end = null)
	{
		trigger_error('Function not implemented yet');

		if ($end === null) {
			$length = $this->length();
		} elseif ($end >= 0 && $end <= $start) {
			return static::create('', $this->encoding);
		} elseif ($end < 0) {
			$length = $this->length() + $end - $start;
		} else {
			$length = $end - $start;
		}

		return $this->substr($start, $length);
	}

	/**
	 * Splits the string with the provided regular expression, returning an
	 * array of Stringy objects. An optional integer $limit will truncate the
	 * results.
	 *
	 * @param  string   $pattern The regex with which to split the string
	 * @param  int      $limit   Optional maximum number of results to return
	 * @return static[] An array of Stringy objects
	 */
	public function split($pattern, $limit = null)
	{
		trigger_error('Function not implemented yet');

		if ($limit === 0) {
			return [];
		}

		// mb_split errors when supplied an empty pattern in < PHP 5.4.13
		// and HHVM < 3.8
		if ($pattern === '') {
			return [static::create($this->str, $this->encoding)];
		}

		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		// mb_split returns the remaining unsplit string in the last index when
		// supplying a limit
		$limit = ($limit > 0) ? $limit += 1 : -1;

		static $functionExists;
		if ($functionExists === null) {
			$functionExists = function_exists('\mb_split');
		}

		if ($functionExists) {
			$array = \mb_split($pattern, $this->str, $limit);
		} else if ($this->supportsEncoding()) {
			$array = \preg_split("/$pattern/", $this->str, $limit);
		}

		$this->regexEncoding($regexEncoding);

		if ($limit > 0 && count($array) === $limit) {
			array_pop($array);
		}

		for ($i = 0; $i < count($array); $i++) {
			$array[$i] = static::create($array[$i], $this->encoding);
		}

		return $array;
	}

	/**
	 * Strip all whitespace characters. This includes tabs and newline
	 * characters, as well as multibyte whitespace such as the thin space
	 * and ideographic space.
	 *
	 * @return static Object with whitespace stripped
	 */
	public function stripWhitespace()
	{
		trigger_error('Function not implemented yet');

		return $this->regexReplace('[[:space:]]+', '');
	}

	/**
	 * Returns the length of the string. strlen() wrapper.
	 *
	 * @return int The number of characters in $str given the encoding
	 */
	public function strlen()
	{
		return strlen($this->str);
	}

	/**
	 * Returns the substring beginning at $start with the specified $length.
	 * It differs from the mb_substr() function in that providing a $length of
	 * null will return the rest of the string, rather than an empty string.
	 *
	 * @param  int    $start  Position of the first character to use
	 * @param  int    $length Maximum number of characters used
	 * @return static Object with its $str being the substring
	 */
	public function substr($start, $length = null)
	{
		return new static($length === null ? substr($this->str, $start) : substr($this->str, $start, $length));
	}

	/**
	 * Surrounds $str with the given substring.
	 *
	 * @param  string $substring The substring to add to both sides
	 * @return static Object whose $str had the substring both prepended and appended
	 */
	public function surround($substring)
	{
		return new static($substring . $this->str . $substring);
	}

	/**
	 * Returns a case swapped version of the string.
	 *
	 * @return static Object whose $str has each character's case swapped
	 */
	public function swapCase()
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->str, $this->encoding);
		$encoding = $stringy->encoding;

		$stringy->str = preg_replace_callback(
			'/[\S]/u',
			function ($match) use ($encoding) {
				if ($match[0] == \mb_strtoupper($match[0], $encoding)) {
					return \mb_strtolower($match[0], $encoding);
				}

				return \mb_strtoupper($match[0], $encoding);
			},
			$stringy->str
		);

		return $stringy;
	}

	/**
	 * Returns a string with smart quotes, ellipsis characters, and dashes from
	 * Windows-1252 (commonly used in Word documents) replaced by their ASCII
	 * equivalents.
	 *
	 * @return static Object whose $str has those characters removed
	 */
	public function tidy()
	{
		trigger_error('Function not implemented yet');

		$str = preg_replace([
			'/\x{2026}/u',
			'/[\x{201C}\x{201D}]/u',
			'/[\x{2018}\x{2019}]/u',
			'/[\x{2013}\x{2014}]/u',
		], [
			'...',
			'"',
			"'",
			'-',
		], $this->str);

		return static::create($str, $this->encoding);
	}

	/**
	 * Returns a trimmed string with the first letter of each word capitalized.
	 * Also accepts an array, $ignore, allowing you to list words not to be capitalized.
	 *
	 * @param  array  $ignore An array of words not to capitalize
	 * @return static Object with a titleized $str
	 */
	public function titleize($ignore = null)
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->trim(), $this->encoding);
		$encoding = $this->encoding;

		$stringy->str = preg_replace_callback(
			'/([\S]+)/u',
			function ($match) use ($encoding, $ignore) {
				if ($ignore && in_array($match[0], $ignore)) {
					return $match[0];
				}

				$stringy = new Stringy($match[0], $encoding);

				return (string) $stringy->toLowerCase()->upperCaseFirst();
			},
			$stringy->str
		);

		return $stringy;
	}

	/**
	 * Returns an ASCII version of the string. A set of non-ASCII characters are
	 * replaced with their closest ASCII counterparts, and the rest are removed
	 * by default. The language or locale of the source string can be supplied
	 * for language-specific transliteration in any of the following formats:
	 * en, en_GB, or en-GB. For example, passing "de" results in "äöü" mapping
	 * to "aeoeue" rather than "aou" as in other languages.
	 *
	 * @param  string $language          Language of the source string
	 * @param  bool   $removeUnsupported Whether or not to remove the
	 *                                    unsupported characters
	 * @return static Object whose $str contains only ASCII characters
	 */
	public function toAscii($language = 'en', $removeUnsupported = true)
	{
		trigger_error('Function not implemented yet');

		$str = $this->str;

		$langSpecific = $this->langSpecificCharsArray($language);
		if (!empty($langSpecific)) {
			$str = str_replace($langSpecific[0], $langSpecific[1], $str);
		}

		foreach ($this->charsArray() as $key => $value) {
			$str = str_replace($value, $key, $str);
		}

		if ($removeUnsupported) {
			$str = preg_replace('/[^\x20-\x7E]/u', '', $str);
		}

		return static::create($str, $this->encoding);
	}

	/**
	 * Returns a base64 encoded string object.
	 *
	 * @return static base64 encoded string object
	 */
	public function toBase64()
	{
		return new static(base64_encode($this->str));
	}

	/**
	 *  Alias of toBoolean()
	 *
	 *  @return bool A boolean value for the string
	 */
	public function toBool()
	{
		return $this->toBoolean();
	}

	/**
	 * Returns a boolean representation of the given logical string value.
	 * For example, 'true', '1', 'on' and 'yes' will return true. 'false', '0',
	 * 'off', and 'no' will return false. In all instances, case is ignored.
	 * For other numeric strings, their sign will determine the return value.
	 * In addition, blank strings consisting of only whitespace will return false.
	 * For all other strings, the return value is a result of a boolean cast.
	 *
	 * @return bool A boolean value for the string
	 */
	public function toBoolean()
	{
		$key = ctype_lower($this->str) ? $this->str : strtolower($this->str);

		if (isset(self::$boolMap[$key]))
			return self::$boolMap[$key];

		if (is_numeric($this->str))
			return intval($this->str) > 0;

		return (bool) \str_replace(\str_split(" \t\n\r\0\x0B"), '', $this->str);
	}

	/**
	 * Converts all characters in the string to lowercase.
	 *
	 * @return static Object with all characters of $str being lowercase
	 */
	public function toLower()
	{
		return new static(\strtolower($this->str));
	}

	/**
	 * Converts all characters in the string to lowercase.
	 *
	 * @return static Object with all characters of $str being lowercase
	 */
	public function toLowerCase()
	{
		return new static(\strtolower($this->str));
	}

	/**
	 * Converts each tab in the string to some number of spaces, as defined by
	 * $tabLength. By default, each tab is converted to 4 consecutive spaces.
	 *
	 * @param  int    $tabLength Number of spaces to replace each tab with
	 * @return static Object whose $str has had tabs switched to spaces
	 */
	public function toSpaces($tabLength = 4)
	{
		return new static(\str_replace("\t", \str_repeat(' ', $tabLength), $this->str));
	}

	/**
	 * Converts each occurrence of some consecutive number of spaces, as
	 * defined by $tabLength, to a tab. By default, each 4 consecutive spaces
	 * are converted to a tab.
	 *
	 * @param  int    $tabLength Number of spaces to replace with a tab
	 * @return static Object whose $str has had spaces switched to tabs
	 */
	public function toTabs($tabLength = 4)
	{
		return new static(\str_replace(\str_repeat(' ', $tabLength), "\t", $this->str));
	}

	/**
	 * Converts the first character of each word in the string to uppercase.
	 *
	 * @return static Object with all characters of $str being title-cased
	 */
	public function toTitleCase()
	{
		return new static(\ucwords($this->str));
	//	return new static(\mb_convert_case($this->str, \MB_CASE_TITLE, 'UTF-8'));
	}

	/**
	 * Converts all characters in the string to uppercase.
	 *
	 * @return static Object with all characters of $str being uppercase
	 */
	public function toUpper()
	{
		return new static(\ctype_upper($this->str));
	}

	/**
	 * Converts all characters in the string to uppercase.
	 *
	 * @return static Object with all characters of $str being uppercase
	 */
	public function toUpperCase()
	{
		return new static(\ctype_upper($this->str));
	}

	/**
	 * Returns a string with whitespace removed from the start and end of the string.
	 * Supports the removal of unicode whitespace.
	 * Accepts an optional string of characters to strip instead of the defaults.
	 *
	 * @param  string $character_mask Optional string of characters to strip
	 * @return static Object with a trimmed $str
	 */
	public function trim(string $character_mask = " \t\n\r\0\x0B")
	{
		return new static(\trim($this->str, $character_mask));
	}

	/**
	 * Returns a string with whitespace removed from the start of the string.
	 * Supports the removal of unicode whitespace. Accepts an optional
	 * string of characters to strip instead of the defaults.
	 *
	 * @param  string $chars Optional string of characters to strip
	 * @return static Object with a trimmed $str
	 */
	public function trimLeft(string $character_mask = " \t\n\r\0\x0B")
	{
		return new static(\ltrim($this->str, $character_mask));
	}

	/**
	 * Returns a string with whitespace removed from the end of the string.
	 * Supports the removal of unicode whitespace. Accepts an optional
	 * string of characters to strip instead of the defaults.
	 *
	 * @param  string $chars Optional string of characters to strip
	 * @return static Object with a trimmed $str
	 */
	public function trimRight(string $character_mask = " \t\n\r\0\x0B")
	{
		return new static(\rtrim($this->str, $character_mask));
	}

	/**
	 * Truncates the string to a given length. If $substring is provided, and
	 * truncating occurs, the string is further truncated so that the substring
	 * may be appended without exceeding the desired length.
	 *
	 * @param  int    $length    Desired length of the truncated string
	 * @param  string $substring The substring to append if it can fit
	 * @return static Object with the resulting $str after truncating
	 */
	public function truncate($length, $substring = '')
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$stringy = static::create($this->str, $this->encoding);
		if ($length >= $stringy->length()) {
			return $stringy;
		}

		// Need to further trim the string so we can append the substring
		$substringLength = \mb_strlen($substring, $stringy->encoding);
		$length = $length - $substringLength;

		$truncated = \mb_substr($stringy->str, 0, $length, $stringy->encoding);
		$stringy->str = $truncated . $substring;

		return $stringy;
	}

	/**
	 * Converts the first character of the supplied string to upper case.
	 *
	 * @return static Object with the first character of $str being upper case
	 */
	public function ucFirst()
	{
		return new static(\ucfirst($this->str));
	}

	/**
	 * Returns a lowercase and trimmed string separated by underscores.
	 * Underscores are inserted before uppercase characters (with the exception
	 * of the first character of the string), and in place of spaces as well as dashes.
	 *
	 * @return static Object with an underscored $str
	 */
	public function underscored()	//	AKA snake_case
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		return $this->delimit('_');
	}

	/**
	 * Returns an UpperCamelCase version of the supplied string. It trims
	 * surrounding spaces, capitalizes letters following digits, spaces, dashes
	 * and underscores, and removes spaces, dashes, underscores.
	 *
	 * @return static Object with $str in UpperCamelCase
	 */
	public function upperCamelize()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		return $this->camelize()->upperCaseFirst();
	}

	/**
	 * Converts the first character of the supplied string to upper case.
	 *
	 * @return static Object with the first character of $str being upper case
	 */
	public function upperCaseFirst()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$first = \mb_substr($this->str, 0, 1, $this->encoding);
		$rest = \mb_substr($this->str, 1, $this->length() - 1, $this->encoding);

		$str = \mb_strtoupper($first, $this->encoding) . $rest;

		return static::create($str, $this->encoding);
	}

	/**
	 * Returns the replacements for the toAscii() method.
	 *
	 * @return array An array of replacements.
	 */
	protected function charsArray()
	{
		if (isset(self::$charsArray))
			return self::$charsArray;

		return self::$charsArray = [
			'0'     => ['°', '₀', '۰', '０'],
			'1'     => ['¹', '₁', '۱', '１'],
			'2'     => ['²', '₂', '۲', '２'],
			'3'     => ['³', '₃', '۳', '３'],
			'4'     => ['⁴', '₄', '۴', '٤', '４'],
			'5'     => ['⁵', '₅', '۵', '٥', '５'],
			'6'     => ['⁶', '₆', '۶', '٦', '６'],
			'7'     => ['⁷', '₇', '۷', '７'],
			'8'     => ['⁸', '₈', '۸', '８'],
			'9'     => ['⁹', '₉', '۹', '９'],
			'a'     => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ',
						'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å',
						'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ',
						'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά',
						'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ',
						'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä'],
			'b'     => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ'],
			'c'     => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
			'd'     => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ',
						'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ'],
			'e'     => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ',
						'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ',
						'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э',
						'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ', 'ｅ'],
			'f'     => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ'],
			'g'     => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ',
						'ｇ'],
			'h'     => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ'],
			'i'     => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į',
						'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ',
						'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ',
						'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი',
						'इ', 'ی', 'ｉ'],
			'j'     => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
			'k'     => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ',
						'ک', 'ｋ'],
			'l'     => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ',
						'ｌ'],
			'm'     => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ'],
			'n'     => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န',
						'ნ', 'ｎ'],
			'o'     => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ',
						'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő',
						'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό',
						'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ',
						'ö'],
			'p'     => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ'],
			'q'     => ['ყ', 'ｑ'],
			'r'     => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ'],
			's'     => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ',
						'ſ', 'ს', 'ｓ'],
			't'     => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ',
						'თ', 'ტ', 'ｔ'],
			'u'     => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ',
						'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ',
						'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ', 'ｕ',
						'ў', 'ü'],
			'v'     => ['в', 'ვ', 'ϐ', 'ｖ'],
			'w'     => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
			'x'     => ['χ', 'ξ', 'ｘ'],
			'y'     => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ',
						'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
			'z'     => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ'],
			'aa'    => ['ع', 'आ', 'آ'],
			'ae'    => ['æ', 'ǽ'],
			'ai'    => ['ऐ'],
			'ch'    => ['ч', 'ჩ', 'ჭ', 'چ'],
			'dj'    => ['ђ', 'đ'],
			'dz'    => ['џ', 'ძ'],
			'ei'    => ['ऍ'],
			'gh'    => ['غ', 'ღ'],
			'ii'    => ['ई'],
			'ij'    => ['ĳ'],
			'kh'    => ['х', 'خ', 'ხ'],
			'lj'    => ['љ'],
			'nj'    => ['њ'],
			'oe'    => ['œ', 'ؤ'],
			'oi'    => ['ऑ'],
			'oii'   => ['ऒ'],
			'ps'    => ['ψ'],
			'sh'    => ['ш', 'შ', 'ش'],
			'shch'  => ['щ'],
			'ss'    => ['ß'],
			'sx'    => ['ŝ'],
			'th'    => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
			'ts'    => ['ц', 'ც', 'წ'],
			'uu'    => ['ऊ'],
			'ya'    => ['я'],
			'yu'    => ['ю'],
			'zh'    => ['ж', 'ჟ', 'ژ'],
			'(c)'   => ['©'],
			'A'     => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ',
						'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą',
						'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ',
						'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ',
						'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
			'B'     => ['Б', 'Β', 'ब', 'Ｂ'],
			'C'     => ['Ç','Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
			'D'     => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ',
						'Ｄ'],
			'E'     => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ',
						'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ',
						'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э',
						'Є', 'Ə', 'Ｅ'],
			'F'     => ['Ф', 'Φ', 'Ｆ'],
			'G'     => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
			'H'     => ['Η', 'Ή', 'Ħ', 'Ｈ'],
			'I'     => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į',
						'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ',
						'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ',
						'Ｉ'],
			'J'     => ['Ｊ'],
			'K'     => ['К', 'Κ', 'Ｋ'],
			'L'     => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
			'M'     => ['М', 'Μ', 'Ｍ'],
			'N'     => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
			'O'     => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ',
						'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő',
						'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ',
						'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
			'P'     => ['П', 'Π', 'Ｐ'],
			'Q'     => ['Ｑ'],
			'R'     => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
			'S'     => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
			'T'     => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
			'U'     => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ',
						'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ',
						'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
			'V'     => ['В', 'Ｖ'],
			'W'     => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
			'X'     => ['Χ', 'Ξ', 'Ｘ'],
			'Y'     => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ',
						'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
			'Z'     => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
			'AE'    => ['Æ', 'Ǽ'],
			'Ch'    => ['Ч'],
			'Dj'    => ['Ђ'],
			'Dz'    => ['Џ'],
			'Gx'    => ['Ĝ'],
			'Hx'    => ['Ĥ'],
			'Ij'    => ['Ĳ'],
			'Jx'    => ['Ĵ'],
			'Kh'    => ['Х'],
			'Lj'    => ['Љ'],
			'Nj'    => ['Њ'],
			'Oe'    => ['Œ'],
			'Ps'    => ['Ψ'],
			'Sh'    => ['Ш'],
			'Shch'  => ['Щ'],
			'Ss'    => ['ẞ'],
			'Th'    => ['Þ'],
			'Ts'    => ['Ц'],
			'Ya'    => ['Я'],
			'Yu'    => ['Ю'],
			'Zh'    => ['Ж'],
			' '     => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81",
						"\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84",
						"\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87",
						"\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A",
						"\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80",
						"\xEF\xBE\xA0"],
		];
	}

	/**
	 * Returns language-specific replacements for the toAscii() method.
	 * For example, German will map 'ä' to 'ae', while other languages
	 * will simply return 'a'.
	 *
	 * @param  string $language Language of the source string
	 * @return array  An array of replacements.
	 */
	protected static function langSpecificCharsArray($language = 'en')
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$split = preg_split('/[-_]/', $language);
		$language = strtolower($split[0]);

		static $charsArray = [];
		if (isset($charsArray[$language])) {
			return $charsArray[$language];
		}

		$languageSpecific = [
			'de' => [
				['ä',  'ö',  'ü',  'Ä',  'Ö',  'Ü' ],
				['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
			],
			'bg' => [
				['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
				['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y']
			]
		];

		if (isset($languageSpecific[$language])) {
			$charsArray[$language] = $languageSpecific[$language];
		} else {
			$charsArray[$language] = [];
		}

		return $charsArray[$language];
	}

	/**
	 * Adds the specified amount of left and right padding to the given string.
	 * The default character used is a space.
	 *
	 * @param  int    $left   Length of left padding
	 * @param  int    $right  Length of right padding
	 * @param  string $padStr String used to pad
	 * @return static String with padding applied
	 */
	protected function applyPadding($left = 0, $right = 0, $padStr = ' ')
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$stringy = static::create($this->str, $this->encoding);
		$length = \mb_strlen($padStr, $stringy->encoding);

		$strLength = $stringy->length();
		$paddedLength = $strLength + $left + $right;

		if (!$length || $paddedLength <= $strLength) {
			return $stringy;
		}

		$leftPadding = \mb_substr(str_repeat($padStr, ceil($left / $length)), 0, $left, $stringy->encoding);
		$rightPadding = \mb_substr(str_repeat($padStr, ceil($right / $length)), 0, $right, $stringy->encoding);

		$stringy->str = $leftPadding . $stringy->str . $rightPadding;

		return $stringy;
	}

	/**
	 * Returns true if $str matches the supplied pattern, false otherwise.
	 *
	 * @param  string $pattern Regex pattern to match against
	 * @return bool   Whether or not $str matches the pattern
	 */
	protected function matchesPattern($pattern)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$match = \mb_ereg_match($pattern, $this->str);
		$this->regexEncoding($regexEncoding);

		return $match;
	}

	/**
	 * Alias for mb_ereg_replace with a fallback to preg_replace if the
	 * mbstring module is not installed.
	 */
	protected function eregReplace($pattern, $replacement, $string, $option = 'msr')
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		static $functionExists;
		if ($functionExists === null) {
			$functionExists = function_exists('\mb_split');
		}

		if ($functionExists) {
			return \mb_ereg_replace($pattern, $replacement, $string, $option);
		} else if ($this->supportsEncoding()) {
			$option = str_replace('r', '', $option);
			return \preg_replace("/$pattern/u$option", $replacement, $string);
		}
	}

	/**
	 * Alias for mb_regex_encoding which default to a noop if the mbstring
	 * module is not installed.
	 */
	protected function regexEncoding()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		static $functionExists;

		if ($functionExists === null) {
			$functionExists = function_exists('\mb_regex_encoding');
		}

		if ($functionExists) {
			$args = func_get_args();
			return call_user_func_array('\mb_regex_encoding', $args);
		}
	}

	/**
	 * Returns a string with whitespace removed from the start and end of the string.
	 * Supports the removal of unicode whitespace.
	 * Accepts an optional string of characters to strip instead of the defaults.
	 *
	 * @param  string $character_mask Optional string of characters to strip
	 * @return static Object with a trimmed $str
	 */
	public function rtrim(string $character_mask = " \t\n\r\0\x0B")
	{
		return new static(\rtrim($this->str, $character_mask));
	}


	function __get($name)
	{
		switch ($name)
		{
			case 'length':		return \strlen($this->str);

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

	static function curdate($format = 'Y-m-d')
	{
		return new static(date($format));
	}

}
