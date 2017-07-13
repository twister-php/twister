<?php

namespace Twister;

class Request
{
	private $container	=	null;
	private $db			=	null;

	public $remote_addr	=	null;
	public $inet_pton	=	null;
	public $ip2hex		=	null;
	public $ipv4		=	null;

	public $method		=	null;

	public $is_https	=	null;
	public $isHttps		=	null;
	public $isSecure	=	null;

	public $isIpv4		=	null;
	public $is_ipv4		=	null;

	public $cc			=	null;

	public $uri			=	null;

	public $agent_id			=	null;
	public $forwarded_for_id	=	null;
	public $via_id				=	null;

	public $route		=	null;
	public $routes		=	null;
	public $params		=	null;

	function __construct(Container $container)
	{
		$this->container	=	$container;
		$this->db			=	$container->db;

		$this->_normalize_ip_address();
		$this->_detect_banned_ips();

		$this->method		=	strtoupper($_SERVER['REQUEST_METHOD']);

		$this->uri			=	Uri::fromGlobals();

		$this->isHttps		=	$this->is_https		=	$this->isSecure		=	$this->uri->isHttps();

		$this->cc			=	$this->_get_cc();

		$this->_get_agent_ex();
	}

	private function _normalize_ip_address()
	{
		if ( ! isset($_SERVER['REMOTE_ADDR']) || filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) === false)
			die('Invalid $_SERVER[REMOTE_ADDR]');

		$this->remote_addr	=	$_SERVER['REMOTE_ADDR'];
		$this->inet_pton	=	inet_pton($this->remote_addr);
		$this->ip2hex		=	bin2hex($this->inet_pton);
		$this->ipv4			=	ip2long($this->remote_addr);

