<?php

namespace Twister;

/**
 *	About 80% of this class was re-written by Trevor Herselman
 *	But originally based on the zend-diactoros Uri class
 *	I took some ideas from .NET, Symfony, Zend, CodeIgniter etc.
 */

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

use Psr\Http\Message\UriInterface;

/**
 * Implementation of Psr\Http\Message\UriInterface.
 *
 * Provides a value object representing a URI for HTTP requests.
 *
 * Instances of this class  are considered immutable; all methods that
 * might change state are implemented such that they retain the internal
 * state of the current instance and return a new instance that contains the
 * changed state.
 */
class Uri implements UriInterface
{
	/**
	 *	Sub-delimiters used in query strings and fragments.
	 *
	 *	@const string
	 */
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

	/**
	 *	Unreserved characters used in paths, query strings, and fragments.
	 *
	 *	@const string
	 */
	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

	/**
	 *	Idea taken from: https://msdn.microsoft.com/en-us/library/system.uri.getleftpart.aspx
	 *	Used with getLeftPart()
	 *
	 *	@const int
	 */
	const PARTIAL_SCHEME	=	0;
	const PARTIAL_AUTHORITY	=	1;
	const PARTIAL_PATH		=	2;
	const PARTIAL_QUERY		=	3;

	/**
	 *	Idea taken from: https://msdn.microsoft.com/en-us/library/system.urikind.aspx
	 *
	 *	@const int
	 */
	const KIND_ABSOLUTE		=	0;
	const KIND_PARTIAL		=	1;
	const KIND_ANY			=	2;


	//	Unfinished idea, use an array index to store the components instead of private variables, since we don't usually use `user`, `pass`, `port` and `fragment`
	/*
	const URI			=	0;
	const SCHEME		=	1;
	const USER			=	2;
	const PASS			=	3;
	const HOST			=	4;
	const PORT			=	5;
	const PATH			=	6;
	const QUERY			=	7;
	const FRAG			=	8;
	*/


	/**
	 *	Idea taken from: https://msdn.microsoft.com/en-us/library/system.urihostnametype.aspx
	 *
	 *	@const int
	 */
	const HOST_NAME_TYPE_BASIC		=	0;	//	The host is set, but the type cannot be determined.
	const HOST_NAME_TYPE_DNS		=	1;	//	The host name is a domain name system (DNS) style host name.
	const HOST_NAME_TYPE_IPV4		=	2;	//	The host name is an Internet Protocol (IP) version 4 host address.
	const HOST_NAME_TYPE_IPV6		=	3;	//	The host name is an Internet Protocol (IP) version 6 host address.
	const HOST_NAME_TYPE_UNKNOWN	=	4;	//	The type of the host name is not supplied.


	/**
	 *	@var int[] Array indexed by valid scheme names to their corresponding ports.
	 */
	static $allowedSchemes = [
				'http'		=> 80,
				'https'		=> 443,
				'ftp'		=> 21,		//	ftp://[user[:password]@]host[:port]/url-path
				'mailto'	=> true,	//	mailto:name@email.com
				'file'		=> true,	//	file://host/path	file://localhost/etc/fstab == file:///etc/fstab		file://localhost/c:/WINDOWS/clock.avi == file:///c:/WINDOWS/clock.avi
				'php'		=> true		//	php://stdin, php://stdout and php://stderr, php://input etc.			http://php.net/manual/en/wrappers.php.php
			];

	//	currently unused!
	static $authorityPrefix = [
				'http'		=> '//',
				'https'		=> '//',
				'ftp'		=> '//',	//	ftp://[user[:password]@]host[:port]/url-path
				'mailto'	=> null,	//	mailto:name@email.com
				'file'		=> '//',	//	file://host/path	file://localhost/etc/fstab == file:///etc/fstab		file://localhost/c:/WINDOWS/clock.avi == file:///c:/WINDOWS/clock.avi
				'php'		=> '//'		//	php://stdin, php://stdout and php://stderr, php://input etc.			http://php.net/manual/en/wrappers.php.php
			];

	/**
	 *  Array of supported hash algoithms, initialized to hash_algos() on first use!
	 *      Used when generating dynamic hash properties eg. $this->md5
	 *      Some algorithms cannot be used such as `gost-crypto`, `tiger128,3` etc. because of invalid characters in the name.
	 *          We could create a mapping from `gost-crypto` to `gost_crypto` and `tiger128,3` to `tiger128_3` etc. replacing invalid characters with underscores
	 *          But that would require looping through the array, and most of those algorithms are not very useful for Uri hashing.
	 *  http://php.net/manual/en/function.hash-algos.php
	 *  @var string[] Array of supported hash algoithms as array keys for fast lookup with isset(), used to test for dynamic hash properties eg. $this->md5
	 */
	static $hashAlgos = null;

	/**
	 *	Currently UNUSED: cannot use `gost-crypto`, `tiger128,3` etc.
	 *	Maybe we can create a mapping from `gost-crypto` to `gost_crypto` and `tiger128,3` to `tiger128_3` etc. replacing invalid characters with underscore
	 */
	/*
	static $validHashAlgos =	[	'md2', 'md4', 'md5',
									'sha1', 'sha224', 'sha256', 'sha384', 'sha512',
									'ripemd128', 'ripemd160', 'ripemd256', 'ripemd320',
									'whirlpool', 'snefru', 'snefru256', 'gost',
									'adler32', 'crc32', 'crc32b', 'fnv132', 'fnv1a32', 'fnv164', 'fnv1a64', 'joaat'
								];
	*/

	/**
	 *  Idea taken from CakePHP: https://api.cakephp.org/2.3/class-CakeRequest.html#$_detectors
	 *                           https://api.cakephp.org/2.3/source-class-CakeRequest.html#92-117
	 *
	 *	@var mixed[] Array of built in detectors used with is($type) or is$type(), can be modified with addDetector().
	 */
	//	TODO
	static $_detectors = null;	//	['dotcom' => function(&$uri){return substr($uri->host, -4) === '.com'}] eg. isDotCom() || is('DotCom') || is('.com') (the '.com' version cannot be tested with is.com()!)
								//	['domain' => function(&$uri, $domain){return substr($uri->host, -strlen($uri->host)) === $domain}]	//	isDomain('example.com') || isDomain('.com') || is('domain', '.com')
	/**
	 *	TODO: Not implemented yet!
	 *	Idea to support `Trusted Proxes` and the "X-Forwarded-Proto" header, so isSecure() will return true even for 'http' requests that are routed through them
	 *	See: http://api.symfony.com/2.3/Symfony/Component/HttpFoundation/Request.html#method_setTrustedProxies
	 *	@var bool[] 
	 */
	static $trustedProxies = [];

	/**
	 *	`PHP uses a non-standards compliant practice of including brackets in query string fieldnames` eg. foo[]=1&foo[]=2&foo[]=3  ==>  ['foo' => ['1', '2', '3']]
	 *	the CGI standard way of handling duplicate query fields is to create an array, but PHP's parse_str() silently overwrites them; eg. foo=1&foo=2&foo=3  ==>  ['foo' => '3']
	 *	This parameter will 
	 *	@var bool
	 */
	static $standardArrays = false;

