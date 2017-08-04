<?php

namespace Twister;

/**
 *	HTML Response Class
 *	JSON/Ajax output is handled elsewhere
 *
 *	This class should actually be extending a base Response class!
 */
class Response
{
	private $container		=	null;

	public $title			=	null;
	public $description		=	null;
	public $keywords		=	[];
	public $robots			=	'index,follow,noarchive';
	public $styles			=	[];
	public $scripts			=	[];
	public $style			=	null;
	public $script			=	null;
	public $meta			=	[];
	public $canonical		=	null;
	public $elements		=	[];
	public $lang			=	'en';

	private $renderer		=	null;

	function __construct(Container $c, $layout, $mixed)
	{
		$this->container = $c;

		header('Content-Type: text/html; charset=utf-8');

//		require __DIR__ . '/../lib/functions.php';
//		require __DIR__ . '/../classes/data.php';
//		require __DIR__ . '/../classes/lang.php';
//		require __DIR__ . '/../classes/assets.php';

		$paths = $c->config['paths'];

		//	Load layout/template specific default configuration
		//(require __DIR__ . '/../layouts/' . $layout . '.php')($this); // requires Less!?!? WHY???
		(require $paths['layouts'] . $layout . '.php')($this); // requires Less!?!? WHY???

		//	Load route specific page configuration
		if (is_string($mixed))
			$this->elements['content'] = $mixed;
		else if (is_array($mixed))
		{
			if (isset($mixed['title']))			$this->title				=	$mixed['title'];
			if (isset($mixed['description']))	$this->description			=	$mixed['description'];
			if (isset($mixed['keywords']))		$this->keywords				=	array_merge($this->keywords, (is_string($mixed['keywords']) ? array($mixed['keywords']) : $mixed['keywords']));
			if (isset($mixed['robots']))		$this->robots				=	$mixed['robots'];
			if (isset($mixed['canonical']))		$this->canonical			=	$mixed['canonical'];
			if (isset($mixed['styles']))		$this->styles				=	array_merge($this->styles, $mixed['styles']);
			if (isset($mixed['scripts']))		$this->scripts				=	array_merge($this->scripts, $mixed['scripts']);
			if (isset($mixed['content']))		$this->elements['content']	=	$mixed['content'];
			else if (isset($mixed['elements']))	$this->elements				=	array_merge($this->elements, $mixed['elements']);
		}

	//	assets::init(db::conn());

	//	$LM->load_file('includes/lang/common.xml', $FW['locale']); // OR $FW['page']['lang'] ... also loaded in sitemap!
	//	$DB->real_query('SET time_zone = "' . $LM->languages[$FW['locale']]['tz'] . '", lc_time_names = "' . $LM->languages[$FW['locale']]['tl'] . '"');

		$path = $paths['elements'];

		//
		//	Process Element Initialization
		//
		foreach ($this->elements as $element)
		{
			$filename = $path . $element . '/init.php';
			$init = require $filename;
			if (is_callable($init))
				$init($this);
		//	else				//	by disabling this we make the `callable function` requirement OPTIONAL!
		//		throw new \Exception('File `' . $filename . '` must return a callable function!');
		}

	//	assets::preload(true);

	//	$AM->preload(true);
	//	$LM->preload(true); // TODO: DEPRECATE THIS!!! ... WHY ? It's file based, not DB based! So loading them while the DB is open is bad!
	//	if (user::is_bot() === false)

		if (isset($_SESSION))
			session_write_close();

		$this->container->db->close();
	}

	/**
	 *	The `renderer` is normally just a callback function set by the `layout` file.
	 *	Before rendering the page (ie. calling the view modules), we close the session and database connection.
	 *	This function just echoes the HTML layout/template and calls the (element) views
	 */
	function render()
	{
		call_user_func($this->renderer);

		echo PHP_EOL,
			'<!--          memory_get_usage(): ', number_format(memory_get_usage()),			' -->', PHP_EOL,
			'<!--      memory_get_usage(true): ', number_format(memory_get_usage(true)),		' -->', PHP_EOL,
			'<!--     memory_get_peak_usage(): ', number_format(memory_get_peak_usage()),		' -->', PHP_EOL,
			'<!-- memory_get_peak_usage(true): ', number_format(memory_get_peak_usage(true)),	' -->', PHP_EOL,
			'<!--                        time: ', microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], ' -->';	//	Available since PHP 5.4.0.
	}

	function setRenderer(callable $callback)
	{
		$this->renderer = $callback;
	}
}
