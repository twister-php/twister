<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php
use \DateInterval;				//	http://php.net/manual/en/class.dateinterval.php
use \DateTimeZone;				//	http://php.net/manual/en/class.datetimezone.php
//use \DatePeriod;				//	http://php.net/manual/en/class.dateperiod.php					//	I don't extend this class, because the `start`, `current` and `end` are private, so I don't have access to them!

use \Iterator;					//	http://php.net/manual/en/class.iteratoraggregate.php			Interface to create an external Iterator.

class DatePeriod implements Iterator
{
	private $start		=	null;
	private $current	=	null;
	private $end		=	null;
	private $interval	=	null;

	public static $utc	=	null;
	public static $p1d	=	null;

	/**
	 *	@param string|DateTime $start	start date
	 *	@param string|DateTime $param1	end date or interval
	 *	@param string|DateTime $param2	interval or end date
	 *	@return void
	 */
	public function __construct($start, $param1 = null, $param2 = null)
	{
		if (is_string($start))
		{
			$this->start = new \DateTime($start, self::$utc);																	//	all roads are lead to this ...
			/*
			if (strlen($start) === 10 && preg_match('~\d\d\d\d-\d\d-\d\d~', $start) === 1)
			{
				$this->start = new \DateTime($start, self::$utc);
			}
			else if (preg_match('~\d\d\d\d-\d\d-\d\d~', $start) === 1)															//	more relaxed ... basically the same as above, leaving this here incase I don't support `time` component in the future! Currently, because I support the `time` component, the top is not necessary, only this one! I was going to do this: preg_match('~(\d\d\d\d)-(\d\d)-(\d\d)~', $param1, $matches) and extract the date, but I curently don't care!
			{
				$this->start = new \DateTime($start, self::$utc);
			}
			else
			{
				$this->start = new \DateTime($start, self::$utc);
			}
			*/
		}
		else if ($start instanceof \Twister\DateTime || $start instanceof \Twister\Date)
		{
			$this->start = new \DateTime((string) $start, self::$utc);
		}
		else if ($start instanceof \DateTime)
		{
			$this->start = $start;
		}
		else if (is_object($start))
		{
			if ( ! method_exists($start, '__toString'))
				throw new InvalidArgumentException('Starting date object must have a __toString method');

			$this->start = new \DateTime((string) $start, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to DatePeriod constructor in $start parameter; expecting a string or DateTime object, received "%s"',
						(is_object($start) ? get_class($start) : gettype($start))
					));
		}

		$this->current	= clone $this->start;


