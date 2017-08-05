<?php

namespace Twister;

use ArrayAccess;

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

class DateTime implements Countable, IteratorAggregate, ArrayAccess
{
	/**
	 * An instance's string.
	 *
	 * @var string
	 */
	protected $str;

	/**
	 *  Modified by Trevor Herselman
	 *  Used when mapping a string to boolean with toBoolean()
	 *
	 *  @var string
	 */
	private static $boolMap = [
				'true'  => true,
				'1'     => true,
				'on'    => true,
				'yes'   => true,
				'y'     => true,	//	added by Trevor Herselman
				'false' => false,
				'0'     => false,
				'off'   => false,
				'no'    => false,
				'n'     => false	//	added by Trevor Herselman
			];

	private static $charsArray;

	/**
	 *  Idea taken from CakePHP: https://api.cakephp.org/2.3/class-CakeRequest.html#$_detectors
	 *                           https://api.cakephp.org/2.3/source-class-CakeRequest.html#92-117
	 *
	 *	@var mixed[] Array of built in detectors used with is($type) or is$type(), can be modified with addDetector().
	 */
	private static $_detectors = null;	//	['dotcom' => function(&$uri){return substr($uri->host, -4) === '.com'}] eg. isDotCom() || is('DotCom') || is('.com') (the '.com' version cannot be tested with is.com()!)
										//	['domain' => function(&$uri, $domain){return substr($uri->host, -strlen($uri->host)) === $domain}]	//	isDomain('example.com') || isDomain('.com') || is('domain', '.com')

	/**
	 *  Array of supported hash algoithms, initialized to hash_algos() on first use!
	 *      Used when generating dynamic hash properties eg. $str->md5
	 *      Some algorithms cannot be used such as `gost-crypto`, `tiger128,3` etc.
	 *			because of invalid characters in the name.
	 *  http://php.net/manual/en/function.hash-algos.php
	 */
	private static $hashAlgos = null;

	/**
	 * Initializes a Stringy object and assigns both str and encoding properties
	 * the supplied values. $str is cast to a string prior to assignment, and if
	 * $encoding is not specified, it defaults to mb_internal_encoding(). Throws
	 * an InvalidArgumentException if the first argument is an array or object
	 * without a __toString method.
	 *
	 * @param  mixed  $str      Value to modify, after being cast to string
	 * @param  string $encoding The character encoding
	 * @throws \InvalidArgumentException if an array or object without a
	 *         __toString method is passed as the first argument
	 */
	public function __construct($str = '')
	{
		if ( ! is_string($str)) {
			if (is_array($str)) {
				throw new InvalidArgumentException('Passed value cannot be an array');
			} elseif (is_object($str) && !method_exists($str, '__toString')) {
				throw new InvalidArgumentException('Passed object must have a __toString method');
			}
		}

        $this->str = (string) $str;
	}

	/**
	 * Function added by Trevor Herselman
	 * Concats a string and returns the new one.
	 * Actually, it is the same as the dot operator.
	 * @param string $args,... Variable number of strings to concatenate. Returns a single Mb object.
	 * @return static A Stringy object
	 */
	public function concat()
	{
		$args = func_get_args();
		$str = $this->str;
		foreach ($args as $arg)
		{
			$str .= (string) $arg;
		}
		return new static($str);
	}

	/**
	 * Creates a Stringy object and assigns both str and encoding properties
	 * the supplied values. $str is cast to a string prior to assignment, and if
	 * $encoding is not specified, it defaults to mb_internal_encoding(). It
	 * then returns the initialized object. Throws an InvalidArgumentException
	 * if the first argument is an array or object without a __toString method.
	 *
	 * @param  mixed  $str      Value to modify, after being cast to string
	 * @param  string $encoding The character encoding
	 * @return static A Stringy object
	 * @throws \InvalidArgumentException if an array or object without a
	 *         __toString method is passed as the first argument
	 */
	public static function create($str = '')
	{
		return new static($str);
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
	 * Returns a new string with $string appended.
	 *
	 * @param  string $string The string to append
	 * @return static Object with appended $string
	 */
	public function append($string)
	{
		return static::create($this->str . $string);
	}

	/**
	 * Returns the character at $index, with indexes starting at 0.
	 *
	 * @param  int    $index Position of the character
	 * @return static The character at $index
	 */
	public function at($index)
	{
		return $this->str[$index];
	}

	/**
	 * Returns the substring between $start and $end, if found, or an empty
	 * string. An optional offset may be supplied from which to begin the
	 * search for the start string.
	 *
	 * @param  string $start  Delimiter marking the start of the substring
	 * @param  string $end    Delimiter marking the end of the substring
	 * @param  int    $offset Index from which to begin the search
	 * @return static Object whose $str is a substring between $start and $end
	 */
	public function between($start, $end, $offset = 0)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$startIndex = $this->indexOf($start, $offset);
		if ($startIndex === false) {
			return static::create('', $this->encoding);
		}

		$substrIndex = $startIndex + \mb_strlen($start, $this->encoding);
		$endIndex = $this->indexOf($end, $substrIndex);
		if ($endIndex === false) {
			return static::create('', $this->encoding);
		}

		return $this->substr($substrIndex, $endIndex - $substrIndex);
	}

