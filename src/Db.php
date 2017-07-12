<?php

namespace Twister;

/**
 *	Just a MySQLi wrapper class
 *	Adds 2 helper functions I use a lot, lookup() and get_array()
 *	`lookup()` returns a single value/field from the database, or a single row as an associated array by field names
 *	`get_array()` returns multiple rows in an associated array
 *	The other functions and functionality are less useful!
 */
class Db extends mysqli
{
	static function build($module, array $commands = array())
	{
		static $_fn = null;
		if ( ! isset($_fn[$module]))
			$_fn[$module] = require __DIR__ . '/../queries/' . $module . '.php';
		return $_fn[$module]($commands);
	}
	//	helper functions for the build scripts!
	private static function preg_replace_query($sql, $values)
	{
		return preg_replace_callback('~{(\w+)(?::(.*))?}~U', function ($matches) use ($values) { return isset($values[$matches[1]]) ? $values[$matches[1]] : (isset($matches[2]) ? $matches[2] : null); }, $sql);
	}
	private static function get_context_fields(&$fields, &$contexts, $context)
	{
		if (is_string($contexts[$context]))
			$contexts[$context] = array_flip(preg_split('/[\s,]+/', $contexts[$context]));
		return implode(', ', array_intersect_key($fields, $contexts[$context]));
	}
	private static function get_fields(&$fields, $list)
	{	//	eg. db::get_fields(['id' => 'u.id', 'name' => 'u.name'], 'id, name'); ... result = 'u.id, u.name'
		return implode(', ', array_intersect_key($fields, array_flip(preg_split('/[\s,]+/', $list))));
	}

	function set_time_zone($time_zone, $lc_time_names = null)
	{
		$this->real_query('SET time_zone = "' . $time_zone . '"' . $lc_time_names ? ', lc_time_names = "' . $lc_time_names . '"' : null);
	}


	//	function written on 24 May 2017 @ 12pm
	//	I realised that fetch_object() was not powerful or flexible enough to create custom objects.
	//	So with this function, I can initialize an object with custom parameters, or anything else I want!
	//	I can return an array of objects, or discard the result completely.
	//	eg. $list = \db::fetch_callback('SELECT * FROM worlds', function (&$row, &$result) { $result[] = $row['id']; /* (optional) return true; */ });
	//	optionally `return false;` in callback to stop processing
	function fetch_callback($sql, callable $callback)
	{
		$result = null;
		$rs = $this->query($sql, MYSQLI_USE_RESULT);
		while ($row = $rs->fetch_assoc())
		{
		//	if (call_user_func_array($callback, [&$row, &$result]) === false)	//	alternative: when using references, the values MUST be passed via an array with call_user_func_array(), call_user_func() only passes by value!
			if ($callback($row, $result) === false)
				break;
		}
		$rs->free_result();
		return $result;
	}