	/**
	 *	URI string cache ~ reset to null when any property changes
	 *
	 *	@var null|string
	 */
	protected $_uri			=	null;

	/**
	 * @var null|string
	 */
	protected $_scheme		=	null;

	/**
	 * @var null|string
	 */
//	protected $_authority	=	null;

	/**
	 * @var null|string
	 */
//	protected $_userInfo	=	null;

	/**
	 * @var null|string
	 */
	protected $_user		=	null;

	/**
	 * @var null|string
	 */
	protected $_pass		=	null;

	/**
	 * @var null|string
	 */
	protected $_host		=	null;

	/**
	 * @var null|int
	 */
	protected $_port		=	null;

	/**
	 * @var null|string
	 */
	protected $_path		=	null;

	/**
	 * @var null|string
	 */
	protected $_query		=	null;

	/**
	 * @var null|string
	 */
	protected $_frag		=	null;


	/**
	 *	@param string $uri
	 *	@throws InvalidArgumentException on non-string $uri argument
	 */
	public function __construct()
	{
		switch (func_num_args())
		{
			case 0:
				//	$_parts['original'] = null;
				return;
			case 1:
				$args = func_get_args();
				if (is_string($args[0]))
				{
					if ($args[0] === 'https' || $args[0] === 'http')
					{
						self::fromGlobals($this);
						$this->_scheme = $args[0];
						return;
					}
					else
					{
						$this->parse($args[0]);
						return;
					}
				}
				else if (is_array($args[0]))
					$parts = &$args[0];
				else if ($args[0] instanceof self)
				{
					// Copy constructor
					$uri = &$args[0];
					$this->_uri			=	$uri->_uri;
					$this->_scheme		=	$uri->_scheme;
					$this->_user		=	$uri->_user;
					$this->_pass		=	$uri->_pass;
					$this->_host		=	$uri->_host;
					$this->_port		=	$uri->_port;
					$this->_path		=	$uri->_path;
					$this->_query		=	$uri->_query;
					$this->_frag		=	$uri->_frag;
					return;
				}
				else
					throw new InvalidArgumentException(sprintf(
								'Invalid argument type passed to Uri constructor; expecting a string or Uri object, received "%s"',
								(is_object($args[0]) ? get_class($args[0]) : gettype($args[0]))
							));
				break;

			case 2:

				$args = func_get_args();
				if ($args[0] instanceof self && $args[1] instanceof self)
				{	//	TODO: Build a Uri from base and relative Uri (`Initializes a new instance of the Uri class based on the combination of a specified base Uri instance and a relative Uri instance.`)
					//	https://msdn.microsoft.com/en-us/library/ceyeze4f(v=vs.110).aspx
					
				}
				else	//	I want to support a constructor that takes a baseUri/absoluteUri and relativeUri eg. 'http://example.com/admin/' + '../login' => 'http://example.com/login'
				{
					$parts = &$args;	//	instead of creating a new array, use the existing one! Basically 'scheme' and 'host' will be added to $args
					$parts['scheme']			=	&$args[0];
					$parts['host']				=	&$args[1];
				}
				break;

			case 3:
			case 4:
			case 5:

				//	Build a Uri string with 'scheme' + 'host' + ('path' + 'query' + 'fragment')
				//	Does not support `user`, `pass` and `port`
				$args = func_get_args();
				$parts = &$args;
				$parts['scheme']				=	&$args[0];
				$parts['host']					=	&$args[1];
				if (isset($args[2]))
				{
					$parts['path']				=	&$args[2];
					if (isset($args[3]))
					{
						$parts['query']			=	&$args[3];
						if (isset($args[4]))
							$parts['fragment']	=	&$args[4];
					}
				}
				break;

			default;
				throw new InvalidArgumentException(
							'Too many arguments passed to Uri constructor!'
						);
		}
		$this->fromArray($parts);
	}

	public static function fromGlobals(&$ptr = null)
	{
		static $uri;
		if (empty($uri))
		{
			if (empty($_SERVER['HTTP_HOST']))
				throw new \Exception('$_SERVER[HTTP_HOST] is empty');

			if (empty($_SERVER['REQUEST_URI']))
				throw new \Exception('$_SERVER[REQUEST_URI] is empty');

			if ( ! isset($_SERVER['QUERY_STRING']))	//	QUERY_STRING can be empty!
				throw new \Exception('$_SERVER[QUERY_STRING] is not set');

			$uri = new Uri();

			$uri->_scheme		=	($_SERVER['HTTPS'] ?? null) === 'on' ? 'https' : 'http';

			$uri->_host			=	empty($_SERVER['HTTP_HOST']) ? (empty($_SERVER['SERVER_NAME']) ? getenv('SERVER_NAME') : $_SERVER['SERVER_NAME']) : strtolower($_SERVER['HTTP_HOST']);

			if (self::isNonStandardPort($uri->_scheme, $uri->_host, (int) ($_SERVER['SERVER_PORT'] ?? 0)))
				$uri->_port		=	(int) $_SERVER['SERVER_PORT'];

			$uri->_path			=	parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);																				//	alternative 1
		//	$uri->_path			=	parse_url(rawurldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH)																	//	alternative 2
		//	$uri->_path			=	($qpos = strpos($_SERVER['REQUEST_URI'], '?')) !== false ? substr($_SERVER['REQUEST_URI'], 0, $qpos) : $_SERVER['REQUEST_URI'];	//	alternative 3
		//	$uri->_path			=	preg_replace('#^[^/:]+://[^/]+#', '', $_SERVER['REQUEST_URI']);	// from marshalRequestUri(), but this includes the query string	//	alternative 4

			if ( ! empty($_SERVER['QUERY_STRING']))
				$uri->_query 	=	$_SERVER['QUERY_STRING'];
		}

		if ($ptr === null)
			return clone $uri;

