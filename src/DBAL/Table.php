<?php

namespace Twister\DBAL;

use Twister\DBAL\Types;

class Table
{
	protected $fields	=	null;

	public function __construct(array $fields = null)
	{
		$this->fields	=&	$fields;
	}

	public function __get($field)
	{
		return $this->fields[$field];
	}

	public static function build($db, string $cache, string $schema, $table = null)
	{
		mysqli_report( MYSQLI_REPORT_STRICT );

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
					'CHARACTER_SET_NAME,' .			//	utf8
				//	'COLLATION_NAME,' .				//	utf8_general_ci
					'COLUMN_TYPE' .					//	int(10) unsigned / enum('','right','left')
				//	'COLUMN_KEY,' .					//	PRI / UNI / MUL
				//	'EXTRA' .						//	auto_increment / on update CURRENT_TIMESTAMP
				' FROM INFORMATION_SCHEMA.COLUMNS' .
				' WHERE TABLE_SCHEMA = "' . $schema . ($table ? (is_array($table) ? '" AND TABLE_NAME IN ("' . implode('","', $table) . '")' : '" AND TABLE_NAME = "' . $table) : null) . '"';
				' ORDER BY TABLE_NAME, ORDINAL_POSITION';

		$results = null;

		$rows = $db->query($sql)->fetch_all(MYSQLI_NUM); // MYSQLI_ASSOC || MYSQLI_NUM
		foreach ($rows as $row)
		{
			//$field = [$row[2], $row[3], $row[4] !== null];
			//... would have added more to the array ... but decided to make this realtime only for now!
			//$results[$row[0]][$row[1]] = &$field;
			$unsigned = strpos($row[7], 'unsigned') !== false;
			switch($row[2])
			{
				case 'int':			$obj = new IntegerType('int',		$row[3], $row[4] !== null, $unsigned ? 0 : -2147483648, $unsigned ? 4294967295 : 2147483647);
				case 'tinyint':		$obj = new IntegerType('tinyint',	$row[3], $row[4] !== null, $unsigned ? 0 : -128, $unsigned ? 255 : 127);
				case 'float':		$obj = new FloatType('float',		$row[3], $row[4] !== null, $row[5]);
				case 'varchar':		$obj = new StringType('varchar',	$row[3], $row[4] !== null, $row[5], $row[6]);
				case 'smallint':	$obj = new IntegerType('smallint',	$row[3], $row[4] !== null, $unsigned ? 0 : -32768, $unsigned ? 65535 : 32767);
				case 'enum':		$obj = new ArrayType('enum',		$row[3], $row[4] !== null, $row[3]);
				case 'mediumint':	$obj = new IntegerType('mediumint',	$row[3], $row[4] !== null, $unsigned ? 0 : -8388608, $unsigned ? 16777215 : 8388607);
				case 'date':		$obj = new DateType('date',			$row[3], $row[4] !== null);
				case 'bit':			$obj = new IntegerType('bit',		$row[3], $row[4] !== null, 0, 1);
				case 'char':		$obj = new StringType('char',		$row[3], $row[4] !== null, $row[5], $row[6]);
				case 'text':		$obj = new StringType('text',		$row[3], $row[4] !== null, 65535, $row[6]);
				case 'timestamp':	$obj = new DateType('timestamp',	$row[3], $row[4] !== null);
				case 'binary':		$obj = new StringType('binary',		$row[3], $row[4] !== null, $row[5], 0);
				case 'double':		$obj = new FloatType('double',		$row[3], $row[4] !== null, $row[5]);
				case 'tinytext':	$obj = new StringType('tinytext',	$row[3], $row[4] !== null, 255, $row[6]);
				case 'set':			$obj = new ArrayType('set',			$row[3], $row[4] !== null, $row[3]);
				case 'decimal':		$obj = new FloatType('decimal',		$row[3], $row[4] !== null, $row[5]);
				case 'year':		$obj = new IntegerType('year',		$row[3], $row[4] !== null, 1970, 2070); // 4-digit format = 1901 to 2155, or 0000.
				case 'varbinary':	$obj = new StringType('varbinary',	$row[3], $row[4] !== null, $row[5], 0);
				case 'bigint':		$obj = new IntegerType('bigint',	$row[3], $row[4] !== null, $unsigned ? 0 : -9223372036854775808, $unsigned ? 18446744073709551615 : 9223372036854775807);
				case 'datetime':	$obj = new DateType('datetime',		$row[3], $row[4] !== null);
				case 'time':		$obj = new DateType('time',			$row[3], $row[4] !== null);
				case 'mediumtext':	$obj = new StringType('mediumtext',	$row[3], $row[4] !== null, 16777215, $row[6]);
				case 'longblob':	$obj = new StringType('longblob',	$row[3], $row[4] !== null, 4294967295, $row[6]);
				case 'mediumblob':	$obj = new StringType('mediumblob',	$row[3], $row[4] !== null, 16777215, $row[6]);
				case 'numeric':		$obj = new FloatType('numeric',		$row[3], $row[4] !== null, $row[5]);
				case 'blob':		$obj = new StringType('blob',		$row[3], $row[4] !== null, 65535, $row[6]);
				case 'tinyblob':	$obj = new StringType('tinyblob',	$row[3], $row[4] !== null, 255, $row[6]);
				case 'longtext':	$obj = new StringType('longtext',	$row[3], $row[4] !== null, 4294967295, $row[6]);
			}
			$results[$row[0]][$row[1]] = &$obj;
		}

dump($results);
die();
var_dump($tmp);
die();
		$columns = $db->get_array($sql, ['TABLE_NAME', 'COLUMN_NAME']);
var_dump($columns);

		foreach ($columns as $table => $fields)
		{
			
		}
	}
}
