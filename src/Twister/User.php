<?php	//	AKA player || visitor (bot/agent/human) || manager

namespace Twister;

class User
{
	private $container		=	null;
	public	$id				=	0;
	private $_properties	=	null;
	private $_db			=	null;
	private $_permissions	=	null;

	function __construct(Container &$c, $id = 0)
	{
		$this->container = $c;
		$this->_db = $c->db;

		$this->id = $id;

		if ($id)
		{
			$this->load_permissions();
		}
	}

	/**
	 * Get member by id/index
	 *
	 * @param  string|int  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->_properties[$key];
	}

	/**
	 * Set member by id/index
	 *
	 * @param  string|int  $key
	 * @param  mixed       $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->_properties[$key] = $value;
	}

	function __isset($key)
	{
		return isset($this->_properties[$key]);
	}
	function __unset($key)
	{
		unset($this->_properties[$key]);
	}

	private function load_permissions()
	{
		$this->_permissions = $this->_db->get_array(	'SELECT SQL_CACHE ' . // cached because these tables are less frequenty updated!
															'g.alias as g_alias,' .
															'p.alias as p_alias,' .
															'acl.object_id' .
														' FROM acl' .
															' JOIN acl_permissions p ON p.id = acl.permission_id' .
															' JOIN acl_groups g ON g.id = p.group_id' .
														' WHERE acl.user_id = ' . $this->id .
															' AND acl.disabled = 0',
														['g_alias', 'p_alias', 'object_id'], ['object_id']);
	}
	function permission($group_alias, $permission_alias, $query_data = null, $object = 0)
	{
		if (!is_array($object))
		{
			if (isset($this->_permissions[$group_alias][$permission_alias][$object])) return true;
		}
		else // used when we want to specify default zero OR a value ... eg. array(0, 13);
			foreach ($object as $obj) if (isset($this->_permissions[$group_alias][$permission_alias][$obj])) return true;
		if (isset($this->_permissions['administrators']['super'])) return true; // super-admin bypass!
		if (isset($query_data))
		{
			if (is_string($query_data)) $query_data = ['next' => $query_data];
			$query_data['warning'] = 'Protected Area! Login with relevant permissions required!';	//	<== TODO: Translate this!!! Or send a constant!
			$this->container->request->redirect('/login?' . http_build_query($query_data));
		}
		return false;
	}
	function permissions($group_alias, $permission_alias)
	{
		return isset($this->_permissions[$group_alias][$permission_alias]) ? array_keys($this->_permissions[$group_alias][$permission_alias]) : array();
	}
}