	function get_array($sql, $indexes = null, $values = null)
	{
		$rs = $this->query($sql, MYSQLI_USE_RESULT);	//	MYSQLI_USE_RESULT was about 12% faster than MYSQLI_STORE_RESULT
		if (isset($indexes))
		{
			if ($indexes == '#')	//	used in the ajax queries where we need to `override` the two field array index => value (field_count === 2) below!	eg. db::get_array($sql, '#', array('id', 'value'))
			{
				if (isset($values))
				{
					$result = array();
					if (is_string($values))
						foreach ($tmp as $row)
							$result[] = $row[$values];
					else // $values is an array
					{
						$values = array_flip($values);
						foreach ($tmp as $row)
							$result[] = array_intersect_key($row, $values);
					}
				}
				else // $values is null
					$result = $rs->fetch_all(MYSQLI_ASSOC); // MYSQLI_ASSOC | MYSQLI_NUM | MYSQLI_BOTH
			}
			else
			{
				$result = array();
				$tmp = $rs->fetch_all(MYSQLI_ASSOC); // MYSQLI_ASSOC || MYSQLI_NUM || MYSQLI_BOTH		WARNING: This has caused memory issues before!!! Unable to allocate n bytes!!! `SELECT * FROM city_catchment` = 2,981,304 rows | `data length` = 31MB (will be even larger in PHP)

				if (is_string($indexes))
					if (isset($values))
						if (is_string($values))
							foreach ($tmp as $row)
								$result[$row[$indexes]] = $row[$values];
						else // $values is an array
						{
							$values = array_flip($values);
							foreach ($tmp as $row)
								$result[$row[$indexes]] = array_intersect_key($row, $values);
						}
					else // $values is null
						foreach ($tmp as $row)	// Note: I tried using &$row here but it was slightly slower when just returning the recset without any processing on it, maybe it's faster if we do something with the recset?? Was 9.1s vs 9.5s!
							$result[$row[$indexes]] = $row;
				else // $indexes is an array
				{
					if (isset($values))
						if (is_string($values))
							foreach ($tmp as $row)
							{
								$entry = &$result;
								foreach ($indexes as $index)
									$entry = &$entry[$row[$index]];
								$entry = $row[$values];
							}
						else // $values is an array
						{
							$values = array_flip($values);
							foreach ($tmp as $row)
							{
								$entry = &$result;
								foreach ($indexes as $index)
									$entry = &$entry[$row[$index]];
								$entry = array_intersect_key($row, $values);
							}
						}
					else // $values is null
						foreach ($tmp as $row)
						{
							$entry = &$result;
							foreach ($indexes as $index)
								$entry = &$entry[$row[$index]];
							$entry = $row;
						}
				}
			}
		}
		else if ($rs->field_count === 2)	//	NOTE: We can construct this type of array from a larger (superset) array with the make_select_array() static HELPER function!
		{
			$result = array();
			$tmp = $rs->fetch_all(MYSQLI_NUM);
			foreach ($tmp as $row)
				$result[$row[0]] = $row[1];
		}
		else
			$result = $rs->fetch_all(MYSQLI_ASSOC); // MYSQLI_ASSOC | MYSQLI_NUM | MYSQLI_BOTH

		$rs->free_result();
		while ($this->more_results() && $this->next_result()); // Added on 2 May 2013; To be compatible with Stored Procedures (CALL ...)
		return $result;
	}

	function lookup($sql)
	{
		$rs = $this->query($sql, MYSQLI_USE_RESULT);
		if ($rs->field_count == 1)
		{
			$row = $rs->fetch_row();
			$rs->free_result();
			return $row[0];
		}
		$row = $rs->fetch_assoc();
		$rs->free_result();
		return $row;
	}

	function call($sql) // the original `lookup` code ... the `more_results` section is the only one required by stored procedures!
	{
		//$this->real_query('CALL ' . $sql); // not sure if I want to do this ??? will have to concat the string, but call('CALL ...') looks funny with 2x call's!
		$rs = $this->query($sql, MYSQLI_USE_RESULT);
		if ($rs->field_count == 1)
		{
			$row = $rs->fetch_row();
			$rs->free_result();
			while ($this->more_results() && $this->next_result()); // Added on 23 Feb 2013; To be compatible with Stored Procedures (CALL ...)
			return $row[0];
		}
		$row = $rs->fetch_assoc();
		$rs->free_result();
		while ($this->more_results() && $this->next_result()); // Added on 23 Feb 2013; To be compatible with Stored Procedures (CALL ...)
		return $row;
	}


	// MySQL only accepts 3-byte UTF-8! SANITIZE our "UTF-8" string for MySQL!
	// Taken from: http://stackoverflow.com/questions/8491431/remove-4-byte-characters-from-a-utf-8-string
	static function utf8($str)
	{
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", (string) $str);
	}
	function escape($value, $prefix = false) // AKA sanitize string! My NEW version which includes UTF-8 cleaning as well! I DO NOT KNOW WHERE THIS FUNCTION `escape` was being used ... so I `replaced` it on 22 July 2015 with my enhanced version `new_escape` which was built out of trying to replace the NULLable parameters ...
	{	// CODE FROM new_esacpe() written on 22 July 2015 @ 5pm (about) ... updated and re-written on 11 Dec 2015!
		if (is_numeric($value)) return ($prefix?'= ':null) . $value;
		if (is_string($value)) return $value ? ($prefix?'= "':'"') . $this->real_escape_string(self::utf8($value)) . '"' : ($prefix?'= ""':'""');
		if (is_null($value)) return $prefix ? 'IS NULL' : 'NULL';
		foreach ($value as $key => &$v)
			$v = $this->escape($v);
		return ($prefix?'IN (':'(') . implode(', ', $value) . ')';
	}
	// This function is used in post.php files to remove 4-byte UTF-8 characters (MySQL only accepts upto 3-bytes), pack multiple space values, trim and get only $length characters!
	static function varchar($str, $length = 65535, $empty = '', $compact = true)
	{
		//return mb_substr(str_squash(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $str)), 0, $length);
		//return mb_substr(trim(mb_ereg_replace('\s+', ' ', preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $str))), 0, $length);
		$str = trim(mb_substr(trim(mb_ereg_replace($compact ? '\s+' : ' +', ' ', self::utf8($str))), 0, $length)); // 2x trim() because after shortening it, we could have a space at the end of our shortened (substr) string
		return empty($str) ? $empty : $str;
	}