	/**
	 * Returns a camelCase version of the string. Trims surrounding spaces,
	 * capitalizes letters following digits, spaces, dashes and underscores,
	 * and removes spaces, dashes, as well as underscores.
	 *
	 * @return static Object with $str in camelCase
	 */
	public function camelize()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$encoding = $this->encoding;
		$stringy = $this->trim()->lowerCaseFirst();
		$stringy->str = preg_replace('/^[-_]+/', '', $stringy->str);

		$stringy->str = preg_replace_callback(
			'/[-_\s]+(.)?/u',
			function ($match) use ($encoding) {
				if (isset($match[1])) {
					return \mb_strtoupper($match[1], $encoding);
				}

				return '';
			},
			$stringy->str
		);

		$stringy->str = preg_replace_callback(
			'/[\d]+(.)?/u',
			function ($match) use ($encoding) {
				return \mb_strtoupper($match[0], $encoding);
			},
			$stringy->str
		);

		return $stringy;
	}

	/**
	 * Returns an array consisting of the characters in the string.
	 *
	 * @return array An array of string chars
	 */
	public function chars()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		return str_split($this->str);
	}

	/**
	 * Trims the string and replaces consecutive whitespace characters with a
	 * single space. This includes tabs and newline characters, as well as
	 * multibyte whitespace such as the thin space and ideographic space.
	 *
	 * @return static Object with a trimmed $str and condensed whitespace
	 */
	public function collapseWhitespace()
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		return $this->regexReplace('[[:space:]]+', ' ')->trim();
	}

	/**
	 * Returns true if the string contains $needle, false otherwise. By default
	 * the comparison is case-sensitive, but can be made insensitive by setting
	 * $caseSensitive to false.
	 *
	 * @param  string $needle        Substring to look for
	 * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool   Whether or not $str contains $needle
	 */
	public function contains($needle, $caseSensitive = true)
	{
		return	($caseSensitive ?
				\strpos($this->str, $needle, 0) :
				\stripos($this->str, $needle, 0)) !== false;
	}

	/**
	 * Returns true if the string contains all $needles, false otherwise. By
	 * default the comparison is case-sensitive, but can be made insensitive by
	 * setting $caseSensitive to false.
	 *
	 * @param  string[] $needles       Substrings to look for
	 * @param  bool     $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool     Whether or not $str contains $needle
	 */
	public function containsAll($needles, $caseSensitive = true)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		if (empty($needles)) {
			return false;
		}

		foreach ($needles as $needle) {
			if (!$this->contains($needle, $caseSensitive)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if the string contains any $needles, false otherwise. By
	 * default the comparison is case-sensitive, but can be made insensitive by
	 * setting $caseSensitive to false.
	 *
	 * @param  string[] $needles       Substrings to look for
	 * @param  bool     $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool     Whether or not $str contains $needle
	 */
	public function containsAny($needles, $caseSensitive = true)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		if (empty($needles)) {
			return false;
		}

		foreach ($needles as $needle) {
			if ($this->contains($needle, $caseSensitive)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the length of the string, implementing the countable interface.
	 *
	 * @return int The number of characters in the string, given the encoding
	 */
	public function count()
	{
		return strlen($this->str);
	}

	/**
	 * Returns the number of occurrences of $substring in the given string.
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  string $substring     The substring to search for
	 * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
	 * @return int    The number of $substring occurrences
	 */
	public function countSubstr($substring, $caseSensitive = true)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		if ($caseSensitive) {
			return \mb_substr_count($this->str, $substring, $this->encoding);
		}

		$str = \mb_strtoupper($this->str, $this->encoding);
		$substring = \mb_strtoupper($substring, $this->encoding);

		return \mb_substr_count($str, $substring, $this->encoding);
	}

	/**
	 * Returns a lowercase and trimmed string separated by dashes. Dashes are
	 * inserted before uppercase characters (with the exception of the first
	 * character of the string), and in place of spaces as well as underscores.
	 *
	 * @return static Object with a dasherized $str
	 */
	public function dasherize()
	{
		return $this->delimit('-');
	}

	/**
	 * Returns a lowercase and trimmed string separated by the given delimiter.
	 * Delimiters are inserted before uppercase characters (with the exception
	 * of the first character of the string), and in place of spaces, dashes,
	 * and underscores. Alpha delimiters are not converted to lowercase.
	 *
	 * @param  string $delimiter Sequence used to separate parts of the string
	 * @return static Object with a delimited $str
	 */
	public function delimit($delimiter)
	{
		$str = \preg_replace('~([^A-Z\b])([A-Z])~', '\1-\2', \trim($this->str));
		$str = \preg_replace('~[-_\s]+~', $delimiter, \strtolower($str));

		return new static($str);
	}

	/**
	 * Returns true if the string ends with $substring, false otherwise. By
	 * default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  string $substring     The substring to look for
	 * @param  bool   $caseSensitive Whether or not to enforce case-sensitivity
	 * @return bool   Whether or not $str ends with $substring
	 */
	public function endsWith($substring, $caseSensitive = true)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$substringLength = \mb_strlen($substring, $this->encoding);
		$strLength = strlen($this->str);

		$endOfStr = \mb_substr($this->str, $strLength - $substringLength,
			$substringLength, $this->encoding);

		if (!$caseSensitive) {
			$substring = \mb_strtolower($substring, $this->encoding);
			$endOfStr = \mb_strtolower($endOfStr, $this->encoding);
		}

		return (string) $substring === $endOfStr;
	}

	/**
	 * Returns true if the string ends with any of $substrings, false otherwise.
	 * By default, the comparison is case-sensitive, but can be made insensitive
	 * by setting $caseSensitive to false.
	 *
	 * @param  string[] $substrings    Substrings to look for
	 * @param  bool     $caseSensitive Whether or not to enforce
	 *                                 case-sensitivity
	 * @return bool     Whether or not $str ends with $substring
	 */
	public function endsWithAny($substrings, $caseSensitive = true)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		if (empty($substrings)) {
			return false;
		}

		foreach ($substrings as $substring) {
			if ($this->endsWith($substring, $caseSensitive)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Ensures that the string begins with $substring. If it doesn't, it's
	 * prepended.
	 *
	 * @param  string $substring The substring to add if not present
	 * @return static Object with its $str prefixed by the $substring
	 */
	public function ensureLeft($substring)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$stringy = static::create($this->str, $this->encoding);

		if (!$stringy->startsWith($substring)) {
			$stringy->str = $substring . $stringy->str;
		}

		return $stringy;
	}

	/**
	 * Ensures that the string ends with $substring. If it doesn't, it's
	 * appended.
	 *
	 * @param  string $substring The substring to add if not present
	 * @return static Object with its $str suffixed by the $substring
	 */
	public function ensureRight($substring)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');

		$stringy = static::create($this->str, $this->encoding);

		if (!$stringy->endsWith($substring)) {
			$stringy->str .= $substring;
		}

		return $stringy;
	}

	/**
	 * Returns the first $n characters of the string.
	 *
	 * @param  int    $n Number of characters to retrieve from the start
	 * @return static Object with its $str being the first $n chars
	 */
	public function first($n)
	{
		return new static($n <= 0 ? null : \substr($this->str, 0, $n));
	}


	/**
	 * Returns a new ArrayIterator, thus implementing the IteratorAggregate
	 * interface. The ArrayIterator's constructor is passed an array of chars
	 * in the string. This enables the use of foreach with instances of S.
	 *
	 * @return \ArrayIterator An iterator for the characters in the string
	 */
	public function getIterator()
	{
		return new ArrayIterator(str_split($this->str));
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
	public function getDate(string $pattern = null)
	{
		$pattern = $pattern ?? '/^(?P<year>[12][0-9]{3})-(?P<month>0[1-9]|1[0-2])-(?P<day>0[1-9]|[1-2][0-9]|3[0-1])$/';
		return preg_match($pattern, $this->str, $matches) === false ? null : $matches;
	}

	/**
	 * Wrapper around preg_match(), returning the array based on a matching pattern, or null on failure.
	 * The pattern can contain any number of named or unnamed capture groups.
	 *
	 * @return string[]|null Returns an array with matching patterns, or null on failure
	 */
	public function getMatch(string $pattern, int $flags = 0, int $offset = 0)
	{
		return preg_match($pattern, $this->str, $matches, $flags, $offset) === false ? null : $matches;
	}

	/**
	 * Wrapper around preg_match_all(), returning the array based on a matching pattern, or null on failure.
	 * The pattern can contain any number of named or unnamed capture groups.
	 *
	 * @return string[]|null Returns an array with matching patterns, or null on failure
	 */
	public function getMatchAll(string $pattern, int $flags = PREG_PATTERN_ORDER, int $offset = 0)
	{
		return preg_match_all($pattern, $this->str, $matches, $flags, $offset) === false ? null : $matches;
	}

	/**
	 * Gets a hash code of the internal string.
	 *
	 * @param  string|null $algo Algorithm name supported by the hash() library, defaults to 'md5'
	 * @return string
	 */
	public function hash(string $algo = 'md5', bool $raw_output = false)
	{
		return new static(hash($algo, $this->str, $raw_output));
	}

	/**
	 * Returns true if the string contains a lower case char, false
	 * otherwise.
	 *
	 * @return bool Whether or not the string contains a lower case character.
	 */
	public function hasLowerCase()
	{
		trigger_error('Function not implemented yet');

		return $this->matchesPattern('.*[[:lower:]]');
	}

	/**
	 * Returns true if the string contains an upper case char, false
	 * otherwise.
	 *
	 * @return bool Whether or not the string contains an upper case character.
	 */
	public function hasUpperCase()
	{
		trigger_error('Function not implemented yet');

		return $this->matchesPattern('.*[[:upper:]]');
	}


	/**
	 * Convert all HTML entities to their applicable characters. An alias of
	 * html_entity_decode. For a list of flags, refer to
	 * http://php.net/manual/en/function.html-entity-decode.php
	 *
	 * @param  int|null $flags Optional flags
	 * @return static   Object with the resulting $str after being html decoded.
	 */
	public function htmlDecode($flags = ENT_COMPAT)
	{
		trigger_error('Function not implemented yet');

		return static::create(html_entity_decode($this->str, $flags, $this->encoding), $this->encoding);
	}

	/**
	 * Convert all applicable characters to HTML entities. An alias of
	 * htmlentities. Refer to http://php.net/manual/en/function.htmlentities.php
	 * for a list of flags.
	 *
	 * @param  int|null $flags Optional flags
	 * @return static   Object with the resulting $str after being html encoded.
	 */
	public function htmlEncode($flags = ENT_COMPAT)
	{
		trigger_error('Function not implemented yet');

		return static::create(htmlentities($this->str, $flags, $this->encoding), $this->encoding);
	}

	/**
	 * Capitalizes the first word of the string, replaces underscores with
	 * spaces, and strips '_id'.
	 *
	 * @return static Object with a humanized $str
	 */
	public function humanize()
	{
		trigger_error('Function not implemented yet');

		return static::create(
					str_replace(['_id', '_'], ['', ' '], $this->str),
					$this->encoding)->trim()->upperCaseFirst();
	}

	/**
	 * Returns the index of the first occurrence of $needle in the string,
	 * and false if not found. Accepts an optional offset from which to begin
	 * the search.
	 *
	 * @param  string   $needle Substring to look for
	 * @param  int      $offset Offset from which to search
	 * @return int|bool The occurrence's index if found, otherwise false
	 */
	public function indexOf($needle, $offset = 0)
	{
		trigger_error('Function not implemented yet');

		return \mb_strpos($this->str, (string) $needle, (int) $offset, $this->encoding);
	}

	/**
	 * Returns the index of the last occurrence of $needle in the string,
	 * and false if not found. Accepts an optional offset from which to begin
	 * the search. Offsets may be negative to count from the last character
	 * in the string.
	 *
	 * @param  string   $needle Substring to look for
	 * @param  int      $offset Offset from which to search
	 * @return int|bool The last occurrence's index if found, otherwise false
	 */
	public function indexOfLast($needle, $offset = 0)
	{
		trigger_error('Function not implemented yet');

		return \mb_strrpos($this->str, (string) $needle, (int) $offset, $this->encoding);
	}

	/**
	 * Inserts $substring into the string at the $index provided.
	 *
	 * @param  string $substring String to be inserted
	 * @param  int    $index     The index at which to insert the substring
	 * @return static Object with the resulting $str after the insertion
	 */
	public function insert($substring, $index)
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->str, $this->encoding);
		if ($index > $stringy->length()) {
			return $stringy;
		}

		$start = \mb_substr($stringy->str, 0, $index, $stringy->encoding);
		$end = \mb_substr($stringy->str, $index, $stringy->length(), $stringy->encoding);

		$stringy->str = $start . $substring . $end;

		return $stringy;
	}

	/**
	 * Returns true if the string contains only alphabetic chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only alphabetic chars
	 */
	public function isAlpha()
	{
		trigger_error('Function not implemented yet');

		return $this->matchesPattern('^[[:alpha:]]*$');
	}

	/**
	 * Returns true if the string contains only alphabetic and numeric chars,
	 * false otherwise.
	 *
	 * @return bool Whether or not $str contains only alphanumeric chars
	 */
	public function isAlphanumeric()
	{
		trigger_error('Function not implemented yet');

		return $this->matchesPattern('^[[:alnum:]]*$');
	}

	/**
	 * Returns true if the string contains only whitespace chars, false
	 * otherwise.
	 *
	 * @return bool Whether or not $str contains only whitespace characters
	 */
	public function isBlank()
	{
		trigger_error('Function not implemented yet');

		return $this->matchesPattern('^[[:space:]]*$');
	}

	/**
	 * Returns true if the string contains a date in the format 'YYYY-MM-DD'
	 * Alternative patterns:
	 *		'/^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/'
	 * 		'/^\d\d\d\d-\d\d-\d\d$/'
	 * 		'/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'
	 *
	 * @return bool Whether or not $str contains a matching date in the format 'YYYY-MM-DD'
	 */
	public function isDate(string $pattern = null)
	{
		$pattern = $pattern ??
					($matches === null
						? '/^(?:[0-9]{4})-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1])$/'
						: '/^(?P<year>[0-9]{4})-(?P<month>0[1-9]|1[0-2])-(?P<day>0[1-9]|[1-2][0-9]|3[0-1])$/'
					);
		return preg_match($pattern, $this->str, $matches);
	}

	/**
	 * Returns true if the string contains only hexadecimal chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only hexadecimal chars
	 */
	public function isHex()
	{
		return ctype_xdigit($this->str);
	}

	/**
	 * Returns true if the string contains only hexadecimal chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only hexadecimal chars
	 */
	public function isHexadecimal()
	{
		return ctype_xdigit($this->str);
	}

	/**
	 * Returns true if the string is JSON, false otherwise. Unlike json_decode
	 * in PHP 5.x, this method is consistent with PHP 7 and other JSON parsers,
	 * in that an empty string is not considered valid JSON.
	 *
	 * @return bool Whether or not $str is JSON
	 */
	public function isJson()
	{
		return is_array(json_decode($this->str, true));

	//	if (!$this->length()) return false;
	//	json_decode($this->str);
	//	return (json_last_error() === JSON_ERROR_NONE);
	}

	/**
	 * Returns true if the string contains only lower case chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only lower case characters
	 */
	public function isLower()
	{
		return \ctype_lower($this->str);
	}

	/**
	 * Returns true if the string contains only lower case chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only lower case characters
	 */
	public function isLowerCase()
	{
		return \ctype_lower($this->str);
	}

	/**
	 * Returns true if the string contains a match for $pattern
	 *
	 * @return bool Whether or not $str contains a match for $pattern
	 */
	public function isMatch($pattern, array &$matches = null)
	{
		return \preg_match($pattern, $this->str, $matches);
	}

	/**
	 * Returns true if the string is serialized, false otherwise.
	 *
	 * @return bool Whether or not $str is serialized
	 */
	public function isSerialized()
	{
		return $this->str === 'b:0;' || @unserialize($this->str) !== false;
	}

	/**
	 * Returns true if the string is base64 encoded, false otherwise.
	 *
	 * @return bool Whether or not $str is base64 encoded
	 */
	public function isBase64()
	{
		return \base64_encode(\base64_decode($this->str, true)) === $this->str;
	}

	/**
	 * Returns true if the string contains only upper case chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only upper case characters
	 */
	public function isUpper()
	{
		return \ctype_upper($this->str);
	}

	/**
	 * Returns true if the string contains only upper case chars, false otherwise.
	 *
	 * @return bool Whether or not $str contains only upper case characters
	 */
	public function isUpperCase()
	{
		return \ctype_upper($this->str);
	}

	/**
	 * Returns the last $n characters of the string.
	 *
	 * @param  int    $n Number of characters to retrieve from the end
	 * @return static Object with its $str being the last $n chars
	 */
	public function last($n)
	{
		return new static($n <= 0 ? null : \substr($this->str, -$n));
	}

	/**
	 * Converts the first character of the supplied string to lower case.
	 *
	 * @return static Object with the first character of $str being lower case
	 */
	public function lcFirst()
	{
		return new static(\lcfirst($this->str));
	}

	/**
	 * Returns the length of the string. An alias for PHP's mb_strlen() function.
	 *
	 * @return int The number of characters in $str given the encoding
	 */
	public function length()
	{
		return \strlen($this->str);
	}

	/**
	 * Splits on newlines and carriage returns, returning an array of Stringy
	 * objects corresponding to the lines in the string.
	 *
	 * @return static[] An array of Stringy objects
	 */
	public function lines()
	{
		trigger_error('Function not implemented yet');

		$array = $this->split('[\r\n]{1,2}', $this->str);
		for ($i = 0; $i < count($array); $i++) {
			$array[$i] = static::create($array[$i], $this->encoding);
		}

		return $array;
	}

	/**
	 * Returns the longest common prefix between the string and $otherStr.
	 *
	 * @param  string $otherStr Second string for comparison
	 * @return static Object with its $str being the longest common prefix
	 */
	public function longestCommonPrefix($otherStr)
	{
		trigger_error('Function not implemented yet');

		$encoding = $this->encoding;
		$maxLength = min($this->length(), \mb_strlen($otherStr, $encoding));

		$longestCommonPrefix = '';
		for ($i = 0; $i < $maxLength; $i++) {
			$char = \mb_substr($this->str, $i, 1, $encoding);

			if ($char == \mb_substr($otherStr, $i, 1, $encoding)) {
				$longestCommonPrefix .= $char;
			} else {
				break;
			}
		}

		return static::create($longestCommonPrefix, $encoding);
	}

	/**
	 * Returns the longest common suffix between the string and $otherStr.
	 *
	 * @param  string $otherStr Second string for comparison
	 * @return static Object with its $str being the longest common suffix
	 */
	public function longestCommonSuffix($otherStr)
	{
		trigger_error('Function not implemented yet');

		$encoding = $this->encoding;
		$maxLength = min($this->length(), \mb_strlen($otherStr, $encoding));

		$longestCommonSuffix = '';
		for ($i = 1; $i <= $maxLength; $i++) {
			$char = \mb_substr($this->str, -$i, 1, $encoding);

			if ($char == \mb_substr($otherStr, -$i, 1, $encoding)) {
				$longestCommonSuffix = $char . $longestCommonSuffix;
			} else {
				break;
			}
		}

		return static::create($longestCommonSuffix, $encoding);
	}

	/**
	 * Returns the longest common substring between the string and $otherStr.
	 * In the case of ties, it returns that which occurs first.
	 *
	 * @param  string $otherStr Second string for comparison
	 * @return static Object with its $str being the longest common substring
	 */
	public function longestCommonSubstring($otherStr)
	{
		trigger_error('Function not implemented yet');

		// Uses dynamic programming to solve
		// http://en.wikipedia.org/wiki/Longest_common_substring_problem
		$encoding = $this->encoding;
		$stringy = static::create($this->str, $encoding);
		$strLength = $stringy->length();
		$otherLength = \mb_strlen($otherStr, $encoding);

		// Return if either string is empty
		if ($strLength == 0 || $otherLength == 0) {
			$stringy->str = '';
			return $stringy;
		}

		$len = 0;
		$end = 0;
		$table = array_fill(0, $strLength + 1,
			array_fill(0, $otherLength + 1, 0));

		for ($i = 1; $i <= $strLength; $i++) {
			for ($j = 1; $j <= $otherLength; $j++) {
				$strChar = \mb_substr($stringy->str, $i - 1, 1, $encoding);
				$otherChar = \mb_substr($otherStr, $j - 1, 1, $encoding);

				if ($strChar == $otherChar) {
					$table[$i][$j] = $table[$i - 1][$j - 1] + 1;
					if ($table[$i][$j] > $len) {
						$len = $table[$i][$j];
						$end = $i;
					}
				} else {
					$table[$i][$j] = 0;
				}
			}
		}

		$stringy->str = \mb_substr($stringy->str, $end - $len, $len, $encoding);

		return $stringy;
	}

	/**
	 * Converts the first character of the string to lower case.
	 *
	 * @return static Object with the first character of $str being lower case
	 */
	public function lowerCaseFirst()
	{
		trigger_error('Function not implemented yet');

		$first = \mb_substr($this->str, 0, 1, $this->encoding);
		$rest = \mb_substr($this->str, 1, $this->length() - 1,
			$this->encoding);

		$str = \mb_strtolower($first, $this->encoding) . $rest;

		return static::create($str, $this->encoding);
	}

	/**
	 * Returns a string with whitespace removed from the start/left of the string.
	 * Accepts an optional string of characters to strip instead of the defaults.
	 *
	 * @param  string $character_mask Optional string of characters to strip
	 * @return static Object with a trimmed $str
	 */
	public function ltrim(string $character_mask = " \t\n\r\0\x0B")
	{
		return new static(ltrim($this->str, $character_mask));
	}

	/**
	 * Gets an MD5 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the md5 hash
	 * @return string
	 */
	public function md5(bool $raw_output = false)
	{
		return new static(hash('md5', $this->str, $raw_output));
	}

	/**
	 * Returns whether or not a character exists at an index. Offsets may be
	 * negative to count from the last character in the string. Implements
	 * part of the ArrayAccess interface.
	 *
	 * @param  mixed   $offset The index to check
	 * @return boolean Whether or not the index exists
	 */
	public function offsetExists($offset)
	{
		trigger_error('Function not implemented yet');

		$length = $this->length();
		$offset = (int) $offset;

		if ($offset >= 0) {
			return ($length > $offset);
		}

		return ($length >= abs($offset));
	}

	/**
	 * Returns the character at the given index. Offsets may be negative to
	 * count from the last character in the string. Implements part of the
	 * ArrayAccess interface, and throws an OutOfBoundsException if the index
	 * does not exist.
	 *
	 * @param  mixed $offset         The index from which to retrieve the char
	 * @return mixed                 The character at the specified index
	 * @throws \OutOfBoundsException If the positive or negative offset does
	 *                               not exist
	 */
	public function offsetGet($offset)
	{
		trigger_error('Function not implemented yet');

		$offset = (int) $offset;
		$length = $this->length();

		if (($offset >= 0 && $length <= $offset) || $length < abs($offset)) {
			throw new OutOfBoundsException('No character exists at the index');
		}

		return \mb_substr($this->str, $offset, 1, $this->encoding);
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
		trigger_error('Function not implemented yet');

		// Stringy is immutable, cannot directly set char
		throw new Exception('Stringy object is immutable, cannot modify char');
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
		trigger_error('Function not implemented yet');

		// Don't allow directly modifying the string
		throw new Exception('Stringy object is immutable, cannot unset char');
	}

	/**
	 * Pads the string to a given length with $padStr. If length is less than
	 * or equal to the length of the string, no padding takes places. The
	 * default string used for padding is a space, and the default type (one of
	 * 'left', 'right', 'both') is 'right'. Throws an InvalidArgumentException
	 * if $padType isn't one of those 3 values.
	 *
	 * @param  int    $pad_length  Desired string length after padding
	 * @param  string $pad_string  String used to pad, defaults to space
	 * @param  string $pad_type One of 'left'|STR_PAD_LEFT, 'right'|STR_PAD_RIGHT or 'both'|STR_PAD_BOTH
	 * @return static Object with a padded $str
	 * @throws /InvalidArgumentException If $padType isn't one of 'right',
	 *         'left' or 'both'
	 */
	public function pad(int $pad_length, string $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
	{
		if (is_string($pad_type))
		{
			if ( ! ctype_lower($pad_type))
				$pad_type = strtolower($pad_type);

			switch ($pad_type)
			{
				case 'left':	$pad_type = STR_PAD_LEFT;	break;
				case 'right':	$pad_type = STR_PAD_RIGHT;	break;
				case 'both':	$pad_type = STR_PAD_BOTH;	break;
				default:
					throw new InvalidArgumentException('Pad expects $padType to be one of ' .
													"'left', 'right' or 'both'");
			}
		}
		return new static(str_pad($this->str, $pad_length, $pad_string, $pad_type));
	}

	/**
	 * Returns a new string of a given length such that both sides of the string are padded.
	 * Wrapper around str_pad() with $pad_type of 'both'|STR_PAD_BOTH.
	 *
	 * @param  int    $pad_length Desired string length after padding
	 * @param  string $pad_string String used to pad, defaults to space
	 * @return static String with padding applied
	 */
	public function padBoth(int $pad_length, string $pad_string = ' ')
	{
		return new static(str_pad($this->str, $pad_length, $pad_string, STR_PAD_BOTH));
	}

	/**
	 * Returns a new string of a given length such that the beginning of the string is padded.
	 * Wrapper around str_pad() with $pad_type of 'left'|STR_PAD_LEFT.
	 *
	 * @param  int    $pad_length Desired string length after padding
	 * @param  string $pad_string String used to pad, defaults to space
	 * @return static String with left padding
	 */
	public function padLeft(int $pad_length, string $pad_string = ' ')
	{
		return new static(str_pad($this->str, $pad_length, $pad_string, STR_PAD_LEFT));
	}

	/**
	 * Returns a new string of a given length such that the end of the string is padded.
	 * Wrapper around str_pad() with $pad_type of 'right'|STR_PAD_RIGHT.
	 *
	 * @param  int    $pad_length Desired string length after padding
	 * @param  string $pad_string String used to pad, defaults to space
	 * @return static String with left padding
	 */
	public function padRight(int $pad_length, string $pad_string = ' ')
	{
		return new static(str_pad($this->str, $pad_length, $pad_string, STR_PAD_RIGHT));
	}

	/**
	 * Returns a new string starting with $string.
	 *
	 * @param  string $string The string to prepend
	 * @return static Object with prepend $string
	 */
	public function prepend($string)
	{
		return new static($string . $this->str);
	}

	/**
	 * Replaces all occurrences of $pattern in $str by $replacement. An alias
	 * for mb_ereg_replace(). Note that the 'i' option with multibyte patterns
	 * in mb_ereg_replace() requires PHP 5.6+ for correct results. This is due
	 * to a lack of support in the bundled version of Oniguruma in PHP < 5.6,
	 * and current versions of HHVM (3.8 and below).
	 *
	 * @param  string $pattern     The regular expression pattern
	 * @param  string $replacement The string to replace with
	 * @param  string $options     Matching conditions to be used
	 * @return static Object with the resulting $str after the replacements
	 */
	public function regexReplace($pattern, $replacement, $options = 'msr')
	{
		trigger_error('Function not implemented yet');

		$regexEncoding = $this->regexEncoding();
		$this->regexEncoding($this->encoding);

		$str = $this->eregReplace($pattern, $replacement, $this->str, $options);
		$this->regexEncoding($regexEncoding);

		return static::create($str, $this->encoding);
	}

	/**
	 * Returns a new string with the prefix $substring removed, if present.
	 *
	 * @param  string $substring The prefix to remove
	 * @return static Object having a $str without the prefix $substring
	 */
	public function removeLeft($substring)
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->str, $this->encoding);

		if ($stringy->startsWith($substring)) {
			$substringLength = \mb_strlen($substring, $stringy->encoding);
			return $stringy->substr($substringLength);
		}

		return $stringy;
	}

	/**
	 * Returns a new string with the suffix $substring removed, if present.
	 *
	 * @param  string $substring The suffix to remove
	 * @return static Object having a $str without the suffix $substring
	 */
	public function removeRight($substring)
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->str, $this->encoding);

		if ($stringy->endsWith($substring)) {
			$substringLength = \mb_strlen($substring, $stringy->encoding);
			return $stringy->substr(0, $stringy->length() - $substringLength);
		}

		return $stringy;
	}

	/**
	 * Returns a repeated string given a multiplier. An alias for str_repeat.
	 *
	 * @param  int    $multiplier The number of times to repeat the string
	 * @return static Object with a repeated str
	 */
	public function repeat($multiplier)
	{
		trigger_error('Function not implemented yet');

		return static::create(str_repeat($this->str, $multiplier), $this->encoding);
	}

	/**
	 * Replaces all occurrences of $search in $str by $replacement.
	 *
	 * @param  string $search      The needle to search for
	 * @param  string $replacement The string to replace with
	 * @return static Object with the resulting $str after the replacements
	 */
	public function replace($search, $replacement)
	{
		trigger_error('Function not implemented yet');

		return $this->regexReplace(preg_quote($search), $replacement);
	}

	/**
	 * Returns a reversed string.
	 *
	 * @return static Object with a reversed $str
	 */
	public function reverse()
	{
		return new static(strrev($this->str));
	}

	/**
	 * Truncates the string to a given length, while ensuring that it does not
	 * split words. If $substring is provided, and truncating occurs, the
	 * string is further truncated so that the substring may be appended without
	 * exceeding the desired length.
	 *
	 * @param  int    $length    Desired length of the truncated string
	 * @param  string $substring The substring to append if it can fit
	 * @return static Object with the resulting $str after truncating
	 */
	public function safeTruncate($length, $substring = '')
	{
		trigger_error('Function not implemented yet');

		$stringy = static::create($this->str, $this->encoding);
		if ($length >= $stringy->length()) {
			return $stringy;
		}

		// Need to further trim the string so we can append the substring
		$encoding = $stringy->encoding;
		$substringLength = \mb_strlen($substring, $encoding);
		$length = $length - $substringLength;

		$truncated = \mb_substr($stringy->str, 0, $length, $encoding);

		// If the last word was truncated
		if (mb_strpos($stringy->str, ' ', $length - 1, $encoding) != $length) {
			// Find pos of the last occurrence of a space, get up to that
			$lastPos = \mb_strrpos($truncated, ' ', 0, $encoding);
			if ($lastPos !== false) {
				$truncated = \mb_substr($truncated, 0, $lastPos, $encoding);
			}
		}

		$stringy->str = $truncated . $substring;

		return $stringy;
	}

	/**
	 * Gets a SHA1 (160-bit) hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA1 hash
	 * @return string
	 */
	public function sha1(bool $raw_output = false)
	{
		return new static(hash('sha1', $this->str, $raw_output));
	}

	/**
	 * Gets a SHA-256 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-256 hash
	 * @return string
	 */
	public function sha256(bool $raw_output = false)
	{
		return new static(hash('sha256', $this->str, $raw_output));
	}

	/**
	 * Gets a SHA-384 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-384 hash
	 * @return string
	 */
	public function sha384(bool $raw_output = false)
	{
		return new static(hash('sha384', $this->str, $raw_output));
	}

	/**
	 * Gets a SHA-512 hash code of the internal string. Return result can be raw binary or hex by default
	 *
	 * @param  bool|null $raw_output return the raw binary bytes or hex values of the SHA-512 hash
	 * @return string
	 */
	public function sha512(bool $raw_output = false)
	{
		return new static(hash('sha512', $this->str, $raw_output));
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
