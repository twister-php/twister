<?php

/**
 *	Immutable!
 */

namespace Twister\DBAL\Types;

abstract class BaseType	//	AKA Type (Doctrine), Primitive, Core, Base
{
	const TYPE_INT			=	0;
	const TYPE_TINYINT		=	1;
	const TYPE_SMALLINT		=	2;
	const TYPE_MEDIUMINT	=	3;
	const TYPE_BIGINT		=	4;
	const TYPE_BIT			=	5;
	const TYPE_FLOAT		=	6;
	const TYPE_DOUBLE		=	7;	//	AKA REAL
	const TYPE_DECIMAL		=	8;
	const TYPE_NUMERIC		=	9;	//	`In MySQL, DECIMAL(M,D) and NUMERIC(M,D) are the same, and both have a precision of exactly M digits.`
	const TYPE_TIMESTAMP	=	10;
	const TYPE_DATETIME		=	11;
	const TYPE_DATE			=	12;
	const TYPE_TIME			=	13;
	const TYPE_YEAR			=	14;
	const TYPE_VARCHAR		=	15;	//	AKA NVARCHAR
	const TYPE_CHAR			=	16;
	const TYPE_BLOB			=	17;
	const TYPE_TINYBLOB		=	18;
	const TYPE_MEDIUMBLOB	=	19;
	const TYPE_LONGBLOB		=	20;
	const TYPE_TEXT			=	21;
	const TYPE_TINYTEXT		=	22;
	const TYPE_MEDIUMTEXT	=	23;
	const TYPE_LONGTEXT		=	24;
	const TYPE_BINARY		=	25;
	const TYPE_VARBINARY	=	26;
	const TYPE_ENUM			=	27;
	const TYPE_SET			=	28;

	static $primitives		=	[	'int',
									'tinyint',
									'smallint',
									'mediumint',
									'bigint',
									'bit',
									'float',
									'double',
									'decimal',
									'numeric',
									'timestamp',
									'datetime',
									'date',
									'time',
									'year',
									'varchar',
									'char',
									'blob',
									'tinyblob',
									'mediumblob',
									'longblob',
									'text',
									'tinytext',
									'mediumtext',
									'longtext',
									'binary',
									'varbinary',
									'enum',
									'set'
								];

	public $type		=	null;
	public $default		=	null;
	public $nullable	=	null;

//	$flags			= null;		//	We might have to use `flags` if we want to support more than just `nullable` eg. `binary`, `primary_key`, `unique_key`, `unsigned`, `auto_increment` etc.
	/*
	static $flag_types = array(
					MYSQLI_NUM_FLAG				=>	'numeric',			//	Field is defined as NUMERIC
					MYSQLI_PART_KEY_FLAG		=>	'part_key',			//	Field is part of an multi-index
					MYSQLI_SET_FLAG				=>	'set',				//	Field is defined as SET
					MYSQLI_TIMESTAMP_FLAG		=>	'timestamp',		//	Field is defined as TIMESTAMP
					MYSQLI_AUTO_INCREMENT_FLAG	=>	'auto_increment',	//	Field is defined as AUTO_INCREMENT
					MYSQLI_ENUM_FLAG			=>	'enum',				//	Field is defined as ENUM. Available since PHP 5.3.0.
					MYSQLI_BINARY_FLAG			=>	'binary',			//	Field is defined as BINARY. Available since PHP 5.3.0.
					MYSQLI_GROUP_FLAG			=>	'group_by',			//	Field is part of GROUP BY
					MYSQLI_ZEROFILL_FLAG		=>	'zerofill',			//	Field is defined as ZEROFILL
					MYSQLI_UNSIGNED_FLAG		=>	'unsigned',			//	Field is defined as UNSIGNED
					MYSQLI_BLOB_FLAG			=>	'blob',				//	Field is defined as BLOB
					MYSQLI_MULTIPLE_KEY_FLAG	=>	'multiple_key',		//	Field is part of an index.
					MYSQLI_UNIQUE_KEY_FLAG		=>	'unique_key',		//	Field is part of a unique index.
					MYSQLI_PRI_KEY_FLAG			=>	'primary_key',		//	Field is part of a primary index
					MYSQLI_NOT_NULL_FLAG		=>	'not_null'			//	Indicates that a field is defined as NOT NULL
				);

	//	alternative
	const FLAG_NULLABLE		=	MYSQLI_NOT_NULL_FLAG;
	const FLAG_PRIMARY_KEY	=	MYSQLI_PRI_KEY_FLAG;
	const FLAG_UNIQUE		=	MYSQLI_UNIQUE_KEY_FLAG;
	const FLAG_INDEX		=	MYSQLI_MULTIPLE_KEY_FLAG;	//	or MYSQLI_PART_KEY_FLAG ???
	const FLAG_AUTO			=	MYSQLI_AUTO_INCREMENT_FLAG;
	const FLAG_UNSIGNED		=	MYSQLI_UNSIGNED_FLAG;
	*/

//	public $validator		= null;		// callback funtion! AKA $valid ... or we make `valid()` a function we call which internally calls this function!?!?
//	public $escape			= null;		// callback funtion!
//	public $range			= null;		// can be: array(0, 255) ... or an array of enums eg. array('US','UK','DE','FR') etc. AKA dimentions AKA attributes AKA properties
//	public $value			= null;		// do we store the value here ??? NO ... we don't need ALL this shit for each value, this class will be used in the (entity) model for each column!