		$this->isIpv4		=	$this->is_ipv4		=	$this->ipv4 !== false;
	}

	private function _detect_banned_ips()
	{
		$db = $this->db;
		if ($db->lookup('SELECT SQL_CACHE 1 FROM bot_bans WHERE ip = 0x' . $this->ip2hex))
		{
			$db->real_query('UPDATE bot_ban_requests SET requests = requests + 1 WHERE ip = 0x' . $this->ip2hex);
			$db->close();
			sleep(3);
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
	}

	private function _get_cc()
	{
		return $this->db->lookup(	$this->is_ipv4 ?
								('SELECT SQL_CACHE cc FROM geoip2_ipv4 WHERE ' . $this->ipv4 . ' BETWEEN range_from AND range_until') :
							//	'SELECT SQL_CACHE cc FROM geoip2_ipv6 WHERE CONV(HEX(LEFT(0x' . self::$ip2hex . ', 8)), 16, 10) BETWEEN range_high_from AND range_high_until AND CONV(HEX(RIGHT(0x' . self::$ip2hex . ', 8)), 16, 10) BETWEEN range_low_from AND range_low_until LIMIT 1'
								('SELECT SQL_CACHE cc FROM geoip2_ipv6 WHERE 0x' . substr($this->ip2hex, 0, 16) . ' BETWEEN range_high_from AND range_high_until AND 0x' . substr($this->ip2hex, 16) . ' BETWEEN range_low_from AND range_low_until LIMIT 1')
							);
	}

	private function _get_agent_ex()	//	`agent` is just a generic term for `agent`, `forwarded for` and `via`
	{
		$db = $this->db;
		$agent	= isset($_SERVER['HTTP_USER_AGENT'])	?	db::varchar($_SERVER['HTTP_USER_AGENT'], 700) : null;
		$ff		= db::varchar(self::_normalize_forwarded_for(), 128);
		$via	= isset($_SERVER['HTTP_VIA'])			?	db::varchar($_SERVER['HTTP_VIA'], 224) : null;
		$db->real_query('LOCK TABLES request_agents WRITE, request_forwarded_for WRITE, request_via WRITE');
			$this->agent_id			=	empty($agent)	? 0 : $this->_get_agent_ex_id($db, $agent,	'request_agents', 'agent');
			$this->forwarded_for_id	=	empty($ff)		? 0 : $this->_get_agent_ex_id($db, $ff,		'request_forwarded_for', 'forwarded_for');
			$this->via_id			=	empty($via)		? 0 : $this->_get_agent_ex_id($db, $via,	'request_via', 'via');
		$db->real_query('UNLOCK TABLES');
	}

	//	Combine all the possible values for 'HTTP_X_FORWARDED_FOR' together
	//	List taken from: http://blackbe.lt/advanced-method-to-obtain-the-client-ip-in-php/
	private static function _normalize_forwarded_for()
	{
		$HTTP_CLIENT_IP				= isset($_SERVER['HTTP_CLIENT_IP'])				? trim((string) $_SERVER['HTTP_CLIENT_IP'])				: null;
		$HTTP_X_FORWARDED_FOR		= isset($_SERVER['HTTP_X_FORWARDED_FOR'])		? trim((string) $_SERVER['HTTP_X_FORWARDED_FOR'])		: null;
		$HTTP_X_FORWARDED			= isset($_SERVER['HTTP_X_FORWARDED'])			? trim((string) $_SERVER['HTTP_X_FORWARDED'])			: null;
		$HTTP_X_CLUSTER_CLIENT_IP	= isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])	? trim((string) $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])	: null;
		$HTTP_FORWARDED_FOR			= isset($_SERVER['HTTP_FORWARDED_FOR'])			? trim((string) $_SERVER['HTTP_FORWARDED_FOR'])			: null;
		$HTTP_FORWARDED				= isset($_SERVER['HTTP_FORWARDED'])				? trim((string) $_SERVER['HTTP_FORWARDED'])				: null;
		$result = array();
		if (!empty($HTTP_CLIENT_IP))			$result[] = $HTTP_CLIENT_IP;
		if (!empty($HTTP_X_FORWARDED_FOR))		$result[] = $HTTP_X_FORWARDED_FOR;
		if (!empty($HTTP_X_FORWARDED))			$result[] = $HTTP_X_FORWARDED;
		if (!empty($HTTP_X_CLUSTER_CLIENT_IP))	$result[] = $HTTP_X_CLUSTER_CLIENT_IP;
		if (!empty($HTTP_FORWARDED_FOR))		$result[] = $HTTP_FORWARDED_FOR;
		if (!empty($HTTP_FORWARDED))			$result[] = $HTTP_FORWARDED;
		return preg_replace('/[, ]+/', ',', implode(',', $result));
	}

	private function _get_agent_ex_id($db, $value, $table, $field)
	{
		$md5 = md5($value);
		$id = $db->lookup('SELECT id FROM ' . $table . ' WHERE hash = 0x' . $md5);
		if ( ! $id)
		{
			$id = $db->call('CALL spGetRandomID("id", "' . $table . '", 1, 0x7FFFFFFF)');
			//	TEMPORARY HACK, while we examine the various 'forwarded_for' values!
			//	I want to know which of the various `forwarded_for` combinations are used on the internet!
			//	So these are just `bitmasks` of various possible fields! Once we establish which ones are used, we can remove the bit fields and the $_SERVER[] array member in normalize_forwarded_for()
			if ($field == 'forwarded_for')
				// REMOVE THIS SECTION when our 'testing' is complete! We should determine what 'forwarded_for' server variables are used, and reduce it!
				$db->real_query('INSERT IGNORE INTO request_forwarded_for (id, hash, forwarded_for, client_ip, x_forwarded_for, x_forwarded, x_cluster_client_ip, forwarded_for2, forwarded) VALUES (' . $id . ', 0x' . $md5 . ', ' . $db->escape($value) . ', ' . (int) empty($_SERVER['HTTP_CLIENT_IP']) . ', ' . (int) empty($_SERVER['HTTP_X_FORWARDED_FOR']) . ', ' . (int) empty($_SERVER['HTTP_X_FORWARDED']) . ', ' . (int) empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) . ', ' . (int) empty($_SERVER['HTTP_FORWARDED_FOR']) . ', ' . (int) empty($_SERVER['HTTP_FORWARDED']) . ')');
			else
				//	Leave only this statement after we've tested the various 'forwarded_for' combinations!
				$db->real_query('INSERT IGNORE INTO ' . $table . ' (id, hash, ' . $field . ') VALUES (' . $id . ', 0x' . $md5 . ', ' . $db->escape($value) . ')');
		}
		return $id;
	}

	public function execute_route(array $routes)
	{
		$path			=	$this->uri->path;
		$paths			=	explode('/', $path, 4);
		$route			=	null;
		$method			=	$this->method;
	//	$this->route	=	null;	//	general (debug) information about the matching route
		$this->routes	=	$routes; // $routes = require __DIR__ . '/../config/routes.php';
	//	$this->params	=	null;
		$controller		=	$routes['routes'][$paths[1]] ?? $routes[404];
	//	$matches		=	null;

		if (is_array($controller))
		{
			$arr		=	$controller;
			$controller	=	$routes[404];

			if (isset($arr[0]) && is_array($arr[0]))	//	test the first array item, continue if it's another array, if it's NOT an array, then it's a single route path processed below, this $arr[0] will be null or a string like 'GET' in the `single route/path` version
			{
				foreach ($arr as $route)
				{
					if ( ! isset($route[0]) || strpos($route[0], $method) !== false)
					{
						$regexp = '~^' . (strpos($route[1], '/') === 0 ? null : '/' . $paths[1] . '/') . $this->_convert_route_pattern($route[1]) . '$~';
						if (preg_match($regexp, $path, $this->params) === 1)
						{
							$this->route['regexp']	= $regexp;			//	store the `winning` regexp
							$this->route['command']	= $route[1];		//	store the `winning` (route) command of the regexp
						//	$this->params = $matches;
							$controller = $route[2];
							break;	//	break from the foreach loop!
						}
					}
				}
			}
			else if (is_null($arr[0]) || strpos($arr[0], $method) !== false)
			{
				$regexp = '~^' . (empty($arr[1]) ? '/' . $paths[1] : ((strpos($arr[1], '/') === 0 ? null : '/' . $paths[1] . '/') . $this->_convert_route_pattern($arr[1]))) . '$~';	//	by default if we leave the route/command null, we use /login instead of /login/ like `/admin/...` basically this works differently to the array (sub-folder) version for arrays, where we append /name/ but here we only do /name
				if (preg_match($regexp, $path, $this->params) === 1)
				{
					$this->route['regexp']	= $regexp;
					$this->route['command']	= $arr[1];
				//	$this->params = $matches;
					$controller = $arr[2];
				}
			}
		}

		$this->route['controller']	= $controller;


		//_::params($matches);	//	AKA _::request()['params'] = $params;

		if (is_string($controller))
		{
			if (($pos = strpos($controller, '::')) !== false)		//	controller is a static class method
			{
				$class = substr($controller, 0, $pos);
				$method = substr($controller, $pos + 2);
				require __DIR__ . '/../controllers/' . $class . '.php';
				$reflection = new \ReflectionMethod($class, $method);
				return $reflection->invokeArgs(null, $this->_get_args_from_params($reflection->getParameters()));
			}
			else if (($pos = strpos($controller, '->')) !== false)	//	controller is an instantiatable object, usually an object extending the base Controller class
			{
				$class = substr($controller, 0, $pos);
				$method = substr($controller, $pos + 2);
				require __DIR__ . '/../controllers/' . $class . '.php';
				$reflection = new \ReflectionMethod($class, $method);
				$obj = new $class($this->container);
				return $reflection->invokeArgs($obj, $this->_get_args_from_params($reflection->getParameters()));
			}
			else
			{
				//(require __DIR__ . '/../controllers/' . $controller . '.php')($params);
				$result = require __DIR__ . '/../controllers/' . $controller . '.php';
				if (is_callable($result))							//	controller is (possibly) a callable function
				{
					$reflection = new \ReflectionFunction($result);
					return $reflection->invokeArgs($this->_get_args_from_params($reflection->getParameters()));
				}
				//	else											//	controller is public/global code already executed directly inside the included file
			}
		}
		else if (is_callable($controller))
		{
			$reflection = new \ReflectionFunction($controller);
			return $reflection->invokeArgs($this->_get_args_from_params($reflection->getParameters()));
		}
		else if (is_array($controller))
		{
			// TODO
			die('array controller is not implemented yet');
		}
		else
		{
			die('unknown/unsuported controller type');
		}

		//(require __DIR__ . '/../controllers/' . $controller . '.php')($params);
	}

	//
	//	This function expands routes like '/admin/article/{id}' ==> '/admin/article/(?<id>[0-9]+)'
	//	It also converts TRAILING `optional` paths to the preg equivalent: '/club/{id}[/history]' ==> '/club/(?<id>[0-9]+)(?:/history)?'
	//
	private function _convert_route_pattern($route)
	{
		if (strrpos($route, ']', -1) !== false)	//	check if the route ends with optional parameters (where last character in route is `]`)
		{
			//	Check matching number of opening & closing brackets ... disabled only for performance reasons, the preg_match() will also throw an exception!?!? wtf?
			//if (substr_count($route, '[') !== substr_count($route, ']'))
			//	throw new \Exception('Number of opening `[` and closing `]` do not match in route: ' . $route);
			$depth = 0;
			$count = 0;							//	counts character for substr(), so we're not appending on every character, although routes are generally very short!
			$optionals = '';
			$i = strlen($route) - 1 - 1;		//	we already know the last character is ], so skip it
			while($route[$i] === ']')	$i--;	//	now find the first NON ] character from the back, eg. /admin[/] ... $i = 7 `/` ... /admin[opt2[opt1]]
			for (; $i >= 0; $i-- )
			{
				if ($route[$i] == '[')
				{
					if ($depth-- == 0)	//	found the opening `[` for an optional parameter!
					{
						$optionals = '(?:' . substr($route, $i + 1, $count) . $optionals . ')?';
						$count = -1;	//	the $count++ below will reset it to 0
						$depth = 0; 	//	reset counter, $depth is currently -1 ($depth-- == 0 means we test $depth for 0 BEFORE the decrement, leaving it as -1 after)
					}
				}
				else if ($route[$i] == ']')
					$depth++;
				$count++;
			}
			$route = substr($route, 0, $count) . $optionals;
		}

		$patterns = $this->routes['patterns'];

		//	replace matching named patterns
		return	preg_replace_callback(
						'~\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\s*(?::\s*([^{}]*(?:\{(?-1)\}[^{}]*)*))?\}~',
						function ($matches) use ($patterns) {
							return '(?<' . $matches[1] . '>' . (isset($matches[2]) ? (isset($patterns[$matches[2]]) ? $patterns[$matches[2]] : $matches[2]) : (isset($patterns[$matches[1]]) ? $patterns[$matches[1]] : '[^/]+')) . ')';
						},
						$route
					);
	}

	//	Dynamic route controller::handler argument builder
	private function _get_args_from_params(array $params)
	{
		$byType	=	[	'twister\container'	=>	&$this->container,
						'db'		=>	&$this->db,
						'request'	=>	&$this,
					//	'user'		=>	&$this->container->user
					];
		$args	=	[];
		foreach ($params as $param)
		{
			if ($param->hasType() && isset($byType[$type = strtolower($param->getType())]))
				$args[] = $byType[$type];
			else if (isset($_GET[$param->name]))
				$args[] = $_GET[$param->name];
			else if (isset($this->params[$param->name]))
				$args[] = $this->params[$param->name];
			else if (isset($_POST[$param->name]))
				$args[] = $_POST[$param->name];
			else
			{
				if ( ! $param->isOptional())
					throw new \Exception('Unable to find NON-optional parameter `' . $param->name . ($param->hasType() ? '` of type `' . $param->getType() : null) . '` for route controller/handler: ' . var_export($this->route, true));
				$args[] = $param->getDefaultValue();
			}
		}
		return $args;
	}

	/**
	 * Searches the $_POST and $_GET arrays for an item, or returns a default value
	 * @param string $key Used as a key for reading an array item value
	 * @param mixed $default The default value returned if the item does not exist
	 * @return mixed
	 */
	function get($key, $default = null)
	{
		return $_GET[$key] ?? $this->params[$key] ?? $_POST[$key] ?? $_REQUEST[$key] ?? $default;
	//	return $post && isset($_POST[$name]) ? $_POST[$name] : ($get && isset($_GET[$name]) ? $_GET[$name] : $default);
	}

	function getPost($key, $default = null)
	{
		return $_POST[$key] ?? $default;
	}

	function getCookie($key, $default = null)
	{
		return $_COOKIE[$key] ?? $default;
	}

	//	Helper functions; implemented by Slim
	function isGet()
	{
		return $this->method === 'GET';
	}
	function isPost()
	{
		return $this->method === 'POST';
	}
	function isPut()
	{
		//	In order to support Restful API's on browsers that only support GET and POST, ...
		//	Notes taken from Slim: https://www.slimframework.com/docs/objects/request.html
		//	`It is possible to fake or override the HTTP request method. This is useful if, for example, you need to mimic a PUT request using a traditional web browser that only supports GET or POST requests.`
		//	`There are two ways to override the HTTP request method. You can include a _METHOD parameter in a POST request’s body. The HTTP request must use the application/x-www-form-urlencoded content type.`
		//	PSEUDO CODE: if (header 'Content-type' == 'application/x-www-form-urlencoded') && get(_METHOD) === 'PUT'
		return $this->method === 'PUT';
	}
	function isDelete()
	{
		return $this->method === 'DELETE';
	}
	function isHead()
	{
		return $this->method === 'HEAD';
	}
	function isPatch()
	{
		return $this->method === 'PATCH';
	}
	function isOptions()
	{
		return $this->method === 'OPTIONS';
	}




	//	eg. request::build_url(array('scheme' => null))			==	//host/path
	//	eg. request::build_url(array('scheme' => 'https', 'host' => 'example.com', 'path' => '/login'))	==	https://example.com/login
	//	eg. request::build_url(array('path' => '/login'))		==	http://host/login		(NOTE: Any `default` (request::$query) query string will be ignored when `path` is specified!)
	//	eg. request::build_url(array('query' => 'test=123'))	==	//host/path?test=123
	//	eg. request::build_url(array('query' => ''))			==	//host/path				<<== removes the query string completely!
	//	eg.	request::build_url(array('scheme' => 'https', 'path' => '/'));	<== This ALSO clears any query string!
	//	SHORTHAND examples
	//	eg. request::build_url('//')		=== request::build_url(array('scheme' => null))
	//	eg. request::build_url('')			=== request::build_url(array('scheme' => null))
	//	eg. request::build_url('https')		=== request::build_url(array('scheme' => 'https'))
	//	eg. request::build_url('/')			=== request::build_url(array('path' => '/'))
	//	eg. request::build_url('test=123')	=== request::build_url(array('query' => 'test=123'))
	function build_url( $parts = null )
	{
		if (is_string($parts))
		{
			if ($parts === '' || $parts === '//')
				return $this->uri->withScheme(null); // '//' . $this->uri->authority . $this->uri->getPathAndQuery();
			if ($parts[0] === '/')
				return $this->uri->getLeftPart(Uri::PARTIAL_AUTHORITY) . $parts; //(_::is_https() ? 'https://' : 'http://') . _::request('host') . $parts;
			if ($parts === 'https' || $parts === 'http')
				return $this->uri->withScheme($parts); //$parts . '://' . _::request('host') . _::request('uri');
			if ($parts[0] === '?')
				return $this->uri->getLeftPart(Uri::PARTIAL_PATH) . $parts; //(_::is_https() ? 'https://' : 'http://') . _::request('host') . _::request('path') . $parts;
			if (strpos($parts, '=') !== false)
				return $this->uri->getLeftPart(Uri::PARTIAL_PATH) . '?' . $parts; // (_::is_https() ? 'https://' : 'http://') . _::request('host') . _::request('path') . '?' . $parts;
			if ($parts[0] === '#')
				return $this->uri->withFragment($parts); // (_::is_https() ? 'https://' : 'http://') . _::request('host') . _::request('uri') . $parts;
			die('Invalid call to Request::build_url("' . $parts . '")');
		}
		return (string) (new Uri($parts));
/*
		return	(isset($parts['scheme']) ? (empty($parts['scheme']) ? '//' : $parts['scheme'] . '://') : ($this->uri->scheme ?? 'http') : 'http://')) .	// Can set `scheme` to NULL to generate a Protocol-Relative-URL!
				'//' . (empty($parts['host']) ? $this->uri->authority : $parts['host']) .
				(empty($parts['path']) ? _::request('path') : $parts['path']) .
				(isset($parts['query']) ? (empty($parts['query']) ? null : '?' . $parts['query']) : (empty($parts['path']) && _::request('query') ? '?' . _::request('query') : null)); // NO FRAGMENT! We can append it if we really need it!
*/
	}

	//	eg. request::redirect('https')							eg. https://host/path?id=1
	//	eg. request::redirect('/')								eg. /
	//	eg. request::redirect('?test=123')						eg. /path?test=123				...	NEW query string
	//	eg. request::redirect('&test=123')						eg. /path?id=1&test=123			...	append to query string
	//	eg. request::redirect('/path?test=123', '?test=456')	eg. /path?test=456				...	OVERWRITES query string
	//	eg. request::redirect('/path?test=123', '?fail=456')	eg. /path?fail=456				...	OVERWRITES query string
	//	eg. request::redirect('/hello-world')
	//	eg. request::redirect('/login', 'error=Invalid Username')
	//	eg. request::redirect('/login', $errors)	`$errors = ['error' => 'Invalid Username']`
	//	eg. request::redirect('/new-home', 301)
	//	eg. request::redirect(['scheme' => 'https', 'host' => 'example.com', 'path' => '/login'])	==	https://example.com/login
	//	eg. request::redirect(['path' => '/login'])		==	/login
	//	eg.	request::redirect(['scheme' => 'https', 'path' => '/']);

	//	Function competely re-written and added to the request class on 21 May 2017
	function redirect( /* mixed */ )
	{
		/*
			Taken from: http://www.php.net/manual/en/function.header.php#78470

			// 301 Moved Permanently
			header("Location: /foo.php",TRUE,301);

			// 302 Found
			header("Location: /foo.php",TRUE,302);
			header("Location: /foo.php");

			// 303 See Other
			header("Location: /foo.php",TRUE,303);

			// 307 Temporary Redirect
			header("Location: /foo.php",TRUE,307);

			"The HTTP status code changes the way browsers and robots handle redirects, so if you are using header(Location:) it's a good idea to set the status code at the same time.  Browsers typically re-request a 307 page every time, cache a 302 page for the session, and cache a 301 page for longer, or even indefinitely.  Search engines typically transfer "page rank" to the new location for 301 redirects, but not for 302, 303 or 307."

			"If the status code is not specified, header('Location:') defaults to 302."
		*/
		$http_response_code = 302;
		$location = null;
		$args = func_get_args();
		if (count($args) === 1 && is_string($args[0]))
		{
			$arg = $args[0];
			switch ($arg[0])
			{
				case '/':
					$location = $arg;		//	$arg == '/path' || $arg == '//host/path'
					break;
				case 'h':
					if ($arg === 'http' || $arg === 'https')
						$location = (string) $this->uri->withScheme($arg); // '//' . $this->uri->authority . $this->uri->getPathAndQuery();
					//	$location = $arg . '://' . _::request('host') . _::request('uri');
					else
						$location = $arg;	//	$arg == 'http://www.google.com/'
					break;
				case '?':
					$location = $this->uri->getLeftPart(Uri::PARTIAL_PATH) . $parts; //(_::is_https() ? 'https://' : 'http://') . _::request('host') . _::request('path') . $parts;
					//$location = _::request('path') . $arg;
					break;
				case '&':	// append to query string
					$location = $this->uri->getLeftPart(Uri::PARTIAL_QUERY);
					$location .= (strpos($location, '?') === false ? '?' . substr($arg, 1) : $arg);
				//	$location .= ($location->query === null ? '?' . substr($arg, 1) : $arg);	//	alternative using Uri object!
					//$location = _::request('uri') . (empty(_::request('query')) ? '?' . substr($arg, 1) : $arg);
					break;
				case '#':
					$location = $this->uri->getLeftPart(Uri::PARTIAL_QUERY);
				//	$location = _::request('uri') . $arg;
					break;
				default:
					$location = $arg;
			}
		}
		else
		{
			throw new \Exception('Request section not implemented/RE-written yet!');

			/*
			//	Note: This is not necessary because the redirect header accepts a path based address; eg. / ... so the scheme & host are NOT always required! IOW we don't need to build a FULL / fully qualified URL like the `build_url()` function!
			$location = [	'scheme'		=>	self::$https ? 'https' ; 'http',
							'host'			=>	self::$host,
							'path'			=>	self::$path,
							'query'			=>	self::$query
						];
			*/
			foreach ($args as $arg)
			{
				if (is_string($arg))
				{
					switch (substr($arg, 0, 1))						//	pseudo: if $arg = '' then $arg[0] == `Notice: Uninitialized string offset: 0` ... IOW: We cannot use `switch($arg[0])` because we could get a PHP notice message!
					{
						case '/':
							if (substr($arg, 0, 2) === '//')		//	`//` eg. '//www.google.com/' ... ie. same protocol!
								$location = parse_url($arg);
							else									//	`/` eg. '/login' ... ie. relative URL
							{
								$qmark = strpos($arg, '?');
								if ($qmark === false)				//	eg. request::redirect('https', '/login')	...	ie. the `https` will set scheme, host, path and query. We only want scheme, host and path! NOT the query string!
								{
									$location['path'] = $arg;
									unset($location['query']);
								}
								else								//	eg. request::redirect('/login?id=123', '?id=456')
								{
									$location['path'] = substr($arg, 0, $qmark);
									$location['query'] = substr($arg, $qmark + 1);
								}
							}
							break;
						case '?':
							//	if ( ! isset($location['path']))	//	optional
							//		$location['path'] = self::$path;
							$location['query'] = substr($arg, 1);	//	overwrite existing query string  OR  create/set query string
							break;
						case '&':
							//	if ( ! isset($location['path']))	//	optional
							//		$location['path'] = self::$path;
							$location['query'] = (empty($location['query']) ? substr($arg, 1) : $location['query'] . $arg);	//	append to existing query string  OR  create new query string if it doesn't exist
							break;
						case '#':
							//	if ( ! isset($location['path']))	//	optional
							//		$location['path'] = self::$path;
							$location['fragment'] = substr($arg, 1);	//	should it be possible to just set the fragment without anything else?
							break;
						case 'h':
							if ($arg === 'http' || $arg === 'https')
							{
								$location = [	'scheme'		=>	$arg,
												'host'			=>	_::request('host'),
												'path'			=>	_::request('path'),
												'query'			=>	_::request('query')
											];
								break;
							}
							//	Alternative version below would have checked the prefix value of $arg for a `http:/` or `https:` value in the beginning to `semi-validate` the parse_url() value.
							//$substr = substr($arg, 0, 6);							//	`optional` alternative
							//if ($substr === 'http:/' || $substr === 'https:')
							//{
								$location = parse_url($arg);
								break;
							//}
							//	fallthrough vvv if NOT `http://` or `https://`
						default:
							die('Invalid value in request::redirect(): ' . print_r($arg, true));
							/*
							if (strpos($arg, '=') !== false)	//	eg. 'test=123' == append to query string
							{
								$location = [	'scheme'		=>	$arg,
												'host'			=>	self::$host,
												'path'			=>	self::$path,
												'query'			=>	self::$query
											];
							}
							*/
					}
				}
				else if (is_int($arg))
					$http_response_code = $arg;
				else if (is_array($arg))
				{
					if (is_array($location))
					{
						if (isset($location['query']) && isset($arg['path']) && ! isset($arg['query']))	//	eg. request::redirect('?id=123', ['path' => '/']) ... result = '/' ... because the '/' is a PATH without query string! The new `arg` value has a PATH but NOT a query string! path = query string = request_uri ... they go hand-in-hand!
							unset($location['query']);
						$location = array_merge($location, $arg);
					}
					else
						$location = $arg;
				}
				else
					die('Invalid value in request::redirect(): ' . print_r($arg, true));
			}

			if (is_array($location))
			{
				throw new \Exception('Request section not implemented/RE-written yet!');

				if (isset($location['scheme']) && ! isset($location['host']))
					$location['host'] = _::request('host');

				if (isset($location['user']) || isset($location['pass']))
					$location['host'] = (isset($location['user']) ? $location['user'] : null) . (isset($location['pass']) ? ':' . $location['pass'] : null) . '@' . (isset($location['host']) ? $location['host'] : _::request('host'));

				if (isset($location['host']) && ! isset($location['path']))
					$location['path'] = '/';

				$location = (isset($location['scheme'])   ? $location['scheme'] . ':' : null) .
							(isset($location['host'])     ? '//' . $location['host'] . (isset($location['port']) ? ':' . $location['port'] : null) : null) .
							(isset($location['path'])     ? $location['path'] : null) .
							(isset($location['query'])    ? '?' . $location['query'] : null) .
							(isset($location['fragment']) ? '#' . $location['fragment'] : null);
			}
			else
				die('Invalid value in request::redirect(): ' . print_r($args, true));
		}

die('redirecting to: <a href="' . $location . '">' . $location . '</a>');

		// ob_get_length() will return "FALSE if no buffering is active." ... so all we want to know is if buffering IS active AND there was already data sent to the buffer ... then we need to die()!
		if (ob_get_length()) die(ob_get_clean() . '<p><span style="color: red;">Errors on Redirect to: <a href="' . $location . '">' . $location . '</a></span></p>');
		if ( ! headers_sent($filename, $linenum))
		{
			if ( ! empty($location) ) header('Location: ' . $location, true, $http_response_code);
			else echo '<p><span style="color: red"><b>ERROR: Empty location on redirect()!</b></span></p>';
			exit;
		}
		die('<p><span style="color: red">ERROR:</span> <b>Headers already sent</b> in "' . $filename . '" on line <b>' . $linenum . '<br />' .
			'Cannot redirect, please click the following link: <a href="' . $location . '">' . $location . '</a></p>');
	}


}