		if ($ptr instanceof self)
		{
			$ptr->_uri				=	(string) $uri;
			$ptr->_scheme			=	$uri->_scheme;
			$ptr->_user				=	$uri->_user;
			$ptr->_pass				=	$uri->_pass;
			$ptr->_host				=	$uri->_host;
			$ptr->_port				=	$uri->_port;
			$ptr->_path				=	$uri->_path;
			$ptr->_query			=	$uri->_query;
			$ptr->_frag				=	$uri->_frag;
		}
		else if (is_array($ptr))
		{
			$ptr['scheme']			=	$uri->_scheme;
			$ptr['user']			=	$uri->_user;
			$ptr['pass']			=	$uri->_pass;
			$ptr['host']			=	$uri->_host;
			if (isset($uri->_port))
				$ptr['port']		=	$uri->_port;
			$ptr['path']			=	$uri->_path;
			if ($uri->_query)
				$ptr['query']		=	$uri->_query;
			if ($uri->_frag)
				$ptr['fragment']	=	$uri->_frag;
		}
		return $ptr;
	}

	//	Alias of fromGlobals() to support Symfony
	public static function createFromGlobals(&$ptr = null)
	{
		return self::fromGlobals($ptr);
	}

	public function reset()
	{
		$this->_uri		=	null;
		$this->_scheme	=	null;
		$this->_user	=	null;
		$this->_pass	=	null;
		$this->_host	=	null;
		$this->_port	=	null;
		$this->_path	=	null;
		$this->_query	=	null;
		$this->_frag	=	null;
	}

	public function isDomain($domain)	//	eg. isDomain('.com') || isDomain('example.com') ... NOTE: For performance reasons, we don't strtolower() the $domain!
	{
		if ($this->_host === null) return false;
		return substr($this->_host, -strlen($this->_host)) === $domain;
	}
	public function isHost($host)	//	alias of isDomain()
	{
		return $this->isDomain($host);
	}

	/**
	 *	Strip the query string from a path
	 *	Taken from zend-diactoros ServerRequestFactory.php on line 368
	 *
	 *	@param mixed $path
	 *	@return string
	 */
	static function stripQueryString($path)
	{
		return ($qpos = strpos($path, '?')) !== false ? substr($path, 0, $qpos) : $path;
	}

	/**
	 *	TODO: Not implemented yet, just returns $qs
	 *
	 *	Idea taken from http://api.symfony.com/2.3/Symfony/Component/HttpFoundation/Request.html#method_normalizeQueryString
	 *	`It builds a normalized query string, where keys/value pairs are alphabetized, have consistent escaping and unneeded delimiters are removed.`
	 *
	 *	@param mixed $path
	 *	@return string
	 */
	static function normalizeQueryString(string $qs)
	{
		return $qs;
	}

	/**
	 *  Returns an array of the host split by '.'
	 *
	 *  @return string[] Array of host value split by '.', can be an IPv4 address or Domain with TLD's
	 */
	public function splitHost()	//	AKA explodeHost()
	{
		return explode('.', $this->_host ?? '');
	}

	/**
	 *  Returns an array of the path split by '/'
	 *
	 *  @return string[] Array of path value split by '/', remember that absolute paths start with /, so the first array value is usually an empty string!
	 */
	public function splitPath()	//	AKA explodePath()
	{
		return explode('/', $this->_path ?? '');
	}

	/**
	 * Parse a URI into its component parts, and set the properties
	 *
	 * @param string $uri
	 */
	private function parse($uri)
	{
		$parts = parse_url($uri);

		if ($parts === false)
			throw new \InvalidArgumentException("The source URI `{$uri}` appears to be malformed");

		//	special handling of mailto: scheme
		if (($parts['scheme'] ?? null) === 'mailto')
		{
			if (isset($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || isset($parts['query']) || isset($parts['port']) || empty($parts['path']) || $parts['path'][0] === '/')
				throw new \InvalidArgumentException("The source email address `{$uri}` appears to be malformed");

			$parts = parse_url('mailto://' . $parts['path']);	//	we 'fool' the parser by adding '//' before the email address, this allows parse_url() to detect the 'authority` components (user@domain)

			if ($parts === false)
				throw new \InvalidArgumentException("The source email address `{$uri}` appears to be malformed");
		}

		$this->fromArray($parts);
	}

	/**
	 *	Parse a URI array into its parts, and set the properties, $parts must be compatible with parse_url()
	 *
	 *	@param array $parts
	 */
	public function fromArray(array $parts)
	{	//	runs parameters through __set()
		$this->scheme	=	$parts['scheme']	??	null;
		$this->user		=	$parts['user']		??	null;
		$this->pass		=	$parts['pass']		??	null;
		$this->host		=	$parts['host']		??	null;
		$this->port		=	$parts['port']		??	null;
		$this->path		=	$parts['path']		??	null;
		$this->query	=	$parts['query']		??	null;
		$this->fragment	=	$parts['fragment']	??	null;
	}

	/**
	 *	builds/returns an array compatible with the result from parse_url()
	 *
	 *	@param string $uri
	 *	@return array compatible with parse_url()
	*/
	public function &toArray()
	{
		$arr =	[];

		if ($this->_scheme !== null)
			$arr['scheme']		=	$this->_scheme;

		if ($this->_user !== null)
			$arr['user']		=	$this->_user;

		if ($this->_pass !== null)
			$arr['pass']		=	$this->_pass;

		if ($this->_host !== null)
			$arr['host']		=	$this->_host;

		if ($this->_port !== null)
			$arr['port']		=	$this->_port;

		if ($this->_path !== null)
			$arr['path']		=	$this->_path;

		if ($this->_query !== null)
			$arr['query']		=	$this->_query;

		if ($this->_frag !== null)
			$arr['fragment']	=	$this->_frag;

		return $arr;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	Similar to Symfony isSecure()	http://api.symfony.com/2.3/Symfony/Component/HttpFoundation/Request.html#method_isSecure
	 *	Symfony has additional checks, so if an 'http' request is routed via X-Forwarded-Proto through a `Trusted Proxy` then it also returns true!
	 *
	 *	@return bool $this->scheme === 'https';
	 */
	public function isSecure()
	{
		return $this->_scheme === 'https';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'https';
	 */
	public function isHttps()
	{
		return $this->_scheme === 'https';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'http';
	 */
	public function isHttp()
	{
		return $this->_scheme === 'http';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'http' || $this->scheme === 'https';
	 */
	public function isHttpOrHttps()
	{
		return $this->_scheme === 'http' || $this->_scheme === 'https';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'ftp';
	 */
	public function isFtp()
	{
		return $this->_scheme === 'ftp';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'https';
	 */
	public function isMailto()
	{
		return $this->_scheme === 'mailto';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return bool $this->scheme === 'file';
	 */
	public function isFile()
	{
		return $this->_scheme === 'file';
	}

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
	public function __toString()
	{
		if ($this->_uri === null)
		{
			$uri = &$this->_uri;

			if ($this->_scheme !== null)
				$uri .= $this->_scheme . ':';

			if ($this->_host !== null)
				$uri .= ($this->_scheme !== 'mailto' ? '//' : null) . $this->authority;	//	warning: `mailto:user@example.com` doesn't have the '//' prefix!

			if ($this->_path !== null)
			{
				if ($this->_path[0] !== '/' && $this->_host !== null)
					$uri .= '/';

				$uri .= $this->_path;

				//$uri .= static::encodePath($this->path);	//	Zend version
			}
			else if ($this->_host !== null && ($this->_query || $this->_frag))
				$uri .= '/';

			if ($this->_query !== null)
				$uri .= '?' . $this->_query;	//	$uri .= '?' . static::encodeQueryFragment($this->query); ZEND

			if ($this->_frag !== null)
				$uri .= '#' . $this->_frag;		//	 $uri .= "#" . static::encodeQueryFragment($this->fragment); ZEND
		}
		return $this->_uri;
	}


	/**
	 *	{@inheritdoc}
	 */
	public function getScheme()
	{
		return $this->_scheme ?: '';
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getAuthority()
	{
		return $this->authority ?: '';
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getUserInfo()
	{
		return $this->userInfo ?: '';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	
	 */
	public function getUser()
	{
		return $this->_user ?: '';
	//	return empty($this->userInfo) ? '' : (($pos = strpos($this->userInfo, ':')) === false ? $this->userInfo : substr($this->userInfo, 0, $pos));
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	
	 */
	public function getPassword()
	{
		return $this->_pass ?: '';
	//	return empty($this->userInfo) ? '' : (($pos = strpos($this->userInfo, ':')) === false ? '' : substr($this->userInfo, $pos + 1));
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getHost()
	{
		return $this->_host ?: '';
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getPort()
	{
		return self::isNonStandardPort($this->_scheme, $this->_host, $this->_port)
			? $this->_port
			: null;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	.NET Framework always returns the default port number, even if it's not given in the URI.
	 *	So I just want to have a function that returns the default port number when one isn't specified!
	 *	
	 *	`The port number defines the protocol port used for contacting the server referenced in the URI.
	 *		If a port is not specified as part of the URI, the Port property returns the default value for the protocol.
	 *		If there is no default port number, this property returns -1.`
	 *
	 *	@return integer
	 */
	public function getRealPort()
	{
		return $this->_port ?: (self::$allowedSchemes[$this->_scheme] ?? -1);
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getPath()
	{
		return $this->_path ?: '';
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getQuery()
	{
		return $this->_query ?: '';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Alias for getQuery() to support Symfony syntax
	 */
	public function getQueryString()
	{
		return $this->_query ?: '';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Based on the Zend framework Uri class method with the same name!
	 *	https://framework.zend.com/manual/2.4/en/modules/zend.uri.html#getting-the-query-part-of-the-uri
	 */
	public function getQueryAsArray()
	{
		parse_str($this->_query, $result);
		return $result;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function getFragment()
	{
		return $this->_frag ?: '';
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Idea taken from the Uri property `PathAndQuery` in .NET Framework: https://msdn.microsoft.com/en-us/library/system.uri.pathandquery.aspx
	 *	AKA REQUEST_URI
	 */
	public function getPathAndQuery()
	{
		return $this->_path . ($this->_query === null ? null : '?' . $this->_query);
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Taken from: https://msdn.microsoft.com/en-us/library/system.uri.getleftpart.aspx
	 *
	 *	`The GetLeftPart method returns a string containing the leftmost portion of the URI string, ending with the portion specified by part.`
	 *
	 *	Taken from MSDN:	`Gets the specified portion of a Uri instance.`
	 */
	public function getLeftPart($part)
	{
		$new = new static();
	//	$uri = null;
		if (is_string($part))
		{
			switch ($part)
			{
				case 'scheme':		$part = self::PARTIAL_SCHEME;		break;
				case 'authority':	$part = self::PARTIAL_AUTHORITY;	break;
				case 'path':		$part = self::PARTIAL_PATH;			break;
				case 'query':		$part = self::PARTIAL_QUERY;		break;
				default:
					throw new \InvalidArgumentException("Invalid part `{$part}` for Uri->getLeftPart(); Only `scheme`, `authority`, `path` and `query` are valid!");
			}
		}
		switch ($part)
		{
			case self::PARTIAL_QUERY:

				$new->_query = $this->_query;

			//	if ($this->_query !== null)
			//		$uri = '?' . $this->_query;

			case self::PARTIAL_PATH:

				$new->_path = $this->_path;

			//	if ($this->_path !== null)
			//		$uri = ($this->_path[0] === '/' ? null : '/') . $this->_path . $uri;
			//	else if ($this->_host !== null)
			//		$uri = '/' . $uri;

			case self::PARTIAL_AUTHORITY:

				$new->_user = $this->_user;
				$new->_pass = $this->_pass;
				$new->_host = $this->_host;
				$new->_port = $this->_port;
			//	$new->authority = $this->authority;

			//	if ($this->_host !== null)
			//		$uri = ($this->_scheme === 'mailto' ? null : '//') . $this->authority . $uri;

			case self::PARTIAL_SCHEME:

				$new->_scheme = $this->_scheme;

			//	if ($this->_scheme !== null)
			//		$uri = $this->_scheme . ':' . $uri;
		}
		return $new;
	//	return $uri;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Taken from: https://msdn.microsoft.com/en-us/library/ms131572.aspx
	 *
	 *	This version accepts a `result` pointer (if func_num_args() === 2)
	 *		which will be set/reset with the current object and returns true/false on success/failure,
	 *		or if result is NOT provided (if func_num_args() === 1) then will return the new object directly!
	 *
	 *	Taken from MSDN:
	 *	`Creates a new Uri using the specified String instance and a UriKind.`
	 *	`If this method returns true, the new Uri is in result.`
	 *
	 *	@return bool
	 */
	public function tryCreate($uri, &$result = null)		//	TODO
	{
		$parts = parse_url($uri);
		if ($parts === false || empty($parts))
			return func_num_args() === 1 ? $result : false;

		switch (func_num_args())
		{
			case 1:
			case 2:
				if ($result instanceof self)
				{
					
				}
				else
				{
					
				}
			default:
		}

		return func_num_args() === 1 ? $result : true;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Similar to .NET Finalize; frees memory associated with the Uri
	 *
	 */
	public function finalize()
	{
		if ($this->_uri === null)
			$this->_uri = (string) $this;
/*
		$this->_scheme	=	null;
		$this->_user	=	null;
		$this->_pass	=	null;
		$this->_host	=	null;
		$this->_port	=	null;
		$this->_path	=	null;
		$this->_query	=	null;
		$this->_freq	=	null;
*/
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	My idea, just a wrapper around preg_match()
	 */
	public function match($pattern, &$matches = null, $flags = 0, $offset = 0)
	{
		return preg_match($pattern, (string) $this, $matches, $flags, $offset);
	}
	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 */
	public function matchHost($pattern, &$matches = null, $flags = 0, $offset = 0)
	{
		return preg_match($pattern, $this->_host ?: '', $matches, $flags, $offset);
	}
	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 */
	public function matchPath($pattern, &$matches = null, $flags = 0, $offset = 0)
	{
		return preg_match($pattern, $this->_path ?: '', $matches, $flags, $offset);
	}
	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 */
	public function matchQuery($pattern, &$matches = null, $flags = 0, $offset = 0)
	{
		return preg_match($pattern, $this->_query ?: '', $matches, $flags, $offset);
	}


	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *	Taken from: https://msdn.microsoft.com/en-us/library/system.uri.segments.aspx
	 *
	 *	Taken from MSDN:
	 *	`The Segments property returns an array of strings containing the "segments" (substrings) that form the URI's absolute path.
	 *		The first segment is obtained by parsing the absolute path from its first character until you reach a slash (/) or the end of the path.
	 *		Each additional segment begins at the first character after the preceding segment, and terminates with the next slash or the end of the path.
	 *		(A URI's absolute path contains everything after the host and port and before the query and fragment.)`
	 *
	 *	`Note that because the absolute path starts with a '/', the first segment contains it and nothing else.`
	 */
	public function getSegments()
	{
		return $this->segments;

		/*
		//	Alternative version using explode() and array internal pointers
		if ($this->path !== null)
		{
			$segments = explode('/', $this->path);
			if (empty(end($segments)))
				array_pop($segments); // this normally indicates that the last character was a '/', which created an empty string in the last array member
			else
				prev($segments); // reverse the internal pointer one place (from the end), because we have a 'file' name and we don't want to append '/' to something like 'file.php'
			for ( ;($key = key($segments)) !== null; prev($segments))
				$segments[$key] .= '/';
		}
		else
			return [];
		*/

		//	old version that excludes the '/' ... we could modify this with a foreach loop that adds the '/' to the end of each string!
		//return explode('/', $this->path ?: '');
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return string[] Returns an array of the `host` name split into DNS segments; eg. example.com => ['example', '.com']
	 */
	public function getDnsSegments()
	{
		return $this->dnsSegments;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	Similar output to the getSegments() function but excludes all the trailing '/'
	 */
	public function getSegmentsExplode()
	{
		if ($this->_path === null)
			return [];

		$segments = explode('/', $this->_path);

		if (empty(end($segments)))
			array_pop($segments); // this normally indicates that the last character was a '/', which created an empty string in the last array member

		return $segments;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withScheme($scheme)
	{
		if ( ! is_string($scheme) && $scheme !== null)
			throw new InvalidArgumentException(sprintf(
						'%s expects a string argument; received %s',
						__METHOD__,
						(is_object($scheme) ? get_class($scheme) : gettype($scheme))
					));

		$new = clone $this;
		$new->scheme = $scheme;

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withUserInfo($user, $password = null)
	{
		if ( ! is_string($user))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string user argument; received %s',
						__METHOD__,
						(is_object($user) ? get_class($user) : gettype($user))
					));

		if ($password !== null && ! is_string($password))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string password argument; received %s',
						__METHOD__,
						(is_object($password) ? get_class($password) : gettype($password))
					));

		$new = clone $this;
		$new->userInfo = $user . ($password ? ':' . $password : null);

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withHost($host)
	{
		if ( ! is_string($host))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string argument; received %s',
						__METHOD__,
						(is_object($host) ? get_class($host) : gettype($host))
					));

		$new = clone $this;
		$new->host = $host;

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withPort($port)
	{
		if ( ! is_numeric($port) && $port !== null)
			throw new InvalidArgumentException(sprintf(
						'Invalid port "%s" specified; must be an integer, an integer string, or null',
						(is_object($port) ? get_class($port) : gettype($port))
					));

		if ($port !== null)
			$port = (int) $port;

		if ($port === $this->port)
			return clone $this;	// Do nothing if no change was made.

		if ($port !== null && $port < 1 || $port > 65535)
			throw new InvalidArgumentException(sprintf(
						'Invalid port "%d" specified; must be a valid TCP/UDP port',
						$port
					));

		$new = clone $this;
		$new->port = $port;

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withPath($path)
	{
		if ( ! is_string($path))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string argument; received %s',
						__METHOD__,
						(is_object($path) ? get_class($path) : gettype($path))
					));

		if (strpos($path, '?') !== false)
			throw new InvalidArgumentException(
						'Invalid path provided; paths must not contain a query string'
					);

		if (strpos($path, '#') !== false)
			throw new InvalidArgumentException(
						'Invalid path provided; paths must not contain a URI fragment'
					);

		$new = clone $this;
		$new->path = $path;

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withQuery($query)
	{
		if ( ! is_string($query))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string argument; received %s',
						__METHOD__,
						(is_object($query) ? get_class($query) : gettype($query))
					));

		if (strpos($query, '#') !== false)
			throw new InvalidArgumentException(
						'Query string must not include a URI fragment'
					);

		$new = clone $this;
		$new->query = $query;

		return $new;
	}

	/**
	 *	{@inheritdoc}
	 */
	public function withFragment($fragment)
	{
		if ( ! is_string($fragment))
			throw new InvalidArgumentException(sprintf(
						'%s expects a string argument; received %s',
						__METHOD__,
						(is_object($fragment) ? get_class($fragment) : gettype($fragment))
					));

		$new = clone $this;
		$new->fragment = $fragment;

		return $new;
	}

	/**
	 *	Create a new Uri from parts array into its parts, and set the properties, $parts must be compatible with parse_url()
	 *
	 *	@param array $parts
	 */
	public function withArray(array $parts = null)
	{	//	WARNING: Cannot use ?? because we might want to set some parts explicity to null, eg. ['fragment' => null]
		//	UPDATE: we could do this: ['fragment' => '']
		$new = new static();
		$new->scheme	=	isset($parts['scheme'])		?	$parts['scheme']	:	$this->_scheme;
		$new->user		=	isset($parts['user'])		?	$parts['user']		:	$this->_user;
		$new->pass		=	isset($parts['pass'])		?	$parts['pass']		:	$this->_pass;
		$new->host		=	isset($parts['host'])		?	$parts['host']		:	$this->_host;
		$new->port		=	isset($parts['port'])		?	$parts['port']		:	$this->_port;
		$new->path		=	isset($parts['path'])		?	$parts['path']		:	$this->_path;
		$new->query		=	isset($parts['query'])		?	$parts['query']		:	$this->_query;
		$new->fragment	=	isset($parts['fragment'])	?	$parts['fragment']	:	$this->_fragment;
		return $new;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setUserInfo($user = null, $password = null)
	{
		$this->user = $user;
		$this->pass = $password;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}

	/**
	 *	NON-PSR-7 function added by Trevor Herselman
	 *
	 *	@return $this;
	 */
	public function setFragment($fragment)
	{
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 *	Is a given port non-standard for the current scheme?
	 *	WARNING: This retarded function from Zend requires $port to be an integer, or "$port !== self::$allowedSchemes[$scheme]" will fail! I was testing it against $_SERVER['SERVER_PORT'] which is a string!
	 *
	 *	@param string $scheme
	 *	@param string $host
	 *	@param int $port
	 *	@return bool
	 */
	static function isNonStandardPort($scheme, $host, $port)
	{
		if ( ! $scheme)
			return ($host && ! $port) ? false : true;

		if ( ! $host || ! $port)
			return false;

		return ! isset(self::$allowedSchemes[$scheme]) || $port !== self::$allowedSchemes[$scheme];
	}

	/**
	 *	Name taken from .NET Framework: https://msdn.microsoft.com/en-us/library/system.uri.isdefaultport.aspx
	 *
	 *	@return bool
	 */
	public function isDefaultPort(int $port = null)
	{
		$port = $port ?: $this->port;
		return $port === null || $port === (self::$allowedSchemes[$this->_scheme] ?? null);
	}

	/**
	 *	Name taken from .NET Framework: https://msdn.microsoft.com/en-us/library/system.uri.gethashcode.aspx
	 *	By default it uses `crc32`, which I think returns the hex values, but I think .NET returns the integer value, problem is with the negative values !?!?
	 *
	 *	@return int
	 */
	public function getHashCode(string $algo = 'crc32', bool $raw_output = false)
	{
		return hash($algo, (string) $this, $raw_output);
	}

	/**
	 *	MODIFIED by Trevor Herselman on 1 July 2017 @ 7pm. Changed the preg_replace to substr() and moved the `if empty()` test before the strtolower()
	 *
	 *	Encodes the scheme to ensure it is a valid scheme.
	 *
	 *	@param string $scheme Scheme name.
	 *
	 *	@return string Encoded scheme.
	 */
	private function encodeScheme($scheme)
	{
		if (strpos($scheme, ':') !== false)
			$scheme = substr($scheme, 0, strpos($scheme, ':'));		//	remove trailing : and optional trailing '//' characters ... eg. ('http://' || 'http:') -> 'http'
		if (empty($scheme))
			return null;
		$scheme = strtolower($scheme);

        if ( ! array_key_exists($scheme, self::$allowedSchemes))
            throw new InvalidArgumentException(sprintf(
						'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
						$scheme,
						implode(', ', array_keys(self::$allowedSchemes))
					));

		return $scheme;
	}

	/**
	 *	Encodes the path of a URI to ensure it is properly encoded.
	 *
	 *	@param string $path
	 *	@return string
	 */

    /**
     * Encode the path
     *
     * Will replace all characters which are not strictly allowed in the path
     * part with percent-encoded representation
     *
     * @param  string $path
     * @return string
     */
    public static function encodePath($path)
	{
		$path = preg_replace_callback(
					'/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
					__CLASS__ . '::urlEncodeChar',
					$path
				);

		if (empty($path))
			return null;	// No path

		if ($path[0] !== '/')
			return $path;	// Relative path

		// Ensure only one leading slash, to prevent XSS attempts.
		return '/' . ltrim($path, '/');
	}

	/**
	 *	Encode a query string to ensure it is propertly encoded.
	 *
	 *	Ensures that the values in the query string are properly urlencoded.
	 *
	 *	@param string $query
	 *	@return string
	 */
	private function encodeQuery($query, $arg_separator = '&')
	{
		if ( ! empty($query) && strpos($query, '?') === 0)
			$query = substr($query, 1);

		$parts = explode($arg_separator, $query);
		foreach ($parts as $index => $part)
		{
			list($key, $value) = $this->splitQueryValue($part);
			if ($value === null)
			{
				$parts[$index] = $this->encodeQueryOrFragment($key);
				continue;
			}
			$parts[$index] = sprintf(
						'%s=%s',
						$this->encodeQueryOrFragment($key),
						$this->encodeQueryOrFragment($value)
					);
		}

		return implode($arg_separator, $parts);
	}

	/**
	 *	Encodes a fragment value to ensure it is properly encoded.
	 *
	 *	@param null|string $fragment
	 *	@return string
	 */
	private function encodeFragment($fragment)
	{
		if ( ! empty($fragment) && $fragment[0] === '#')
			$fragment = '%23' . substr($fragment, 1);

		return $this->encodeQueryOrFragment($fragment);
	}

	/**
	 *	Encodes a query string key or value, or a fragment.
	 *
	 *	@param string $value
	 *	@return string
	 */
	private function encodeQueryOrFragment($value)
	{
		return preg_replace_callback(
			'/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u',
			__CLASS__ . '::urlEncodeChar',
			$value
		);
	}

	/**
	 *	URL encode a character returned by a regex.
	 *
	 *	@param array $matches
	 *	@return string
	 */
	private static function urlEncodeChar(array $matches)
	{
		return rawurlencode($matches[0]);
	}

	/**
	 *	Idea taken from: https://msdn.microsoft.com/en-us/library/system.uri.checkhostname.aspx
	 *	`The CheckHostName method checks that the host name provided meets the requirements for a valid Internet host name.
	 *		It does not, however, perform a host-name lookup to verify the existence of the host.`
	 *
	 *	@param string $value
	 *	@return string
	 */
	private function checkHostName()	//	this is a static method in .NET framework
	{
		//	todo
	}

	/**
	 *	Split a query value into a key/value tuple.
	 *
	 *	@param string $value
	 *	@return array A value with exactly two elements, key and value
	 */
	private function splitQueryValue($value)
	{
		$data = explode('=', $value, 2);
		if (count($data) === 1)
			$data[] = null;
		return $data;
	}

	/**
	 *	Added by Trevor Herselman on 2 July 2017
	 *	Originally taken from: http://php.net/manual/en/function.parse-str.php#76792
	 *
	 *	@param string $str
	 *	@return array key-value pairs
	 */
	static function proper_parse_str($str)
	{
		// result array
		$arr = array();

		// split on outer delimiter
		$pairs = explode('&', $str);

		// loop through each pair
		foreach ($pairs as $i)
		{
			// split into name and value
			list($name,$value) = explode('=', $i, 2);

			// if name already exists
			if( isset($arr[$name]) )
			{
				// stick multiple values into an array
				if( is_array($arr[$name]) )
					$arr[$name][] = $value;
				else
					$arr[$name] = array($arr[$name], $value);
			}
			// otherwise, simply stick it in a scalar
			else
				$arr[$name] = $value;
		}

		// return result array
		return $arr;
	}

	/**
	 *	Added by Trevor Herselman on 2 July 2017
	 *	Originally taken from: http://php.net/manual/en/function.parse-str.php#76792
	 *	UNFINISHED updated version, just added $arg_separator so far
	 *
	 *	@param string $value
	 *	@return array A value with exactly two elements, key and value
	 */
	static function proper_parse_str_new($str, $arg_separator = '&')
	{
		// result array
		$arr = array();

		// split on outer delimiter
		$pairs = explode($arg_separator, $str);

		// loop through each pair
		foreach ($pairs as $i)
		{
			// split into name and value
			list($name,$value) = explode('=', $i, 2);

			// if name already exists
			if( isset($arr[$name]) )
			{
				// stick multiple values into an array
				if( is_array($arr[$name]) )
					$arr[$name][] = $value;
				else
					$arr[$name] = array($arr[$name], $value);
			}
			// otherwise, simply stick it in a scalar
			else
				$arr[$name] = $value;
		}

		// return result array
		return $arr;
	}


	//	Helper function
	static function isAssocArray($array, $empty = false)
	{
		if (empty($array))
			return $empty;

		$i = 0;
		foreach ($array as $key => $value)
		{
			if ($key !== $i++)
				return true;
		}
		return false;
	}

	//	Helper function
	static function isSequentialArray($array, $empty = false)
	{
		if (empty($array))
			return $empty;

		$i = 0;
		foreach ($array as $key => $value)
		{
			if ($key !== $i++)
				return false;
		}
		return true;
	}

	//	Helper function
	private function setParts($name, &$value)
	{
		if (empty($value))
		{
			unset($this->_parts[$name]);
		}
		else //if ($this->_parts[$name] ?? null !== $value)
		{
			$this->_parts[$name] = $value;
			//$this->_uri = null;
		}
	}

	function __get($name)
	{
		switch ($name)
		{
			//	properties ordered by lookup frequency
			case 'scheme':		return $this->_scheme;
			case 'host':		return $this->_host;
			case 'path':		return $this->_path;
			case 'query':		return $this->_query;

			case 'port':		return $this->_port;
			case 'user':		return $this->_user;
			case 'pass':		return $this->_pass;
			case 'fragment':	return $this->_frag;

			//--- End of the common parse_url() properties ---//

			//--- Start of extended parse_url() properties ---//

			case 'userInfo':

				return empty($this->_user) ? null : ($this->_user . (empty($this->_pass) ? null : (':' . $this->_pass)));

			case 'segments':	//	Similar to System.Uri.Segments Property in .NET Framework: https://msdn.microsoft.com/en-us/library/system.uri.segments.aspx

				$segments = [];		//	eg. '/' => ['/'], '/admin/login' => ['/', 'admin/', 'login']
				$offset = 0;
				while (($pos = strpos($this->_path, '/', $offset)) !== false)
				{
					$segments[] = substr($this->_path, $offset, $pos - $offset + 1);
					$offset = $pos + 1;
				}
				if (strlen($this->_path) > $offset)
					$segments[] = substr($this->_path, $offset);

				return $segments;

			case 'authority':

				if ($this->_host === null)
					return null;

				$userInfo = $this->userInfo;

				return	( $userInfo !== null ? $userInfo . '@' : null) .
						$this->_host .
						(self::isNonStandardPort($this->_scheme, $this->_host, $this->_port) ? ':' . $this->_port : null);

			case 'schemeAndServer':		//	Idea taken from: https://msdn.microsoft.com/en-us/library/7767559y.aspx
										//	`The Scheme, Host, and Port data.`
				$scheme = $this->_scheme;
				if ( ! empty($scheme))
					$scheme .= ':';

				$host = $this->_host;
				if ( ! empty($host))
				{
					$host = ($this->_scheme === 'mailto' ? null : '//') . $host;
					$port = $this->_port;
					if ($port !== null)
						$port = ':' . $port;
				}

				return empty($scheme) && empty($host) ? null : ($scheme . $host);

			case 'hostAndPort':

				if ($this->_host === null)
					return null;

				$result = $this->_host;

				if ($this->_port)

				return $this->_host . ($this->_port !== null ? ':' . $this->_port : null);

/*
			case 'hostAndRealPort': // INCLUDES the default port

				$result = $this->host;
				if ()
				return $result
*/

			default:	//	couldn't find the property with common default names; so lets test the property for a mixed-case name or hash algorithm

				if ( ! ctype_lower($name))
					$name = strtolower($name);

				if (self::$hashAlgos === null)
					self::$hashAlgos = array_flip(hash_algos());	//	set the hash algorithms as keys for faster lookup with isset() instead of in_array()!

				if (isset(self::$hashAlgos[$name]))					//	we converted the hash name to lowercase above so we can safely support this: $this->Sha256
					return hash($name, (string) $this);

				//--- Start of alias and mixed-case properties ---//

				switch ($name)
				{
					//	possible mixed-case variants `normalized` to lowercase. eg. `Scheme` => `scheme`
					case 'scheme':		return $this->_scheme;
					case 'host':		return $this->_host;
					case 'path':		return $this->_path;
					case 'query':		return $this->_query;

					case 'user':		return $this->_user;
					case 'pass':		return $this->_pass;
					case 'port':		return $this->_port;
					case 'fragment':	return $this->_frag;

					case 'realport':		//	My alias of .NET `Uri.StrongPort`
					case 'strongport':

						//	This is called the Uri.StrongPort property in .NET Framework
						//	`The Port data. If no port data is in the Uri and a default port has been assigned to the Scheme,
						//		the default port is returned. If there is no default port, -1 is returned.`
						//	I put this property here because you are free to use ANY mixed/camel/pascal case variant of this property: eg. StrongPort, strongPort, realPort, RealPort etc.

						return $this->_port ?? self::$allowedSchemes[$this->_scheme] ?? -1;

					case 'userinfo':		return $this->userInfo;		//	mixed-case variant
					case 'userpass':		return $this->userInfo;		//	Alias of userInfo
					case 'segments':		return $this->__get($name);	//	$this->segments;
					case 'authority':		return $this->__get($name);	//	$this->authority;
					case 'schemeandserver':	return $this->schemeAndServer;
					case 'hostandport':		return $this->hostAndPort;
					case 'querystring':		return $this->_query;		//	possible mixed-case variant of `queryString`
					case 'username':		return $this->_user;
					case 'password':		return $this->_pass;
					case 'uri':				return (string) $this;
					case 'tostring':		return (string) $this;
					case 'string':			return (string) $this;
				}
		}

		$trace = debug_backtrace();
		trigger_error('Undefined property: ' . __CLASS__ . "->{$name} in <b>{$trace[0]['file']}</b> on line <b>{$trace[0]['line']}</b>; thrown", E_USER_ERROR);
	}

	//	requires 13 case matches before an array lookup table becomes viable, unlikely to ever become viable
	function __set($name, $value)
	{
		$this->_uri = null;

		$property = &$name;

		if ( ! ctype_lower($property))
			$property = strtolower($property);

		switch ($property)
		{
			case 'scheme':

				if (is_string($value))
				{
					$scheme = &$value;
					if ( ! ctype_lower($scheme))
					{
						//	Try to fix the (invalid) scheme by removing trailing ':' or '://' and converting to lowercase
						//		ie. normalize the scheme
						//		eg. ('Http://' || 'http:') -> 'http'

						if (strpos($scheme, ':') !== false)
							$scheme = substr($scheme, 0, strpos($scheme, ':'));

						$scheme = strtolower($scheme);

						//	test again - this test is not specifically for lowercase values only, but also for any remaining non-alphanumeric characters

						if ( ! ctype_lower($scheme))
						{
							throw new InvalidArgumentException("Invalid Uri scheme string; received {$value}");
						}
					}

					if ( ! array_key_exists($scheme, self::$allowedSchemes) && ! empty($scheme))
					{
						throw new InvalidArgumentException("Unsupported Uri scheme `{$scheme}`; must be an empty string or in the set (" . implode(', ', array_keys(self::$allowedSchemes)) . ')');
					}

					$this->_scheme = empty($scheme) ? null : $scheme;
				}
				else if ($value === null)
				{
					$this->_scheme = null;
				}
				else if ($value instanceof self)
				{
					$this->_scheme = $value->_scheme;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					// this will resend the array value through __set('scheme') again for validation!

					$this->scheme = $value[$property];
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri scheme string provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'host':

				if (is_string($value))
				{
					$host = &$value;

					if ($host[0] === '/')
					{
						// strip possible prefix characters '//'
						$host = ltrim($host, '/');
					}

					//	do other validation here

					$this->_host = empty($host) ? null : $host;
				}
				else if ($value === null)
				{
					$this->_host = null;
				}
				else if ($value instanceof self)
				{
					$this->_host = $value->_host;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					// this will resend the array value through __set('host') again for validation!

					$this->host = $value[$property];
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri host; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'port':

				if (is_numeric($value))
				{
					$port = (int) $value;

					if (self::isNonStandardPort($this->_scheme, $this->_host, $port))
					{
						if ($port < 1 || $port > 65535)
						{
							throw new InvalidArgumentException(
										"Invalid port `{$port}` specified; must be a valid TCP/UDP port"
									);
						}
					}
					else
					{
						$port = null;
					}

					$this->_port = $port;
				}
				else if ($value === null)
				{
					$this->_port = null;
				}
				else if ($value instanceof self)
				{
					//	resent through validation! Mainly for the non-standard-port checks
					$this->port = $value->_port;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					// resend value through __set('port') for validation!
					$this->port = $value[$property];
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri port provided; expecting a numeric string, integer or null; received ' .
								(is_object($value) ? get_class($value) : (is_string($value) ? $value : 'value of type ' . gettype($value)))
							);
				}

				return $value;

			case 'path':

				if (is_string($value))
				{
					$path = &$value;

					if (strpos($path, '?') !== false)
					{
						throw new InvalidArgumentException(
									'Invalid path provided; paths must not contain a query string'
								);
					}

					if (strpos($path, '#') !== false)
					{
						throw new InvalidArgumentException(
									'Invalid path provided; paths must not contain a URI fragment'
								);
					}

					//	do other validation here

					$path = self::encodePath($path);

					$this->_path = empty($path) ? null : $path;
				}
				else if ($value === null)
				{
					$this->_path = null;
				}
				else if ($value instanceof self)
				{
					$this->_path = $value->_path;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					$this->path = $value[$property]; // resend value through __set('path') for validation!
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri path; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'query':

				if (is_string($value))
				{
					$query = &$value;

					if (empty($query))
					{
						$this->_query = null;
					}
					else
					{
						if (strpos($query, '#') !== false)
							throw new InvalidArgumentException(
										'Invalid Uri query string provided; query must not contain a URI fragment'
									);

						$this->_query = $this->encodeQuery($query);
					}
				}
				else if ($value === null)
				{
					$this->_query = null;
				}
				else if ($value instanceof self)
				{
					$this->_query = $value->_query;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					$this->query = $value[$property]; // resend value through __set('query') for validation!
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri query string provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'fragment':

				if (is_string($value))
				{
					$fragment = &$value;

					if ($fragment[0] === '#')
						$fragment = substr($fragment, 1);

					$this->_frag = $this->encodeFragment($fragment);
				}
				else if ($value === null)
				{
					$this->_frag = null;
				}
				else if ($value instanceof self)
				{
					$this->_frag = $value->_frag;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					// resend value through __set('fragment') for validation!
					$this->fragment = $value[$property];
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid Uri fragment provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'user':

				if ($value === null)
				{
					$this->_user = null;
				}
				else if (is_string($value))
				{
					$user = &$value;

					$user = rawurlencode($user);

					$this->_user = $user;
				}
				else if ($value instanceof self)
				{
					$this->_user = $value->_user;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					$this->user = $value[$property]; // resend value through __set('user') for validation!
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid username provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'pass':

				if ($value === null)
				{
					$this->_pass = null;
				}
				else if (is_string($value))
				{
					$pass = &$value;

					$pass = rawurlencode($pass);

					$this->_pass = $pass;
				}
				else if ($value instanceof self)
				{
					$this->_pass = $value->_pass;
				}
				else if (is_array($value) && isset($value[$property]))
				{
					$this->pass = $value[$property]; // resend value through __set('pass') for validation!
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid password provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'segments':	//	Similar to System.Uri.Segments Property in .NET Framework: https://msdn.microsoft.com/en-us/library/system.uri.segments.aspx

				//	is_array() example; ['/', 'admin/', 'login']

				if (is_array($value))
				{
					$path = (string) implode($value);

					$this->_path = empty($path) ? null : $path;
				}
				else if ($value === null)
				{
					$this->_path = null;
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid segments provided; expecting an array or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'dnssegments':

				//	['example', '.com'] => 'example.com'

				if (is_array($value))
				{
					$host = (string) implode($value);

					$this->_host = empty($host) ? null : $host;
				}
				else if ($value === null)
				{
					$this->_host = null;
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid DNS segments; expecting an array or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			case 'authority':	//	compound property: [user[:pass]@]host[:port]

				if (is_string($value) && $value !== '')
				{
					$authority = &$value;

					if ($authority[0] === '/')
						$authority = ltrim($authority, '/');

					$split = explode('@', $authority);
					$count = count($split);

					if ($count === 1)
					{
						$authUser = null;
						$authHost = &$split[0];
					}
					else if ($count === 2)
					{
						$authUser = &$split[0];
						$authHost = &$split[1];
					}
					else
					{
						throw new InvalidArgumentException(
									'Invalid authority; expecting a string with at most a single `@` sign or null; received ' .
									(is_object($value) ? get_class($value) : gettype($value))
								);
					}

					// detect if optional port exists
					if (strpos($authHost, ':') === false)
					{
						$this->host = $authHost; // run through validation
						$this->_port = null;
					}
					else
					{
						$pos = strpos($authHost, ':');
						$this->host = substr($authHost, 0, $pos);
						$this->port = substr($authHost, $pos + 1);
					}

					if ($authUser === null)
					{
						$this->_user = null;
						$this->_pass = null;
					}
					else
					{
						if (strpos($authUser, ':') === false)
						{
							$this->user = $authUser; // run through validation
							$this->_pass = null;
						}
						else
						{
							$pos = strpos($authUser, ':');
							$this->user = substr($authUser, 0, $pos);
							$this->pass = substr($authUser, $pos + 1);
						}
					}
				}
				else if ($value === null || $value === '')
				{
					$this->_user = null;
					$this->_pass = null;
					$this->_host = null;
					$this->_port = null;
				}
				else if ($value instanceof self)
				{
					$this->_user	=	$value->_user;
					$this->_pass	=	$value->_pass;
					$this->_host	=	$value->_host;
					$this->_port	=	$value->_port;
				}
				else if (is_array($value))
				{	// resend all values through __set('...') for validation!
					$this->user	=	$value['user'] ?? null;
					$this->pass	=	$value['pass'] ?? null;
					$this->host	=	$value['host'] ?? null;
					$this->port	=	$value['port'] ?? null;
				}
				else
				{
					throw new InvalidArgumentException(
								'Invalid authority provided; expecting a string or null; received ' .
								(is_object($value) ? get_class($value) : gettype($value))
							);
				}

				return $value;

			default:
			//	if (in_array($name, hash_algos()))
			//		return hash($name, (string) $this);

				$property = &$name;

				if ( ! ctype_lower($property))
					$property = strtolower($property);

				if (self::$hashAlgos === null)
					self::$hashAlgos = array_flip(hash_algos());

				if (isset(self::$hashAlgos[$property]))
				{
					throw new InvalidArgumentException(
								"Invalid Uri property `{$name}`; hash properties are read-only"
							);
				}

				//--- Start of alias and mixed-case properties ---//

				switch ($property)
				{
					//	possible mixed-case variants `normalized` to lowercase. eg. `Scheme` => `scheme`
					case 'scheme':		return $this->scheme = $value;
					case 'host':		return $this->host = $value;
					case 'path':		return $this->path = $value;
					case 'query':		return $this->query = $value;

					case 'user':		return $this->user = $value;
					case 'pass':		return $this->pass = $value;
					case 'port':		return $this->port = $value;
					case 'fragment':	return $this->fragment = $value;

					case 'realport':		//	My alias of .NET `Uri.StrongPort`
					case 'strongport':

						//	This is called the Uri.StrongPort property in .NET
						//	`The Port data. If no port data is in the Uri and a default port has been assigned to the Scheme,
						//		the default port is returned. If there is no default port, -1 is returned.`

						return $this->port ?? self::$allowedSchemes[$this->scheme] ?? -1;

					case 'userinfo': return $this->userInfo;	//	mixed-case variant
					case 'segments': return $this->segments;
					case 'authority': return $this->authority;
					case 'querystring': return $this->query;	//	possible mixed-case variant of `queryString`
					case 'username': return $this->user;
					case 'password': return $this->pass;
					case 'uri': return (string) $this;
					case 'tostring': return (string) $this;
				}



		}

		$trace = debug_backtrace();
		trigger_error('Undefined property: ' . __CLASS__ . "->{$name} in <b>{$trace[0]['file']}</b> on line <b>{$trace[0]['line']}</b>; thrown", E_USER_ERROR);
	}

}
