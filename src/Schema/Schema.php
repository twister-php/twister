<?php

//Twister\Schema::worlds()->id->min
//Twister\Schema::users()->fields()

namespace Twister;

class Schema
{
	private static $tables	=	null;
	private static $db		=	null;
	private static $schema	=	'DATABASE()';
	private static $cache	=	null;

	//	Set db to MySQL Connection object - if not set, then procedural style will be used
	public static function setConn($conn)
	{
		self::$db = $conn;
	}

	//	Set the database schema name
	public static function setSchema($schema)
	{
		self::$schema = $schema != 'DATABASE()' ? '"' . $schema . '"' : 'DATABASE()';
	}

	//	Set the schema cache directory, where we can store files for the composer autoloader
	public static function setCache($cache)
	{
		self::$cache = $cache;
	}

	public static function __callStatic(string $table, array $args)
	{
		if ( ! isset($tables[$table]))
		{
			try
			{
				$class = 'Twister\\Schema\\' . self::toPascalCase($table);
				$tables[$table] = new $class();
			}
			catch (\Exception $e)
			{
				$tables[$table] = self::build($table);
			}
		}
		return $tables[$table];
	}

	/**
	 *	Converts $table to PascalCase, preserving leading and trailing underscores '_'
	 *	eg. nation_capitals__ => NationCapitals__
	 */
	private static function toPascalCase($table)
	{
		return str_repeat('_', strspn($table, '_')) . str_replace('_', '', ucwords($table, '_')) . str_repeat('_', strspn(strrev($table), '_'));
	}

