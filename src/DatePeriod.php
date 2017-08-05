<?php

namespace Twister;

use \DateTime;					//	http://php.net/manual/en/class.datetime.php
use \DateInterval;				//	http://php.net/manual/en/class.dateinterval.php
use \DateTimeZone;				//	http://php.net/manual/en/class.datetimezone.php
use \DatePeriod;				//	http://php.net/manual/en/class.dateperiod.php

echo 'here 1';

class DatePeriod extends \DatePeriod
{
	public static $utc	= null;
	public static $p1d	= null;

	public function __construct($start, $interval_spec = null, $end = '9999-12-31')
	{
		parent::__construct(	is_string($start) ? new \DateTime($start, self::$utc) : $start,
								$interval_spec === null ? self::$p1d : (is_string($interval_spec) ? new \DateInterval($interval_spec) : $interval_spec),
								is_string($end) ? new \DateTime($end, self::$utc) : $end
							);
	}

	public function next()
	{
trigger_error('HERE');
		$this->current->add($this->interval);
		return $this;
	}
}

DatePeriod::$utc = new \DateTimeZone('UTC');
DatePeriod::$p1d = new \DateInterval('P1D');
