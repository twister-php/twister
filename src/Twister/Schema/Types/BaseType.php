<?php

namespace Twister\Schema\Types;

abstract class BaseType
{
	protected $properties	=	null;
/*
	public function __construct(array $properties)
	{
		$properties['type']		=	&$properties[0];
		$properties['default']	=	&$properties[1];
		$properties['nullable']	=	&$properties[2];

		$this->properties		=&	$properties;
	}

	function __construct($type, $default, $nullable)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;
	}
*/
	/**
	 * Get table field/column property
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->properties[$name];
	}

	/**
	 * Set table field/column property
	 * Values cannot be set, only callables
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ( ! isset($this->properties[$name]) || is_callable($this->properties[$name]))
			$this->properties[$name] = $value;
		else
			throw new \Exception("Cannot set protected property {$name}");
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
			case 'int':			return new IntegerType('int',		$properties[1], $properties[2], $properties[3] === 0 ? 0 : -2147483648, $properties[3] === 0 ? 4294967295 : 2147483647);
			case 'tinyint':		return new IntegerType('tinyint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -128, $properties[3] === 0 ? 255 : 127);
			case 'float':		return new FloatType('float',		$properties[1], $properties[2], $properties[3]);
			case 'varchar':		return new StringType('varchar',	$properties[1], $properties[2], $properties[3], $properties[4]);
			case 'smallint':	return new IntegerType('smallint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -32768, $properties[3] === 0 ? 65535 : 32767);
			case 'enum':		return new ArrayType('enum',		$properties[1], $properties[2], $properties[3]);
			case 'mediumint':	return new IntegerType('mediumint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -8388608, $properties[3] === 0 ? 16777215 : 8388607);
			case 'date':		return new DateType('date',			$properties[1], $properties[2]);
			case 'bit':			return new IntegerType('bit',		$properties[1], $properties[2], 0, 1);
			case 'char':		return new StringType('char',		$properties[1], $properties[2], $properties[3], $properties[4]);
			case 'text':		return new StringType('text',		$properties[1], $properties[2], 65535, $properties[3]);
			case 'timestamp':	return new DateType('timestamp',	$properties[1], $properties[2]);
			case 'binary':		return new StringType('binary',		$properties[1], $properties[2], $properties[3], 0);
			case 'double':		return new FloatType('double',		$properties[1], $properties[2], $properties[3]);
			case 'tinytext':	return new StringType('tinytext',	$properties[1], $properties[2], 255, $properties[3]);
			case 'set':			return new ArrayType('set',			$properties[1], $properties[2], $properties[3]);
			case 'decimal':		return new FloatType('decimal',		$properties[1], $properties[2], $properties[3]);
			case 'year':		return new IntegerType('year',		$properties[1], $properties[2], 1970, 2070); // 4-digit format = 1901 to 2155, or 0000.
			case 'varbinary':	return new StringType('varbinary',	$properties[1], $properties[2], $properties[3], 0);
			case 'bigint':		return new IntegerType('bigint',	$properties[1], $properties[2], $properties[3] === 0 ? 0 : -9223372036854775808, $properties[3] === 0 ? 18446744073709551615 : 9223372036854775807);
			case 'datetime':	return new DateType('datetime',		$properties[1], $properties[2]);
			case 'mediumtext':	return new StringType('mediumtext',	$properties[1], $properties[2], 16777215, $properties[3]);
			case 'longblob':	return new StringType('longblob',	$properties[1], $properties[2], 4294967295, $properties[3]);
			case 'mediumblob':	return new StringType('mediumblob',	$properties[1], $properties[2], 16777215, $properties[3]);
			case 'numeric':		return new FloatType('numeric',		$properties[1], $properties[2], $properties[3]);
			case 'time':		return new DateType('time',			$properties[1], $properties[2]);
			case 'blob':		return new StringType('blob',		$properties[1], $properties[2], 65535, $properties[3]);
			case 'tinyblob':	return new StringType('tinyblob',	$properties[1], $properties[2], 255, $properties[3]);
			case 'longtext':	return new StringType('longtext',	$properties[1], $properties[2], 4294967295, $properties[3]);
		}
		throw new \Exception('Invalid data-type `' . $properties[0] . '` specified in ' . __METHOD__);
	}
}