	/*
	static $intrinsics = array(
					MYSQLI_TYPE_DECIMAL		=>	'decimal',		//	Field is defined as DECIMAL
					MYSQLI_TYPE_NEWDECIMAL	=>	'numeric',		//	Precision math DECIMAL or NUMERIC field (MySQL 5.0.3 and up)
					MYSQLI_TYPE_BIT			=>	'bit',			//	Field is defined as BIT (MySQL 5.0.3 and up)
					MYSQLI_TYPE_TINY		=>	'tinyint',		//	Field is defined as TINYINT
					MYSQLI_TYPE_SHORT		=>	'smallint',		//	Field is defined as SMALLINT
					MYSQLI_TYPE_LONG		=>	'int',			//	Field is defined as INT
					MYSQLI_TYPE_FLOAT		=>	'float',		//	Field is defined as FLOAT
					MYSQLI_TYPE_DOUBLE		=>	'double',		//	Field is defined as DOUBLE
					MYSQLI_TYPE_NULL		=>	'null',			//	Field is defined as DEFAULT NULL
					MYSQLI_TYPE_TIMESTAMP	=>	'timestamp',	//	Field is defined as TIMESTAMP
					MYSQLI_TYPE_LONGLONG	=>	'bigint',		//	Field is defined as BIGINT
					MYSQLI_TYPE_INT24		=>	'mediumint',	//	Field is defined as MEDIUMINT
					MYSQLI_TYPE_DATE		=>	'date',			//	Field is defined as DATE
					MYSQLI_TYPE_TIME		=>	'time',			//	Field is defined as TIME
					MYSQLI_TYPE_DATETIME	=>	'datetime',		//	Field is defined as DATETIME
					MYSQLI_TYPE_YEAR		=>	'year',			//	Field is defined as YEAR
					MYSQLI_TYPE_NEWDATE		=>	'date',			//	Field is defined as DATE
					MYSQLI_TYPE_ENUM		=>	'enum',			//	Field is defined as ENUM
					MYSQLI_TYPE_SET			=>	'set',			//	Field is defined as SET
					MYSQLI_TYPE_TINY_BLOB	=>	'tinyblob',		//	Field is defined as TINYBLOB
					MYSQLI_TYPE_MEDIUM_BLOB	=>	'mediumblob',	//	Field is defined as MEDIUMBLOB
					MYSQLI_TYPE_LONG_BLOB	=>	'longblob',		//	Field is defined as LONGBLOB
					MYSQLI_TYPE_BLOB		=>	'blob',			//	Field is defined as BLOB (& TEXT)
					MYSQLI_TYPE_VAR_STRING	=>	'varchar',		//	Field is defined as VARCHAR
					MYSQLI_TYPE_STRING		=>	'char',			//	Field is defined as CHAR or BINARY
					// MySQL returns MYSQLI_TYPE_STRING for CHAR
					// and MYSQLI_TYPE_CHAR === MYSQLI_TYPE_TINY
					// so this would override TINYINT and mark all TINYINT as string
					// https://sourceforge.net/p/phpmyadmin/bugs/2205/
					MYSQLI_TYPE_CHAR		=>	'tinyint',		//	Field is defined as TINYINT. For CHAR, see MYSQLI_TYPE_STRING
					MYSQLI_TYPE_GEOMETRY	=>	'geometry'		//	Field is defined as GEOMETRY
				);

				//	http://dev.mysql.com/doc/refman/5.7/en/json.html
				//	As of MySQL 5.7.8, MySQL supports a native JSON data type
				//	'json'

				//	http://dev.mysql.com/doc/refman/5.7/en/spatial-datatypes.html
				//	'geometry'
				//	'linestring'
				//	'polygon'
				//	'multipoint'
				//	'multilinestring'
				//	'multipolygon'
				//	'geometrycollection'
				//	'polygon'
				//	'point'			// WARNING: `point` data type doesn't have `(` !!! So we will have to fix some assumptions in the code!

	private static $_type_classes = array(
					MYSQLI_TYPE_DECIMAL		=>	primitive::T_FLOAT_CLASS,	//	Field is defined as DECIMAL
					MYSQLI_TYPE_NEWDECIMAL	=>	primitive::T_FLOAT_CLASS,	//	Precision math DECIMAL or NUMERIC field (MySQL 5.0.3 and up)
					MYSQLI_TYPE_BIT			=>	primitive::T_GENERIC_CLASS,	//	Field is defined as BIT (MySQL 5.0.3 and up)
					MYSQLI_TYPE_TINY		=>	primitive::T_INTEGER_CLASS,	//	Field is defined as TINYINT
					MYSQLI_TYPE_SHORT		=>	primitive::T_INTEGER_CLASS,	//	Field is defined as SMALLINT
					MYSQLI_TYPE_LONG		=>	primitive::T_INTEGER_CLASS,	//	Field is defined as INT
					MYSQLI_TYPE_FLOAT		=>	primitive::T_FLOAT_CLASS,	//	Field is defined as FLOAT
					MYSQLI_TYPE_DOUBLE		=>	primitive::T_FLOAT_CLASS,	//	Field is defined as DOUBLE
					MYSQLI_TYPE_NULL		=>	null,						//	Field is defined as DEFAULT NULL
					MYSQLI_TYPE_TIMESTAMP	=>	primitive::T_STRING_CLASS,	//	Field is defined as TIMESTAMP
					MYSQLI_TYPE_LONGLONG	=>	primitive::T_GENERIC_CLASS,	//	Field is defined as BIGINT
					MYSQLI_TYPE_INT24		=>	primitive::T_INTEGER_CLASS,	//	Field is defined as MEDIUMINT
					MYSQLI_TYPE_DATE		=>	primitive::T_STRING_CLASS,	//	Field is defined as DATE
					MYSQLI_TYPE_TIME		=>	primitive::T_STRING_CLASS,	//	Field is defined as TIME
					MYSQLI_TYPE_DATETIME	=>	primitive::T_STRING_CLASS,	//	Field is defined as DATETIME
					MYSQLI_TYPE_YEAR		=>	primitive::T_GENERIC_CLASS,	//	Field is defined as YEAR
					MYSQLI_TYPE_NEWDATE		=>	primitive::T_STRING_CLASS,	//	Field is defined as DATE
					MYSQLI_TYPE_ENUM		=>	primitive::T_ARRAY_CLASS,	//	Field is defined as ENUM			<= PROBLEM HERE! ENUM() is classified as a STRING type and thus returns MYSQLI_TYPE_STRING
					MYSQLI_TYPE_SET			=>	primitive::T_ARRAY_CLASS,	//	Field is defined as SET
					MYSQLI_TYPE_TINY_BLOB	=>	primitive::T_STRING_CLASS,	//	Field is defined as TINYBLOB
					MYSQLI_TYPE_MEDIUM_BLOB	=>	primitive::T_STRING_CLASS,	//	Field is defined as MEDIUMBLOB
					MYSQLI_TYPE_LONG_BLOB	=>	primitive::T_STRING_CLASS,	//	Field is defined as LONGBLOB
					MYSQLI_TYPE_BLOB		=>	primitive::T_STRING_CLASS,	//	Field is defined as BLOB (& TEXT)
					MYSQLI_TYPE_VAR_STRING	=>	primitive::T_STRING_CLASS,	//	Field is defined as VARCHAR
					MYSQLI_TYPE_STRING		=>	primitive::T_STRING_CLASS,	//	Field is defined as CHAR or BINARY
					// MySQL returns MYSQLI_TYPE_STRING for CHAR
					// and MYSQLI_TYPE_CHAR === MYSQLI_TYPE_TINY
					// so this would override TINYINT and mark all TINYINT as string
					// https://sourceforge.net/p/phpmyadmin/bugs/2205/
					MYSQLI_TYPE_CHAR		=>	primitive::T_INTEGER_CLASS,	//	Field is defined as TINYINT. For CHAR, see MYSQLI_TYPE_STRING
					MYSQLI_TYPE_GEOMETRY	=>	null						//	Field is defined as GEOMETRY
				);

	static $flag_types = array(
					MYSQLI_NUM_FLAG				=>	'numeric',			//	Field is defined as NUMERIC
					MYSQLI_PART_KEY_FLAG		=>	'part_key',			//	Field is part of an multi-index
					MYSQLI_SET_FLAG				=>	'set',				//	Field is defined as SET
					MYSQLI_TIMESTAMP_FLAG		=>	'timestamp',		//	Field is defined as TIMESTAMP
					MYSQLI_AUTO_INCREMENT_FLAG	=>	'auto_increment',	//	Field is defined as AUTO_INCREMENT
					MYSQLI_ENUM_FLAG			=>	'enum',				//	Field is defined as ENUM. Available since PHP 5.3.0.
					MYSQLI_BINARY_FLAG			=>	'binary',			//	Field is defined as BINARY. Available since PHP 5.3.0.
					MYSQLI_GROUP_FLAG			=>	'group_by',			//	Field is part of GROUP BY
					MYSQLI_ZEROFILL_FLAG		=>	'zerofill',			//	Field is defined as ZEROFILL
					MYSQLI_UNSIGNED_FLAG		=>	'unsigned',			//	Field is defined as UNSIGNED
					MYSQLI_BLOB_FLAG			=>	'blob',				//	Field is defined as BLOB
					MYSQLI_MULTIPLE_KEY_FLAG	=>	'multiple_key',		//	Field is part of an index.
					MYSQLI_UNIQUE_KEY_FLAG		=>	'unique_key',		//	Field is part of a unique index.
					MYSQLI_PRI_KEY_FLAG			=>	'primary_key',		//	Field is part of a primary index
					MYSQLI_NOT_NULL_FLAG		=>	'not_null'			//	Indicates that a field is defined as NOT NULL
				);
	*/