		if (is_string($param1))
		{
			if (preg_match('~\d\d\d\d-\d\d-\d\d~', $param1) === 1)
			{
				$this->end = new \DateTime($param1, self::$utc);

				if ($param2 === null)
				{	//	fast test for the most likely param2 value  ... just to return faster!
					$this->interval	= self::$p1d;
					return;
				}
				$this->interval	= $param2;
			}
			else if (strtotime($param1) !== false)
			{
			//	$this->end = new \DateTime(strtotime($param1, $this->start->getTimestamp()), self::$utc);
				$this->end = DateTime::createFromFormat('U', strtotime($param1, $this->start->getTimestamp()), self::$utc);	//	not working 100% for fixed dates like '27 January 2017' returns '2017-01-26'
				if ($param2 === null)
				{	//	fast test for the most likely param2 value  ... just to return faster!
					$this->interval	= self::$p1d;
					return;
				}
				$this->interval	= $param2;
			}
			else if ($param1 !== '' && $param1[0] === 'P')
			{
				$this->interval = new \DateInterval($param1);
				$this->end = $param2;
			}
			else
			{
				throw new InvalidArgumentException(
							"Invalid value passed to DatePeriod constructor in \$param1; expecting a valid DateTime string or DateInterval string, received `{$param1}`"
						);
			}
		}
		else if ($param1 === null)
		{
			$this->end		=	new \DateTime('9999-12-31', self::$utc);
			$this->interval	=	self::$p1d;
			return;
		}
		else if ($param1 instanceof \Twister\DateTime)
		{
			$this->end = new \DateTime((string) $param1, self::$utc);
			if ($param2 === null)
			{	//	fast test for the most likely param2 value  ... just to return faster!
				$this->interval	= self::$p1d;
				return;
			}
			$this->interval	= $param2;
		}
		else if ($param1 instanceof \DateTime)
		{
			$this->end		= $param1;
			if ($param2 === null)
			{	//	fast test for the most likely param2 value  ... just to return faster!
				$this->interval	= self::$p1d;
				return;
			}
			$this->interval	= $param2;
		}
		else if ($param1 instanceof \DateInterval)
		{
			$this->interval	= $param1;
			$this->end		= $param2;
		}
		else if (is_object($param1))
		{
			if ( ! method_exists($param1, '__toString'))
				throw new InvalidArgumentException('Starting date object must have a __toString method');

			$this->end = new \DateTime((string) $param1, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to DatePeriod constructor in $param1 parameter; expecting a string or DateTime object, received "%s"',
						(is_object($param1) ? get_class($param1) : gettype($param1))
					));
		}


		if ($this->end instanceof \DateTime)
		{
			//	do nothing
		}
		else if ($this->end === null)
		{
			$this->end	= new \DateTime('9999-12-31', self::$utc);
		}
		else if (is_string($this->end))
		{
			if (preg_match('~\d\d\d\d-\d\d-\d\d~', $this->end) === 1)
			{
				$this->end = new \DateTime($this->end, self::$utc);
			}
			else if (strtotime($this->end) !== false)
			{
			//	$this->end = new \DateTime(strtotime($this->end, $this->start->getTimestamp()), self::$utc);
				$this->end = DateTime::createFromFormat('U', strtotime($param1, $this->start->getTimestamp()), self::$utc);	//	not working 100% for fixed dates like '27 January 2017' returns '2017-01-26'
			}
			else
			{
				throw new InvalidArgumentException(
							"Invalid value passed to DatePeriod constructor for end date; expecting a valid DateTime string or DateInterval string, received `{$this->end}`"
						);
			}
		}
		else if ($this->end instanceof \Twister\DateTime)
		{
			$this->end = new \DateTime((string) $this->end, self::$utc);
		}
		else if (is_object($this->end))
		{
			if ( ! method_exists($this->end, '__toString'))
				throw new InvalidArgumentException('End date object must have a __toString method');

			$this->end = new \DateTime((string) $this->end, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to DatePeriod constructor for end date; expecting a valid DateTime object or string, received "%s"',
						(is_object($this->end) ? get_class($this->end) : gettype($this->end))
					));
		}


		if ($this->interval === null)
		{
			$this->interval	= self::$p1d;
		}
		else if ($this->interval instanceof \DateInterval)
		{
			//	do nothing
		}
		else if (is_string($this->interval))
		{
			if ($this->interval !== '' && $this->interval[0] === 'P')
				$this->interval	= new \DateInterval($this->interval);
			else
				throw new InvalidArgumentException(
							"Invalid DateInterval passed to DatePeriod constructor; expecting a valid DateInterval string starting with `P` or null, received `{$this->interval}`"
						);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to DatePeriod constructor; expecting a valid DateInterval object or string, received "%s"',
						(is_object($this->interval) ? get_class($this->interval) : gettype($this->interval))
					));
		}
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

	/**
	 *	Gets the current date
	 */
	public function getDate()			//	@alias
	{
		return $this->current;
	}

	/**
	 *	Gets the current date
	 */
	public function getDateTime()		//	@alias
	{
		return $this->current;
	}

	/**
	 *	Gets the current date
	 */
	public function getCurrentDate()	//	@alias
	{
		return $this->current;
	}

	/**
	 *	Gets the start date, if $format is null, then returns the DateTime object
	 *
	 *	@link	http://php.net/manual/en/dateperiod.getstartdate.php
	 *
	 *	@return $this->start if $format is null, then returns the DateTime object
	 */
	public function getStartDate()
	{
		return $this->start;
	}

	/**
	 *	Gets the end date, if $format is null, then returns the DateTime object
	 *
	 *	@link	http://php.net/manual/en/dateperiod.getenddate.php
	 *
	 *	@return $this->end if $format is null, then returns the DateTime object
	 */
	public function getEndDate()
	{
		return $this->end;
	}

	/**
	 *	Gets a DateInterval object representing the interval used for the period.
	 *
	 *	@link	http://php.net/manual/en/dateperiod.getdateinterval.php
	 *
	 *	@return $this->interval
	 */
	public function getDateInterval()
	{
		return $this->interval;
	}

	/**
	 *	Gets a DateInterval object representing the interval used for the period.
	 *
	 *	@alias getDateInterval()
	 *
	 *	@link	http://php.net/manual/en/dateperiod.getdateinterval.php
	 *
	 *	@return $this->interval
	 */
	public function getInterval()
	{
		return $this->interval;
	}

	/**
	 *	Wrapper around \DateTime->format()
	 *
	 *	@link	http://php.net/manual/en/datetime.format.php
	 *
	 *	@return string
	 */
	public function format($format = 'Y-m-d')
	{
		return $this->current->format($format);
	}

	/**
	 *
	 */
	public function formatStartDate($format = 'Y-m-d')
	{
		return $this->start->format($format);
	}

	/**
	 *
	 */
	public function formatEndDate($format = 'Y-m-d')
	{
		return $this->end->format($format);
	}

	/**
	 *
	 *	@link	http://php.net/manual/en/dateinterval.format.php
	 *
	 */
	public function formatInterval($format = '%r%a')
	{
		return $this->interval->format($format);
	}

	/**
	 *	Wrapper around \DateTime->modify()
	 *
	 *	@link	http://php.net/manual/en/datetime.modify.php
	 *
	 *	`Alter the timestamp of a DateTime object by incrementing or decrementing in a format accepted by strtotime().`
	 *
	 *	@param  string $modify A date/time string.
	 *	@return $this
	 */
	public function modify($modify = '+1 day')
	{
		$this->current->modify($modify);
		return $this;
	}

	/**
	 *
	 */
	public function modifyStartDate($modify = '+1 day')
	{
		$this->start->modify($modify);
		return $this;
	}

	/**
	 *
	 */
	public function modifyEndDate($modify = '+1 day')
	{
		$this->end->modify($modify);
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function add($interval_spec = 'P1D')
	{
		$this->current->add(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function sub($interval_spec = 'P1D')
	{
		$this->current->sub(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function addToStart($interval_spec = 'P1D')
	{
		$this->start->add(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function subFromStart($interval_spec = 'P1D')
	{
		$this->start->sub(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function addToEnd($interval_spec = 'P1D')
	{
		$this->end->add(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
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
	 *	`Formats are based on the » ISO 8601 duration specification.`
	 *
	 *	@param  string $interval_spec The character encoding
	 *	@return $this
	 */
	public function subFromEnd($interval_spec = 'P1D')
	{
		$this->end->sub(is_string($interval_spec) ? new \DateInterval($interval_spec) : ($interval_spec instanceof \DateInterval ? $interval_spec : new \DateInterval($interval_spec)));
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setDate() for the current date
	 *
	 *	@link http://php.net/manual/en/datetime.setdate.php
	 *
	 *	`Resets the current date of the DateTime object to a different date.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setDate($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d\d\d)-(\d\d)-(\d\d)~', $param1, $values) === 1)
			{
				$this->current->setDate($values[1], $values[2], $values[3]);
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setDate()");
			}
		}
		else if (is_numeric($param1) && is_numeric($param2) && is_numeric($param3))
		{
			$this->current->setDate($param1, $param2, $param3);
		}
		else if ($param1 instanceof \DateTime && $param2 === null && $param3 === null)
		{
			$this->current = clone $param1;
		}
		else if ($param1 instanceof \Twister\DateTime && $param2 === null && $param3 === null)
		{
			$this->current = new \DateTime((string) $param1, self::$utc);
		}
		else if (is_object($param1) && $param2 === null && $param3 === null)
		{
			if ( ! method_exists($param1, '__toString'))
				throw new InvalidArgumentException('Object passed to setDate() must have a __toString method');

			$this->current = new \DateTime((string) $param1, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to setDate(), received "%s"',
						(is_object($param1) ? get_class($param1) : gettype($param1))
					));
		}
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setDate() for the current date
	 *
	 *	@alias setDate()
	 *
	 *	@link http://php.net/manual/en/datetime.setdate.php
	 *
	 *	`Resets the current date of the DateTime object to a different date.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setCurrentDate($param1, $param2 = null, $param3 = null)
	{
		return $this->setDate($param1, $param2, $param3);
	}

	/**
	 *	Wrapper around \DateTime->setDate() for the start date
	 *
	 *	@link http://php.net/manual/en/datetime.setdate.php
	 *
	 *	`Resets the current date of the DateTime object to a different date.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setStartDate($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d\d\d)-(\d\d)-(\d\d)~', $param1, $values) === 1)
			{
				$this->start->setDate($values[1], $values[2], $values[3]);
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setStartDate()");
			}
		}
		else if (is_numeric($param1) && is_numeric($param2) && is_numeric($param3))
		{
			$this->start->setDate($param1, $param2, $param3);
		}
		else if ($param1 instanceof \DateTime && $param2 === null && $param3 === null)
		{
			$this->start = clone $param1;
		}
		else if ($param1 instanceof \Twister\DateTime && $param2 === null && $param3 === null)
		{
			$this->start = new \DateTime((string) $param1, self::$utc);
		}
		else if (is_object($param1) && $param2 === null && $param3 === null)
		{
			if ( ! method_exists($param1, '__toString'))
				throw new InvalidArgumentException('Object passed to setStartDate() must have a __toString method');

			$this->start = new \DateTime((string) $param1, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to setStartDate(), received "%s"',
						(is_object($param1) ? get_class($param1) : gettype($param1))
					));
		}
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setDate() for the end date
	 *
	 *	@link http://php.net/manual/en/datetime.setdate.php
	 *
	 *	`Resets the current date of the DateTime object to a different date.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setEndDate($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d\d\d)-(\d\d)-(\d\d)~', $param1, $values) === 1)
			{
				$this->end->setDate($values[1], $values[2], $values[3]);
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setEndDate()");
			}
		}
		else if (is_numeric($param1) && is_numeric($param2) && is_numeric($param3))
		{
			$this->end->setDate($param1, $param2, $param3);
		}
		else if ($param1 instanceof \DateTime && $param2 === null && $param3 === null)
		{
			$this->end = clone $param1;
		}
		else if ($param1 instanceof \Twister\DateTime && $param2 === null && $param3 === null)
		{
			$this->end = new \DateTime((string) $param1, self::$utc);
		}
		else if (is_object($param1) && $param2 === null && $param3 === null)
		{
			if ( ! method_exists($param1, '__toString'))
				throw new InvalidArgumentException('Object passed to setEndDate() must have a __toString method');

			$this->end = new \DateTime((string) $param1, self::$utc);
		}
		else
		{
			throw new InvalidArgumentException(sprintf(
						'Invalid type passed to setEndDate(), received "%s"',
						(is_object($param1) ? get_class($param1) : gettype($param1))
					));
		}
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setTime() for the current time
	 *
	 *	@link	http://php.net/manual/en/datetime.settime.php
	 *
	 *	`Resets the current time of the DateTime object to a different time.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setTime($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d):(\d\d):(\d\d)~', $param1, $values) === 1)
			{
				$this->current->setTime($values[1], $values[2], $values[3]);
				return $this;
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setTime(); expecting `HH:MM:SS`");
			}
		}
		$this->current->setTime($param1, $param2, $param3);
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setTime() for the start time
	 *
	 *	@link	http://php.net/manual/en/datetime.settime.php
	 *
	 *	`Resets the current time of the DateTime object to a different time.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setStartTime($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d):(\d\d):(\d\d)~', $param1, $values) === 1)
			{
				$this->start->setTime($values[1], $values[2], $values[3]);
				return $this;
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setStartTime(); expecting `HH:MM:SS`");
			}
		}
		$this->start->setTime($param1, $param2, $param3);
		return $this;
	}

	/**
	 *	Wrapper around \DateTime->setTime() for the end time
	 *
	 *	@link	http://php.net/manual/en/datetime.settime.php
	 *
	 *	`Resets the current time of the DateTime object to a different time.`
	 *
	 *	@param  int|string	$param1
	 *	@param  int|null	$param2
	 *	@param  int|null	$param3
	 *	@return $this
	 */
	public function setEndTime($param1, $param2 = null, $param3 = null)
	{
		if (is_string($param1) && $param2 === null && $param3 === null)
		{
			if (preg_match('~(\d\d):(\d\d):(\d\d)~', $param1, $values) === 1)
			{
				$this->end->setTime($values[1], $values[2], $values[3]);
				return $this;
			}
			else
			{
				throw new InvalidArgumentException("Invalid string format `{$param1}` passed to setEndTime(); expecting `HH:MM:SS`");
			}
		}
		$this->end->setTime($param1, $param2, $param3);
		return $this;
	}

	/**
	 *	
	 *
	 *	@link	http://php.net/manual/en/class.dateinterval.php
	 *
	 *	@param  string|DateInterval $interval_spec
	 *	@return $this
	 */
	public function setInterval($interval_spec)
	{
		$this->interval = is_string($interval_spec) ? new \DateInterval($this->interval) : $interval_spec;
		return $this;
	}

	/**
	 *	Sets the current date timezone
	 *
	 *	@link	http://php.net/manual/en/datetime.settimezone.php
	 *
	 *	@param  string|DateTimeZone $timezone
	 *	@return $this
	 */
	public function setTimezone($timezone = 'UTC')
	{
		$this->current->setTimezone(is_string($timezone) ? new \DateTimeZone($timezone) : $timezone);
		return $this;
	}

	/**
	 *	Sets the start date timezone
	 *
	 *	@link	http://php.net/manual/en/datetime.settimezone.php
	 *
	 *	@param  string|DateTimeZone $timezone
	 *	@return $this
	 */
	public function setStartTimezone($timezone = 'UTC')
	{
		$this->start->setTimezone(is_string($timezone) ? new \DateTimeZone($timezone) : $timezone);
		return $this;
	}

	/**
	 *	Sets the end date timezone
	 *
	 *	@link	http://php.net/manual/en/datetime.settimezone.php
	 *
	 *	@param  string|DateTimeZone $timezone
	 *	@return $this
	 */
	public function setEndTimezone($timezone = 'UTC')
	{
		$this->end->setTimezone(is_string($timezone) ? new \DateTimeZone($timezone) : $timezone);
		return $this;
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
		$this->current->setTimestamp($unixtimestamp);
		return $this;
	}

	/**
	 *	Sets the date and time based on a Unix timestamp.
	 *
	 *	@link	http://php.net/manual/en/datetime.settimestamp.php
	 *
	 *	@param  int $unixtimestamp
	 *	@return $this
	 */
	public function setStartTimestamp($unixtimestamp)
	{
		$this->start->setTimestamp($unixtimestamp);
		return $this;
	}

	/**
	 *	Sets the date and time based on a Unix timestamp.
	 *
	 *	@link	http://php.net/manual/en/datetime.settimestamp.php
	 *
	 *	@param  int $unixtimestamp
	 *	@return $this
	 */
	public function setEndTimestamp($unixtimestamp)
	{
		$this->end->setTimestamp($unixtimestamp);
		return $this;
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
		return $this->end->diff($this->current)->format('%r%a') <= 0;
	}


	/**
	 *	Wrapper for DateTime::getTimestamp()
	 *
	 *	`Gets the Unix timestamp.`
	 *
	 *	@link	http://php.net/manual/en/datetime.gettimestamp.php
	 *
	 *	`Returns the Unix timestamp representing the date.`
	 */
	public function getTimestamp()
	{
		return $this->current->getTimestamp();
	}
	public function getStartTimestamp()
	{
		return $this->start->getTimestamp();
	}
	public function getEndTimestamp()
	{
		return $this->end->getTimestamp();
	}


	/**
	 *	Wrapper for DateTime::getOffset()
	 *
	 *	`Returns the timezone offset.`
	 *
	 *	@link	http://php.net/manual/en/datetime.getoffset.php
	 *
	 *	`Returns the timezone offset in seconds from UTC on success or FALSE on failure.`
	 */
	public function getOffset()
	{
		return $this->current->getOffset();
	}
	public function getStartOffset()
	{
		return $this->start->getOffset();
	}
	public function getEndOffset()
	{
		return $this->end->getOffset();
	}


	public function getYear()
	{
		return $this->current->format('Y');
	}

	public function getMonth()
	{
		return $this->current->format('m');
	}

	public function getDay()
	{
		return $this->current->format('d');
	}


	public function getHour()
	{
		return $this->current->format('H');
	}

	public function getMinute()
	{
		return $this->current->format('i');
	}

	public function getSecond()
	{
		return $this->current->format('s');
	}

	public function getHours()		//	@alias getHour()
	{
		return $this->current->format('H');
	}

	public function getMinutes()	//	@alias getMinute()
	{
		return $this->current->format('i');
	}

	public function getSeconds()	//	@alias getSecond()
	{
		return $this->current->format('s');
	}


	/**
	 *	'D' == A textual representation of a day, three letters									Mon through Sun
	 *	'l' == A full textual representation of the day of the week								Sunday through Saturday
	 *	'N' == ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)		1 (for Monday) through 7 (for Sunday)
	 *	'w' == Numeric representation of the day of the week									0 (for Sunday) through 6 (for Saturday)
	 */
	public function getDayOfWeek($format = 'D')
	{
		return $this->current->format($format);
	}


	public function getStartYear()
	{
		return $this->start->format('Y');
	}

	public function getStartMonth()
	{
		return $this->start->format('m');
	}

	public function getStartDay()
	{
		return $this->start->format('d');
	}


	public function getStartHour()
	{
		return $this->start->format('H');
	}

	public function getStartMinute()
	{
		return $this->start->format('i');
	}

	public function getStartSecond()
	{
		return $this->start->format('s');
	}

	public function getStartHours()		//	@alias getStartHour()
	{
		return $this->start->format('H');
	}

	public function getStartMinutes()	//	@alias getStartMinute()
	{
		return $this->start->format('i');
	}

	public function getStartSeconds()	//	@alias getStartSecond()
	{
		return $this->start->format('s');
	}


	public function getEndYear()
	{
		return $this->end->format('Y');
	}

	public function getEndMonth()
	{
		return $this->end->format('m');
	}

	public function getEndDay()
	{
		return $this->end->format('d');
	}


	public function getEndHour()
	{
		return $this->end->format('H');
	}

	public function getEndMinute()
	{
		return $this->end->format('i');
	}

	public function getEndSecond()
	{
		return $this->end->format('s');
	}

	public function getEndHours()		//	@alias getEndHour()
	{
		return $this->end->format('H');
	}

	public function getEndMinutes()		//	@alias getEndMinute()
	{
		return $this->end->format('i');
	}

	public function getEndSeconds()		//	@alias getEndSecond()
	{
		return $this->end->format('s');
	}



	/**
	 *	Set start and end day
	 *
	 *
	 * @param  string $start  start date
	 * @param  string $end    end   date
	 * @return true/false if the date is between a date range ...
	 */
	public function between($start, $end)
	{
		trigger_error('Function ' . __METHOD__ . ' not implemented yet');
	}


	function __get($name)
	{
		switch ($name)
		{
			case 'year':	return $this->current->format('Y');
			case 'month':	return $this->current->format('m');
			case 'day':		return $this->current->format('d');
		}

		if (strlen($name) === 1)
			return $this->current->format($name);

		if ( ! ctype_lower($name))
			$name = strtolower($name);

		//	@link	http://www.tutorialspoint.com/mysql/mysql-date-time-functions.htm
		//	@link	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html

		switch ($name)
		{
			case 'dayname':			return $this->current->format('l');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayname			MySQL: Returns the name of the weekday for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'dayofweek':		return $this->current->format('w') + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofweek			MySQL: 1 = Sunday, 2 = Monday, …, 7 = Saturday		'w' = 0 (for Sunday) through 6 (for Saturday)
			case 'dayofmonth':		return $this->current->format('j');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofmonth		MySQL: Returns the day of the month for date, in the range 1 to 31, or 0 for dates such as '0000-00-00' or '2008-00-00' that have a zero day part.
			case 'dayofyear':		return $this->current->format('z') + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_dayofyear			MySQL: Returns the day of the year for date, in the range 1 to 366.
			case 'monthname':		return $this->current->format('F');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_monthname			MySQL: Returns the full name of the month for date. The language used for the name is controlled by the value of the lc_time_names system variable
			case 'timestamp':		return $this->current->format('U');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_timestamp			MySQL: With a single argument, this function returns the date or datetime expression expr as a datetime value. With two arguments, it adds the time expression expr2 to the date or datetime expression expr1 and returns the result as a datetime value.
			case 'unix_timestamp':	return $this->current->format('U');		//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_unix-timestamp	MySQL: If called with no argument, returns a Unix timestamp (seconds since '1970-01-01 00:00:00' UTC).
			case 'to_days':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_to-days			MySQL: Given a date date, returns a day number (the number of days since year 0).
			case 'utc_date':		return $this->current->format('Y-m-d');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-date			MySQL: Returns the current UTC date as a value in 'YYYY-MM-DD' or YYYYMMDD format, depending on whether the function is used in a string or numeric context.
			case 'utc_time':		return $this->current->format('H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-time			MySQL: Returns the current UTC time as a value in 'HH:MM:SS'
			case 'utc_timestamp':	return $this->current->format('Y-m-d H:i:s');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_utc-timestamp		MySQL: Returns the current UTC date and time as a value in 'YYYY-MM-DD HH:MM:SS' or YYYYMMDDHHMMSS format, depending on whether the function is used in a string or numeric context.
			case 'quarter':			return $this->current->format('m') / 4 + 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_quarter		MySQL: Returns the quarter of the year for date, in the range 1 to 4.
			case 'week':			break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_week				MySQL: This function returns the week number for date. The two-argument form of WEEK() enables you to specify whether the week starts on Sunday or Monday and whether the return value should be in the range from 0 to 53 or from 1 to 53. If the mode argument is omitted, the value of the default_week_format system variable is used. See Section 5.1.5, “Server System Variables”.
			case 'weekday':			return $this->current->format('N') - 1;	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekday			MySQL: Returns the weekday index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
			case 'weekofyear':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_weekofyear		MySQL: Returns the calendar week of the date as a number in the range from 1 to 53. WEEKOFYEAR() is a compatibility function that is equivalent to WEEK(date,3).
			case 'yearweek':		break;									//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_yearweek			MySQL: Returns year and week for a date. The year in the result may be different from the year in the date argument for the first and the last week of the year.
			case 'date':			return $this->current->format('Y-m-d');	//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_date				MySQL: Extracts the date part of the date or datetime expression expr.

				throw new \Exception('TODO: Property __get(`' . $name . '`) not implemented yet');

			//	strtolower($name) versions
			case 'year':		return $this->current->format('Y');
			case 'month':		return $this->current->format('m');
			case 'day':			return $this->current->format('d');

			case 'hour':		return $this->current->format('G');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour			MySQL: Returns the hour for time. The range of the return value is 0 to 23 for time-of-day values. However, the range of TIME values actually is much larger, so HOUR can return values greater than 23.
			case 'minute':		return $this->current->format('i');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_minute		MySQL: Returns the minute for time, in the range 0 to 59.
			case 'second':		return $this->current->format('s');			//	https://dev.mysql.com/doc/refman/5.7/en/date-and-time-functions.html#function_hour			MySQL: Returns the second for time, in the range 0 to 59.
		}
	}


	function __set($name, $value)
	{
		switch ($name)
		{
			case 'current':

				if (is_string($value)) {
					$this->current = new \DateTime($value, self::$utc);																	//	all roads lead to this ...
				}
				else if ($value instanceof \Twister\DateTime || $value instanceof \Twister\Date) {
					$this->current = new \DateTime((string) $value, self::$utc);
				}
				else if ($value instanceof \DateTime) {
					$this->current = $value;
				}
				else if (is_object($value)) {
					if ( ! method_exists($value, '__toString'))
						throw new InvalidArgumentException('Current date object must have a __toString method');

					$this->current = new \DateTime((string) $value, self::$utc);
				}
				else {
					throw new InvalidArgumentException(sprintf(
								'Invalid type passed to __set(`' . $name . '`); expecting a string or DateTime object, received "%s"',
								(is_object($value) ? get_class($value) : gettype($value))
							));
				}

				return $value;

			case 'start':

				if (is_string($value)) {
					$this->start = new \DateTime($value, self::$utc);																	//	all roads lead to this ...
				}
				else if ($value instanceof \Twister\DateTime || $value instanceof \Twister\Date) {
					$this->start = new \DateTime((string) $value, self::$utc);
				}
				else if ($value instanceof \DateTime) {
					$this->start = $value;
				}
				else if (is_object($value)) {
					if ( ! method_exists($value, '__toString'))
						throw new InvalidArgumentException('Starting date object must have a __toString method');

					$this->start = new \DateTime((string) $value, self::$utc);
				}
				else {
					throw new InvalidArgumentException(sprintf(
								'Invalid type passed to __set(`' . $name . '`); expecting a string or DateTime object, received "%s"',
								(is_object($value) ? get_class($value) : gettype($value))
							));
				}

				return $value;

			case 'end':

				if (is_string($value)) {
					$this->end = new \DateTime($value, self::$utc);																	//	all roads lead to this ...
				}
				else if ($value instanceof \Twister\DateTime || $value instanceof \Twister\Date) {
					$this->end = new \DateTime((string) $value, self::$utc);
				}
				else if ($value instanceof \DateTime) {
					$this->end = $value;
				}
				else if (is_object($value)) {
					if ( ! method_exists($value, '__toString'))
						throw new InvalidArgumentException('End date object must have a __toString method');

					$this->end = new \DateTime((string) $value, self::$utc);
				}
				else {
					throw new InvalidArgumentException(sprintf(
								'Invalid type passed to __set(`' . $name . '`); expecting a string or DateTime object, received "%s"',
								(is_object($value) ? get_class($value) : gettype($value))
							));
				}

				return $value;

			case 'interval':

				if (is_string($value))
				{
					if ($value !== '' && $value[0] === 'P')
						$this->interval	= new \DateInterval($value);
					else
						throw new InvalidArgumentException(
									"Invalid DateInterval passed to __set(`{$name}`); expecting a valid DateInterval string starting with `P` or null, received `{$value}`"
								);
				}
				else if ($value instanceof \DateInterval)
				{
					$this->interval	= $value;
				}
				else if ($value instanceof \Twister\DatePeriod || $value instanceof \DatePeriod)
				{
					$this->interval	= $value->getInterval();
				}
				else if ($value === null)
				{
					$this->interval	= self::$p1d;
				}
				else
				{
					throw new InvalidArgumentException(sprintf(
								'Invalid type passed to __set(`' . $name . '`); expecting a valid DateInterval object or string, received "%s"',
								(is_object($value) ? get_class($value) : gettype($value))
							));
				}

				return $value;

			case 'timezone':
			case 'timestamp':

			case 'year':
			case 'month':
			case 'day':

			case 'hour':
			case 'minute':
			case 'second':
		}

		throw new \Exception('Property __set(`' . $name . '`) not implemented yet');
	}

}

DatePeriod::$utc = new \DateTimeZone('UTC');
DatePeriod::$p1d = new \DateInterval('P1D');
