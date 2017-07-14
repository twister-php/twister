<?php

/**
 *	Immutable!
 */

namespace Twister\DBAL\Types;

class String extends Type
{
	const CHARSET_BINARY	= 0;	// unused	(the charset of BINARY fields is NULL!) ... we need to put BINARY data types inside string, for the maxlength ...
	const CHARSET_LATIN1	= 1;
	const CHARSET_UTF8		= 3;
	const CHARSET_UTF8MB4	= 4;
//	const CHARSET_UTF16		= 16;	// unused
//	const CHARSET_UTF32		= 32;	// unused

	public $maxlength		= null;
	public $charset			= null;
//	public $binary			= null; // needed? could be set by collation type: `utf8_bin` or data type `binary` ???

	function __construct($type, $default, $nullable, $maxlength, $charset)
	{
		$this->type			=	$type;
		$this->default		=	$default;
		$this->nullable		=	$nullable;

		$this->maxlength	=	$maxlength;
		$this->charset		=	$charset;
	}

	function isValid($value)
	{
		return parent::isValid($value) && is_scalar($value) && strlen($value) <= $length;
	}

	function toSQL(&$value)
	{
		return $value;
	}
}
