<?php	//	AKA player || visitor (bot/agent/human) || manager

namespace Twister;

class User
{
	public	$id				=	0;
	private $_properties	=	null;
	private $_db			=	null;

	private $_permissions	=	null;

	function __construct(db &$db)
	{
		$this->_db = $db;
		if (isset($_SESSION['id']))
		{
			$this->id = $_SESSION['id'];
		//	$this->load_config();
		//	$this->load_profile();
			$this->load_permissions();
		}
	//	else
	//		$this->id = false;
//	TODO: This section needs work!
		else if (/*request::$https && */ isset($_COOKIE['HTTPS_ONLY']))	//	NOTE: We should ALREADY be on request::$https!!! Because it's checked before user::init() is called!
		{	//	We need to redirect to the user login page ... and stop there! ... actually ... we are just gonna clear the HTTPS_ONLY cookie, because the user.id is no longer valid!
		//	redirect('/login?next=' . urlencode(env::canonical('https:')) . '&message=session-expired');	//	`URI request too long` ... basically it goes into an infinite loop!
			setcookie('HTTPS_ONLY', null, -1, '/');
			unset($_COOKIE['HTTPS_ONLY']);
		//	env::https_redirect('/login?next=' . url_encode(...));	//	User session probably expired! maybe we should show a message in /login and unset the cookie there! Like `Your session has expired please login again!` or whatever!
		}
	}

	private static function load_permissions()
	{
		self::$_permissions = $this->_db->get_array(	'SELECT SQL_CACHE ' . // cached because these tables are less frequenty updated!
														'g.alias as g_alias,' .
														'p.alias as p_alias,' .
														'acl.object_id' .
													' FROM acl' .
														' JOIN acl_permissions p ON p.id = acl.permission_id' .
														' JOIN acl_groups g ON g.id = p.group_id' .
													' WHERE acl.user_id = ' . $this->id .
														' AND acl.disabled = 0',
													array('g_alias', 'p_alias', 'object_id'), array('object_id'));
	}
	static function permission($group_alias, $permission_alias, $query_data = null, $object = 0)
	{
		if (!is_array($object))
		{
			if (isset(self::$_permissions[$group_alias][$permission_alias][$object])) return true;
		}
		else // used when we want to specify default zero OR a value ... eg. array(0, 13);
			foreach ($object as $obj) if (isset(self::$_permissions[$group_alias][$permission_alias][$obj])) return true;
		if (isset(self::$_permissions['administrators']['super'])) return true; // super-admin bypass!
		if (isset($query_data))
		{
			if (is_string($query_data)) $query_data = array('next' => $query_data);
			$query_data['warning'] = 'Protected Area! Login with relevant permissions required!';							//	<== TODO: Translate this!!! Or send a constant!
			redirect('/login', $query_data);
		}
		return false;
	}
	static function permissions($group_alias, $permission_alias)
	{
		return isset(self::$_permissions[$group_alias][$permission_alias]) ? array_keys(self::$_permissions[$group_alias][$permission_alias]) : array();
	}
}