	function __construct($type, $default, $nullable)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;
	}

	function getType()
	{
		return $this->type;
	}
	function getTypeName()	// TODO
	{
		return $this->type;
	}
	function getName()		// TODO
	{
		return $this->type;
	}

	function getDefault()
	{
		return $this->default;
	}

	function isNullable()
	{
		return $this->nullable;
	}

	function isRequired()
	{
		return !$this->nullable && is_null($this->default);
	}

	function isValid($value)
	{
		return !is_null($value) || $this->nullable;
	}

	/*
	function getFlags()
	{
		return $this->flags;
	}
	*/

	/**
	 * Converts a value from its PHP representation to its database representation
	 * of this type.
	 *
	 * @param mixed                                     $value    The value to convert.
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
	 *
	 * @return mixed The database representation of the value.
	 */
	function toSQL($value)
	{
		return $value;
	}

	/**
	 * Converts a value from its database representation to its PHP representation
	 * of this type.
	 *
	 * @param mixed                                     $value    The value to convert.
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
	 *
	 * @return mixed The PHP representation of the value.
	 */
	function toPHP($value)
	{
		return $value;
	}


	public static function factory(array &$properties)
	{
		/*
			Field Usage Statistics
			SELECT DATA_TYPE, COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "fcm" GROUP BY DATA_TYPE ORDER BY COUNT(*) DESC;
			int			1288
			tinyint		1061
			float		796
			varchar		788
			smallint	384
			enum		361
			mediumint	316
			date		264
			bit			258
			char		219
			text		72
			timestamp	70
			binary		26
			double		25
			tinytext	14
			set			11
			decimal		10
			year		10
			varbinary	9
			bigint		7
			datetime	3
			mediumtext	2
			longblob	1
			mediumblob	1
		*/

		switch ($properties[0])
		{
			case 'int':			return new Integer('int',		$properties[1], $properties[2], $properties[3] === 0 ? 0 : -2147483648, $properties[3] === 0 ? 4294967295 : 2147483647);
			case 'tinyint':		return new Integer('tinyint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -128, $properties[3] === 0 ? 255 : 127);
			case 'float':		return new Float('float',		$properties[1], $properties[2], $properties[3]);
			case 'varchar':		return new String('varchar',	$properties[1], $properties[2], $properties[3], $properties[4]);
			case 'smallint':	return new Integer('smallint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -32768, $properties[3] === 0 ? 65535 : 32767);
			case 'enum':		return new Array('enum',		$properties[1], $properties[2], $properties[3]);
			case 'mediumint':	return new Integer('mediumint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -8388608, $properties[3] === 0 ? 16777215 : 8388607);
			case 'date':		return new Type('date',			$properties[1], $properties[2]);
			case 'bit':			return new Integer('bit',		$properties[1], $properties[2], 0, 1);
			case 'char':		return new String('char',		$properties[1], $properties[2], $properties[3], $properties[4]);
			case 'text':		return new String('text',		$properties[1], $properties[2], 65535, $properties[3]);
			case 'timestamp':	return new Type('timestamp',	$properties[1], $properties[2]);
			case 'binary':		return new String('binary',		$properties[1], $properties[2], $properties[3], 0);
			case 'double':		return new Float('double',		$properties[1], $properties[2], $properties[3]);
			case 'tinytext':	return new String('tinytext',	$properties[1], $properties[2], 255, $properties[3]);
			case 'set':			return new Array('set',			$properties[1], $properties[2], $properties[3]);
			case 'decimal':		return new Float('decimal',		$properties[1], $properties[2], $properties[3]);
			case 'year':		return new Integer('year',		$properties[1], $properties[2], 1970, 2070); // 4-digit format = 1901 to 2155, or 0000.
			case 'varbinary':	return new String('varbinary',	$properties[1], $properties[2], $properties[3], 0);
			case 'bigint':		return new Integer('bigint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -9223372036854775808, $properties[3] === 0 ? 18446744073709551615 : 9223372036854775807);
			case 'datetime':	return new Type('datetime',		$properties[1], $properties[2]);
			case 'mediumtext':	return new String('mediumtext',	$properties[1], $properties[2], 16777215, $properties[3]);
			case 'longblob':	return new String('longblob',	$properties[1], $properties[2], 4294967295, $properties[3]);
			case 'mediumblob':	return new String('mediumblob',	$properties[1], $properties[2], 16777215, $properties[3]);
			case 'numeric':		return new Float('numeric',		$properties[1], $properties[2], $properties[3]);
			case 'time':		return new Type('time',			$properties[1], $properties[2]);
			case 'blob':		return new String('blob',		$properties[1], $properties[2], 65535, $properties[3]);
			case 'tinyblob':	return new String('tinyblob',	$properties[1], $properties[2], 255, $properties[3]);
			case 'longtext':	return new String('longtext',	$properties[1], $properties[2], 4294967295, $properties[3]);
		}
		throw new \Exception('Invalid data-type `' . $properties[0] . '` specified in ' . __METHOD__);
	}
}
