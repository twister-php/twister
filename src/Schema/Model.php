<?php

namespace Twister\Schema;

abstract class Model
{
}

namespace Twister;

class Session
{
	private static $_db = null;

	function __construct(DB &$db)
	{
		session_set_save_handler('Session::open', 'Session::close', 'Session::read', 'Session::write', 'Session::destroy', 'Session::gc');
		register_shutdown_function('session_write_close');
		session_set_cookie_params(0, '/', null, true, true);
		self::$_db = $db;
		session_start();
	}

	static function open($sp, $sn)
	{
		return true;
	}

	static function close()
	{
		return true;
	}

	static function read($id)
	{
		if (isset($_COOKIE[session_name()]))
		{
			if (!ctype_xdigit($id) || strlen($id) !== 32) die('Invalid Session ID: ' . $id);
			if ($rs = self::$_db->query('SELECT SQL_NO_CACHE data FROM sessions WHERE id = 0x' . $id . ' LIMIT 1'))
			{
				$row = $rs->fetch_row();
				$rs->free_result();
				return (string) $row[0];
			}
		}
		return '';
	}

	static function write($id, $data)
	{
		if (isset($_COOKIE[session_name()]))	//	WARNING: This will NOT write the session on the first page view! The cookie MUST be created/set first, which requires another page view before it works! This prevents bots from creating sessions! But during testing a new session_id() or browser, it can be confusing because you won't see a new session until your second page view!
		{
			$data = empty($data) ? 'NULL' : '"' . self::$_db->real_escape_string($data) . '"';
			self::$_db->real_query('INSERT INTO sessions (id, timestamp, persistent, data) VALUES (0x' . $id . ', UNIX_TIMESTAMP(), 0, ' . $data . ') ON DUPLICATE KEY UPDATE timestamp = UNIX_TIMESTAMP(), data = ' . $data);
		}
		return true;
	}

	//	This is ONLY required in the login page!
	static function create_persistent_session()
	{
		self::$_db->real_query('INSERT INTO sessions (id, timestamp, persistent, data) VALUES (0x' . session_id() . ', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), NULL)');
	}

/*
	static function login($user_id, $persistent)
	{
		session_regenerate_id(true);
		setcookie(session_name(), session_id(), $persistent ? 0x7fffffff : 0, '/');
		$_SESSION['id'] = $user_id;
		self::$_db->real_query('INSERT INTO sessions (id, timestamp, persistent, user_id, data) VALUES (0x' . session_id() . ', UNIX_TIMESTAMP(), ' . ($persistent ? 'UNIX_TIMESTAMP(), ' : '0, ') . $user_id . ', "")');
	}
*/

	//	Taken from: http://www.php.net/manual/en/function.session-destroy.php
	//	`session_destroy() destroys all of the data associated with the current session. It does not unset any of the global variables associated with the session, or unset the session cookie.`
	static function destroy($id)
	{
		self::$_db->real_query('DELETE FROM sessions WHERE id = 0x' . $id);
		return true;
	}

	static function gc($ttl)
	{
		self::$_db->real_query('DELETE FROM sessions WHERE timestamp < UNIX_TIMESTAMP() - ' . $ttl . ' AND persistent = 0'); // $ttl = 1440 (default) = 24 minutes
		if (mt_rand(0, 99) == 0)
		{
			self::$_db->real_query('OPTIMIZE TABLE sessions');	// optional routine maintenance
			self::$_db->real_query('FLUSH QUERY CACHE');			// optional routine maintenance
		}
		return true;
	}
}
