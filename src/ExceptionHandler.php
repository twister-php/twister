<?php

namespace Twister;

/**
 *	Custom Exception Handler
 */
class ExceptionHandler
{
	function __construct()
	{
		set_exception_handler(function ($e)
		{
			$dump = null;

			switch (get_class($e))
			{
				case 'mysqli_sql_exception':

					$backtrace = $e->getTrace();
					$sql = null;
					foreach ($backtrace as $trace)
					{
						switch ($trace['function'])
						{	//	Find MySQLi query if there was one!
							case 'query':
							case 'real_query':
								if ( ! isset($trace['class']) || $trace['class'] !== 'mysqli') break;
								$sql = $trace['args'][0];	//	1st arg in object style (mysqli::query())
								break;
							case 'mysqli_query':
							case 'mysqli_real_query':
								$sql = $trace['args'][1];	//	2nd arg in procedural style (mysqli_query())
						}
					}
					if (isset($sql))
					{
						$backtrace	= array_reverse($backtrace); // reverse the array, it feels a bit more natural to see the stack trace in call order!
						$dump		.=	'<hr />' . PHP_EOL . '<b>SQL Query Dump:</b><br />' . PHP_EOL . '<font color="gray">' . htmlentities($sql) . '</font><br />' . PHP_EOL;
					}
					break;
			}

			$backtrace = $e->getTrace();
			$max_file_length = 0;
			$max_line_length = 0;
			$internal_function = '[internal function]';
			foreach($backtrace as $trace)
			{
				if (isset($trace['file']))
				{
					$max_file_length = max($max_file_length, strlen($trace['file']));
					$max_line_length = max($max_line_length, strlen($trace['line']));
				}
				else
					$max_file_length = strlen($internal_function);
			}
			$backtrace	= array_reverse($backtrace); // reverse the array, it feels a bit more natural to see the stack trace in call order!
			$dump		.=	PHP_EOL . PHP_EOL . 'Stack trace:<pre>' . PHP_EOL;
			foreach ($backtrace as $index => $trace)
			{
				$args	= null;
				$comma	= null;
				foreach ($trace['args'] as $arg)
				{
					if (is_string($arg))		$args .= $comma . (strpos($arg,'\'')===false?'\'':'"') . (strlen($arg) > 40 ? substr($arg, 0, 40) . ' ...' : $arg) . (strpos($arg,'\'')===false?'\'':'"');
					else if (is_numeric($arg))	$args .= $comma . $arg;
					else if (is_bool($arg))		$args .= $comma . $arg; // ($arg ? 'true' : 'false')
					else if (is_null($arg))		$args .= $comma . 'null';
					else if (is_array($arg))	$args .= $comma . 'array';
					else if (is_object($arg))	$args .= $comma . '(object) ' . get_class($arg);
					else if (is_callable($arg))	$args .= $comma . 'callable';
					else if (is_resource($arg))	$args .= $comma . 'resource';
					$comma = ', ';
				}
				$dump	.=	str_pad('#' . ($index + 1), 4) .
									(isset($trace['file']) ? (str_pad($trace['file'], $max_file_length) . ' (line: ' . $trace['line'] . ')' . str_repeat(' ', $max_line_length - strlen($trace['line']))) : str_pad($internal_function, $max_file_length + $max_line_length + 9)) . ' <b>' .
									(isset($trace['class']) ? $trace['class'] . $trace['type'] : null) .
									$trace['function'] . '</b>(' . $args . ')' . PHP_EOL;
			}
			$dump		.=	'</pre>';

			//	Detect Content-Type
			$headers = headers_list();
			$html = true;
			$found_content_type = false;
			foreach ($headers as $header)
			{
				if (strpos($header, 'Content-Type:') !== false || strpos($header, 'Content-type:') !== false)
				{
					$found_content_type = true;
					$html = strpos($header, 'text/html') !== false;
				}
			}
			if ( ! $found_content_type && headers_sent() === false)
				header('Content-Type: text/html; charset=utf-8');

			$dump =	'<br />' . PHP_EOL .
					'<b>Fatal error</b>:  Uncaught <span style="color:red">' . get_class($e) . '</span>: ' . htmlentities($e->getMessage()) . ' thrown in <b>' . $e->getFile() . '</b> on line <b>' . $e->getLine() . '</b><br /><br />' . PHP_EOL .
					$dump . PHP_EOL;

			echo $html ? $dump : html_entity_decode(strip_tags($dump));
			exit;
		});
	}
}