	public static function build($table = null, $db = null, string $cache = null)
	{
		mysqli_report( MYSQLI_REPORT_STRICT );

		$cache = $cache ?: self::$cache;

		static $methods = null;
		if ($methods === null)
		{
		//	here for potential caching possibilities !?!? Hopefully PHP can set an internal ref-counter and not re-define the functions each time!
			self::$methods =	[	'date'			=>	function ($type, string $value) { return $value === null && $type->nullable || preg_match('~^\d\d\d\d-\d\d-\d\d$~', $value) === 1; },	//	TODO: Add DateTime object validation! ie. instanceof DateTime
									'datetime'		=>	function ($type, string $value) { return $value === null && $type->nullable || preg_match('~^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$~', $value) === 1; },
									'time'			=>	function ($type, string $value) { return $value === null && $type->nullable || preg_match('~^\d\d:\d\d:\d\d$~', $value) === 1; },
									'time_ex'		=>	function ($type, string $value) { return $value === null && $type->nullable || preg_match('~^-?\d?\d\d:\d\d:\d\d$~', $value) === 1; },	//	From: http://www.mysqltutorial.org/mysql-time/    A TIME value ranges from -838:59:59 to 838:59:59. In addition, a TIME value can have fractional seconds part that is up to microseconds precision (6 digits). To define a column whose data type is TIME with a fractional second precision part, you use the following syntax: column_name TIME(N);
									'year'			=>	function ($type, string $value) { return $value === null && $type->nullable || preg_match('~^\d\d\d\d$~', $value) === 1; },
									'enum'			=>	function ($type, string $value) { return in_array($value, $type->members); },
									'int'			=>	function ($type, $value) { return $value === null && $type->nullable || is_numeric($value) && $value >= $type->min && $value <= $type->max; },
									'float'			=>	function ($type, $value) { return $value === null && $type->nullable || is_numeric($value); },
									'string'		=>	function ($type, $value) { return $value === null && $type->nullable || is_string($value) && ($type->charset); },
								//	'[0,1]'			=>	function ($type, $value) { return $value === null && $type->nullable || is_numeric($value) && $value >= 0.0 && $value <= 1.0; },	//	HYPOTHETICAL 'bit'!
									'clamp'			=>	function ($type, $value) { return isset($value) && is_numeric($value) ? min(max($value, $type->min), $type->max) : $type->default ?: ($type->nullable ? null : min(max(0, $type->min), $type->max)); },
									'isTrue'		=>	function ($type) { return true; },
									'isFalse'		=>	function ($type) { return false; },
									'isUnsigned'	=>	function ($type) { return $type->min >= 0; }
								];
		}

		$sql = 'SELECT ' .
					'TABLE_NAME,' .
					'COLUMN_NAME,' .
					'DATA_TYPE,' .					//	varchar
					'COLUMN_DEFAULT,' .
				//	'NULLIF(IS_NULLABLE="YES",0),' .//	NULL / 1	alternative
					'NULLIF(IS_NULLABLE,"NO"),' .	//	NULL / YES
				//	'IS_NULLABLE,' .				//	NO / YES
					'COALESCE(CHARACTER_MAXIMUM_LENGTH, NUMERIC_SCALE) AS scale,' .	//	These two columns data NEVER overlap!
				//	'CHARACTER_MAXIMUM_LENGTH,' .
				//	'CHARACTER_OCTET_LENGTH,' .		//	eg. varchar(32)	== CHARACTER_MAXIMUM_LENGTH = 32 && CHARACTER_OCTET_LENGTH = 96
				//	'NUMERIC_PRECISION,' .			//	eg. decimal(9,5) == NUMERIC_PRECISION = 9 && NUMERIC_SCALE = 5
				//	'NUMERIC_SCALE,' .				//	5 (the decimal values, or 0 for integers)
					'CHARACTER_SET_NAME,' .			//	utf8		(binary == null)
				//	'COLLATION_NAME,' .				//	utf8_general_ci
					'COLUMN_TYPE' .					//	int(10) unsigned / enum('','right','left')
				//	'COLUMN_KEY,' .					//	PRI / UNI / MUL
				//	'EXTRA' .						//	auto_increment / on update CURRENT_TIMESTAMP
				' FROM INFORMATION_SCHEMA.COLUMNS' .
				' WHERE TABLE_SCHEMA = DATABASE()' . ($table ? (is_array($table) ? ' AND TABLE_NAME IN ("' . implode('","', $table) . '")' : ' AND TABLE_NAME = "' . $table . '"') : null);
				' ORDER BY TABLE_NAME, ORDINAL_POSITION';

		$result = null;

		$rows = $db->query($sql)->fetch_all(MYSQLI_NUM); // MYSQLI_ASSOC || MYSQLI_NUM
		foreach ($rows as $row)
		{
			//$field = [$row[2], $row[3], $row[4] !== null];
			//... would have added more to the array ... but decided to make this realtime only for now!
			//$result[$row[0]][$row[1]] = &$field;
			$unsigned = strpos($row[7], 'unsigned') !== false;
			switch($row[2])
			{	//	5383 / 5996 fields (406 tables) are NOT nullable (nullable = 11%, NON-nullable = 89%) && 3022 fields have defaults (50%, 658 fields default is empty string, 1752 fields default = 0)
				case 'int':			$obj = new Type(['int',			$row[3], $row[4] !== null, 'min' => $unsigned ? 0 : -2147483648, 'max' => $unsigned ? 4294967295 : 2147483647, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'tinyint':		$obj = new Type(['tinyint',		$row[3], $row[4] !== null, 'min' => $unsigned ? 0 : -128, 'max' => $unsigned ? 255 : 127, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'float':		$obj = new Type(['float',		$row[3], $row[4] !== null, 'precision' => $row[5], 'isValid' => $methods['float']]); break;
				case 'varchar':		$obj = new Type(['varchar',		$row[3], $row[4] !== null, 'maxlen' => $row[5], 'charset' => $row[6]]); break;
				case 'smallint':	$obj = new Type(['smallint',	$row[3], $row[4] !== null, 'min' => $unsigned ? 0 : -32768, 'max' => $unsigned ? 65535 : 32767, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'enum':		$obj = new Type(['enum',		$row[3], $row[4] !== null, 'members' => self::convertToArray($row[7]), 'isValid' => $methods['enum']]); break;
				case 'mediumint':	$obj = new Type(['mediumint',	$row[3], $row[4] !== null, 'min' => $unsigned ? 0 : -8388608, 'max' => $unsigned ? 16777215 : 8388607, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'date':		$obj = new Type(['date',		$row[3], $row[4] !== null, 'isValid' => $methods['date']]); break;
				case 'bit':			$obj = new Type(['bit',			$row[3] === null ? null : ($row[3] === "b'0'" ? 0 : 1), $row[4] !== null, 0, 1, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'char':		$obj = new Type(['char',		$row[3], $row[4] !== null, 'maxlen' => $row[5], 'charset' => $row[6]]); break;
				case 'text':		$obj = new Type(['text',		$row[3], $row[4] !== null, 'maxlen' => 65535, 'charset' => $row[6]]); break;
				case 'timestamp':	$obj = new Type(['timestamp',	$row[3] === 'CURRENT_TIMESTAMP' ? null : $row[3], $row[4] !== null, 'isValid' => $methods['datetime']]); break;
				case 'binary':		$obj = new Type(['binary',		$row[3], $row[4] !== null, 'maxlen' => $row[5], 'charset' => null]); break;
				case 'double':		$obj = new Type(['double',		$row[3], $row[4] !== null, 'precision' => $row[5], 'isValid' => $methods['float']]); break;
				case 'tinytext':	$obj = new Type(['tinytext',	$row[3], $row[4] !== null, 'maxlen' => 255, 'charset' => $row[6]]); break;
				case 'set':			$obj = new Type(['set',			$row[3], $row[4] !== null, self::convertToArray($row[7])]); break;
				case 'decimal':		$obj = new Type(['decimal',		$row[3], $row[4] !== null, 'precision' => $row[5], 'isValid' => $methods['float']]); break;
				case 'year':		$obj = new Type(['year',		$row[3], $row[4] !== null, 'min' => 1970, 'max' => 2070, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break; // 4-digit format = 1901 to 2155, or 0000.
				case 'varbinary':	$obj = new Type(['varbinary',	$row[3], $row[4] !== null, 'maxlen' => $row[5], 'charset' => null]); break;
				case 'bigint':		$obj = new Type(['bigint',		$row[3], $row[4] !== null, 'min' => $unsigned ? 0 : -9223372036854775808, 'max' => $unsigned ? 18446744073709551615 : 9223372036854775807, 'isValid' => $methods['int'], 'clamp' => $methods['clamp']]); break;
				case 'datetime':	$obj = new Type(['datetime',	$row[3], $row[4] !== null, 'isValid' => $methods['datetime']]); break;
				case 'time':		$obj = new Type(['time',		$row[3], $row[4] !== null, 'isValid' => $methods['time']]); break;
				case 'mediumtext':	$obj = new Type(['mediumtext',	$row[3], $row[4] !== null, 'maxlen' => 16777215, 'charset' => $row[6]]); break;
				case 'longblob':	$obj = new Type(['longblob',	$row[3], $row[4] !== null, 'maxlen' => 4294967295, 'charset' => $row[6]]); break;
				case 'mediumblob':	$obj = new Type(['mediumblob',	$row[3], $row[4] !== null, 'maxlen' => 16777215, 'charset' => $row[6]]); break;
				case 'numeric':		$obj = new Type(['numeric',		$row[3], $row[4] !== null, 'precision' => $row[5], 'isValid' => $methods['float']]); break;
				case 'blob':		$obj = new Type(['blob',		$row[3], $row[4] !== null, 'maxlen' => 65535, 'charset' => $row[6]]); break;
				case 'tinyblob':	$obj = new Type(['tinyblob',	$row[3], $row[4] !== null, 'maxlen' => 255, 'charset' => $row[6]]); break;
				case 'longtext':	$obj = new Type(['longtext',	$row[3], $row[4] !== null, 'maxlen' => 4294967295, 'charset' => $row[6]]); break;
			}
			$result[$row[0]][$row[1]] = $obj;
		}

		if ( ! empty($cache))
		{
			
		}

		return is_string($table) ? $result[$table] : $result;
	}

	//	Converts enum() and set() to array
	public static function convertToArray($value)
	{
		return explode('\',\'', substr($value, strpos($value, '(') + 2, -2));
	}
}