	//	eg. db::lock_tables('users WRITE', 'worlds READ');
	//	eg. db::lock_tables('users, worlds');
	function lock_tables(/* $tables, ... */)
	{
		$this->real_query('LOCK TABLES ' . implode(',', func_get_args()));
	}
	function unlock_tables()
	{
		$this->real_query('UNLOCK TABLES');
	}


	//	Written on 4 Dec 2015 @ 8:45am
	//	This is mainly just a wrapper around fetch_all()
	//	According to someone else's benchmark, it uses slightly less memory and about 35% faster than a while loop
	//	Use this when we just need to loop through the results and don't really care about the indexes!
	//	http://php.net/manual/en/mysqli-result.fetch-all.php
	function fast_fetch($sql, $resulttype = MYSQLI_ASSOC)
	{
		$recset = $this->query($sql, MYSQLI_USE_RESULT);	//	MYSQLI_USE_RESULT was about 12% faster than MYSQLI_STORE_RESULT
		$result = $recset->fetch_all($resulttype); // MYSQLI_ASSOC || MYSQLI_NUM || MYSQLI_BOTH
		$recset->free();
		return $result;
	}

	//	`get_object(s)` functions written on 17 Nov 2016
	//	http://php.net/manual/en/mysqli-result.fetch-object.php
	//	STUPID function! Its order of creation has changed between PHP 5.6 and 7
	//	The `params` don't seem to be passed to the constructor, so not sure what that's about
	function get_object($sql, $class_name = 'stdClass', array $params = null)
	{
		$rs = $this->query($sql, MYSQLI_USE_RESULT);
		$obj = is_null($params) ? $rs->fetch_object($class_name) : $rs->fetch_object($class_name, $params);
		$rs->free_result();
		return $obj;
	}

	//	http://php.net/manual/en/mysqli-result.fetch-object.php
	function get_objects($sql, $class_name = 'stdClass', array $params = null, $index = null)
	{
		$result = array();
		$recset = $this->query($sql, MYSQLI_USE_RESULT);
		if (isset($index))
		{
			if (is_null($params))
				while ($obj = $recset->fetch_object($class_name))
					$result[$obj->$index] = $obj;
			else
				while ($obj = $recset->fetch_object($class_name, $params))
					$result[$obj->$index] = $obj;
		}
		else
		{
			if (is_null($params))
				while ($obj = $recset->fetch_object($class_name))
					$result[] = $obj;
			else
				while ($obj = $recset->fetch_object($class_name, $params))
					$result[] = $obj;
		}
		$recset->free_result();
		return $result;
	}

	function get_random_id($field, $table, $min, $max)
	{
		return $this->call('CALL spGetRandomID("' . $field . '","' . $table . '",' . $min . ',' . $max . ')');
	}

	static function curdate()
	{
		static $curdate = null;
		if ( ! isset($curdate)) $curdate = date('Y-m-d');	//	`fast` PHP server date version!
		return $curdate;
/*
		//	taken from some older `request.php` init() code, but I don't think should be in there! This is a database lookup of the DATABASE server `curdate` which should be `more accurate` but slower!
		$tmp = db::lookup('SELECT UNIX_TIMESTAMP() AS timestamp, CURDATE() AS curdate');
		self::$timestamp	= $tmp['timestamp'];
		self::$curdate		= $tmp['curdate'];	//	database `slower` version
*/
	}

}

