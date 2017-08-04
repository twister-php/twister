<?php

namespace Twister;

class Css
{
	/**
	 *	Generate Css `duo` of master css file, as well as `themed` file
	 *
	 *	@param  array       $config Array of configuration properties, such as list of css files
	 *	@param  string|null $theme Non-default color theme name eg. 'gunmetal'
	 *	@param  array|null  $tint Additional `tinting` to be applied to the theme, such as alternative title colors
	 *	@param  string      $masterfile Variable to receive the `master filename`
	 *	@param  string      $themefile Variable to receive the `theme filename`
	 *	@return void
	 */
	static function duo($config, $theme, $tint, &$public_master_file, &$public_theme_file)
	{
		static $conf    =	null;
		if ($config === null)
			$config = $conf;
		else
			$conf = $config;

		$max_mtime      =	0;
		$masterfiles    =	[];
		$themefiles     =	[];
		$basepath       =	$config['basepath'];
		$cachepath      =	$config['cachepath'];

		$global_md5     =	md5(serialize($config['global']));

		$palette		=	is_array($tint)	?	(is_string($theme) ? array_merge(reset($config['themes']), $config['themes'][$theme], $tint) : array_merge(reset($config['themes']), $tint))
											:	(is_string($theme) ? array_merge(reset($config['themes']), $config['themes'][$theme]) : reset($config['themes']));
		$palette_md5    =	md5(serialize($palette));

		foreach ($config['files'] as $file)
		{
			$mtime          =	filemtime($basepath . $file);
			$max_mtime      =	max($max_mtime, $mtime);
			$filename       =	pathinfo($file, PATHINFO_FILENAME);
			$masterfiles[]  =	$cachepath . $filename . '-' . $mtime . '-' . $global_md5 . '.css';
			$themefiles[]   =	$cachepath . $filename . '-' . $mtime . '-' . $palette_md5 . '.css';
		}

		$public_master_file     =	$config['publicpath'] . 'duo-' . $max_mtime . '-' . md5(serialize($masterfiles)) . '.css';
		$public_theme_file      =	$config['publicpath'] . 'duo-' . $max_mtime . '-' . md5(serialize($themefiles)) . '.css';

		if ( ! file_exists($public_theme_file) || ! file_exists($public_master_file))
		{
			$global             =	$config['global'];
			$global_keys        =	array_keys($global);
			$palette_keys_md5   =	md5(serialize(array_keys($palette)));
			$public_master_css  =	null;
			$public_theme_css   =	null;
			foreach ($config['files'] as $index => $file)
			{
				/**
				 *	There are 3 files:
				 *		1)	The `main/master` file, with static/non-template values
				 *		2)	The `intermediary` file, which contains ALL key/value properties with the pre-replaced CONSTANTS, this is used by ALL themes when replacing values
				 *		3)	The `template` file, which is where we replace all the CONSTANTS from the intermediary file
				 *
				 *	To build the files, we do the following:
				 *		1)	We get the base file CSS into $css variable
				 *			We minify the original CSS to remove comments and whitespace
				 *			We replace the global/static/common styles, like `MEDIA-SMALL`
				 *				This is very important, because duo has problems with @media sections
				 *				because they have inner styles ... we could solve this,
				 *					but it's more pain than it's worth, and we don't have any need yet!
				 *					The other issue is that we actually have @media selectors that include CONSTANTS
				 *			Then we extract all the selectors that contain themeable CONSTANTS
				 *				eg. a{color:LINK-COLOR;font-size:12px;}
				 *			We remove all themeable CONSTANTS and their entire line from the $css content,
				 *				eg. 'color:LINK-COLOR'
				 *				by doing this, we are creating the final `master` file, which does not inlude any themeable CONSTANTS
				 *		2)	We then use these lines and create a new selector and CONSTANTS for the themeable 'intermediate' file.
				 *				eg. $selector . '{' . implode(';', $values) . '}'
				 *		1)	We use the remaining content in $css and write the final 'master' file.
				 *		3)	Then using the 'intermediate' file, we replace all the themeable CONSTANTS with their values and write the theme file.
				 */
				$intermediary_file      =	$cachepath . pathinfo($masterfiles[$index], PATHINFO_FILENAME) . '-' . $palette_keys_md5 . '.css';

				$intermediary_contents  =	file_exists($intermediary_file)   ? file_get_contents($intermediary_file)   : false;
				$master_contents        =	file_exists($masterfiles[$index]) ? file_get_contents($masterfiles[$index]) : false;
				$theme_contents         =	file_exists($themefiles[$index])  ? file_get_contents($themefiles[$index])  : false;

				if ($intermediary_contents === false || $master_contents === false)
				{
					//	We need to minify FIRST, because there might be some comments with styles! eg. border: BORDER /* BORDER-OLD */; But there might be other cases that screw up the regular expressions!
					$css    =	self::minify(file_get_contents($basepath . $file));

					/**
					 *	First replace all the `global` (common/static) styles
					 *	So `master` files can still be styled with these.
					 *	This is very important for `@media` sections, which cannot be styled with a theme.
					 *	The reason `@media` sections can't be styled, is because I can't extract them properly!
					 *	Because they contain inner styles, and my [^{}] regex fails
					 */
					$css = str_replace($global_keys, $global, $css);

					/**
					 *	Check `@media` sections for any themeable styles (after replacing the globals above)
					 *	@media sections can have styles from the 'global/common/static' list,
					 *		but not the normal themeable styles.
					 *	This section is just to make sure we have no styles in the '@media' sections!
					 *	This is my alternative to actually fully supporting styling in @media sections!
					 */
					preg_match_all('~@media[^{;}]*{(?:[^{;}]*{[^{}]*})+}~', $css, $media_sections);
					if ( ! empty($media_sections))
					{
						foreach ($media_sections as $media)
						{
							if ( ! empty($media))
							{
								preg_match('~[A-Z]+\-[A-Z-]+~', $media[0], $media_errors);
								if ( ! empty($media_errors))
								{
									throw new \Exception("@media sections cannot include themeable styles; check: `{$media_errors[0]}` in file: `{$file}`");
								}
							}
						}
					}

					/**
					 *	Extract a list of CSS selectors that have themeable CONSTANTS inside
					 *	[	0	=>	[	'a{color:LINK-COLOR;text-decoration:none}',
					 *					'a:hover,a:focus{color:LINK-HOVER-COLOR;text-decoration:underline}',
					 *					'.btn.danger:focus{color:BUTTON-DANGER-FOCUS-COLOR;background-color:BUTTON-DANGER-FOCUS-BG-COLOR}'
					 *				],
					 *		1	=>	[	'a',
					 *					'a:hover,a:focus',				<==		These are officially called `selectors`
					 *					'.btn.danger:focus'
					 *				]
					 *	]
					 *	We use [0] to extract each rule with a separate preg below,
					 *		because I can't get a list of CONSTANT + selector (because some selectors have multiple CONSTANTS),
					 *		at least this way I'm ONLY searching the selectors that definately include template/theme constants
					 */
					preg_match_all('~([^{;}]+){[^{]*[A-Z]+\-[A-Z-]+[^}]*}~', $css, $extract);

					/**
					 *	Intermediary file is the most likely candidate for being here.
					 *	Here we ignore if we actually loaded the contents of an intermediary file at the top,
					 *		and rebuild the intermediary contents completely from scratch regardless.
					 */
					$intermediary_contents = null;

					foreach ($extract[0] as $i => $rule)	//	$rule example: '.btn.danger:focus{color:BUTTON-DANGER-FOCUS-COLOR;background-color:BUTTON-DANGER-FOCUS-BG-COLOR}'
					{
						/**
						 *	Splits the rule CONSTANTS
						 *	[	0	=>	[	'color:BUTTON-COLOR;',
						 *					'box-shadow:BUTTON-BOX-SHADOW'		<== note that some end with `;` and others don't!
						 *				],
						 *		1	=>	[	'color:BUTTON-COLOR',
						 *					'box-shadow:BUTTON-BOX-SHADOW'		<==	NONE of these end with ';'
						 *				]
						 *	]
						 */
						preg_match_all('~([^;{]*?[A-Z]+\-[A-Z-]+[^};]*)\;?~', $rule, $values);
						$intermediary_contents .= $extract[1][$i] . '{' . implode(';', $values[1]) . '}';	//	$values[1] excludes the `;`, so we can join with it! If this fails, then our preg_match_all() above is not getting the correct values!
						//	remove each CONSTANT property from the $css, whatever is left in the $css value will become the `master` (because it include only static properties!)
						if ($master_contents === false)
						{
							foreach ($values[0] as $v)
							{
								$css = str_replace($v, null, $css);
							}
						}
					}
					//	minify again, because we might have removed all the values from a rule, this will remove empty selectors!
					$css = self::minify($css);
					if ($master_contents === false)
					{
						$master_contents = $css;
						file_put_contents($masterfiles[$index], $css);
					}
					file_put_contents($intermediary_file, $intermediary_contents);
				}

				if ($theme_contents === false)
				{
					$theme_contents = preg_replace_callback('~([A-Z]+-[A-Z-]+)~',
						function ($matches) use ($palette, &$file)
						{
							if ( ! isset($palette[$matches[0]]))
								throw new \Exception("Invalid css theme property: `{$matches[0]}` used in css file: `{$file}`. Check the palette array in your themes config file!");
							return $palette[$matches[0]];
						}, $intermediary_contents);
					file_put_contents($themefiles[$index], $theme_contents);
				}

				$public_master_css .= $master_contents;
				$public_theme_css .= $theme_contents;
			}
			if ( ! file_exists($public_master_file))
			{
				file_put_contents($public_master_file, $public_master_css);
			}
			//if ( ! file_exists($public_theme_file))
			{
				file_put_contents($public_theme_file, $public_theme_css);
			}
		}
	}


	/**
	 *	Why do we need keys???
	 *
	 */
	static function keys($config)
	{

	}


	/**
	 *	Old/original duo code
	 *	main problem was all the hard coding of paths
	 */
	static function __duo($masterfile, $palette = null, $tint = null, array $overrides = null)
	{
		//	THEME vs. SCHEME vs. SKIN
		//	Theme = light/dark! AKA `tint` in the current system ... ie. `What theme would you like?` asking the user to choose a color scheme!
		//	Scheme = `Color Scheme` = club/brand colors = several headers/borders colored with this color scheme
		//	Skin = related to Scheme ... The club/brand skin is the backgrounds, like watermarks, background images representing a club/brand!
		static $basepath = '../app/duo/';
		static $cachepath = '../app/duo/cache/';
		static $duos = array();
		if (!isset($duos[$masterfile])) $duos[$masterfile] = require($basepath . $masterfile . '.php');
		$arr =& $duos[$masterfile];
		$files = array();
		$max_mtime = 0;
		if ($palette === null && $tint === null && $overrides === null)
		{
			if (isset($arr['static']))
				foreach ($arr['static'] as $static)
				{
					$mtime = filemtime($basepath . $static);
					$max_mtime = max($max_mtime, $mtime);
					$files[] = $cachepath . pathinfo($static, PATHINFO_FILENAME) . '-' . $mtime . /* '-' . md5($static) */ '.css';
				}
			$global_md5 = md5(serialize($arr['global']));
			foreach ($arr['themeable'] as $themeable)
			{
				$mtime = filemtime($basepath . $themeable);
				$max_mtime = max($max_mtime, $mtime);
				$files[] = $cachepath . pathinfo($themeable, PATHINFO_FILENAME) . '-' . $mtime . '-' . $global_md5 . '.css';
			}
			$result = 'styles/duo-' . $max_mtime . '-' . md5(serialize($files)) . '.css';
			if (file_exists($result)) return $result;
			//
			//	Global/base style file doesn't exists, create a new one!
			//
			$result_contents = null;	//	contents of the final file!
			require_once APP_LIB . 'duocss.php';
			$index = 0;	//	$files[$index] ... because static messes up the counter! ... can't we just add the count($arr['static']) ???
			if (isset($arr['static']))
				foreach ($arr['static'] as $static)
				{
					if (!file_exists($files[$index]))
					{
						$result_contents .= $contents = minify_css(file_get_contents($basepath . $static));
						file_put_contents($files[$index], $contents);
					}
					else
						$result_contents .= file_get_contents($files[$index]);
					$index++;
				}
			$global_keys = array_keys($arr['global']);	//	Get the global keys for str_replace()
			foreach ($arr['themeable'] as $themeable)
			{
				if (!file_exists($files[$index]))
				{
					$pattern = array();	//	Remove ALL lines with palette styles! Even if they include global styles!
					foreach ($arr['palette'] as $key => $unused)		//	We run the preg_replace() first, because it will reduce the contents, making str_replace() slightly faster!
						$pattern[] = '~[^{;]*' . $key . '[^;}]*;?~';
					//	We need to minify FIRST, because there might be some comments with styles! eg. border: BORDER /* BORDER-OLD */; But there might be other cases that screw up the regular expressions!
					$result_contents .= $contents = minify_css(str_replace($global_keys, $arr['global'], preg_replace($pattern, '', minify_css(file_get_contents($basepath . $themeable)))));
					file_put_contents($files[$index], $contents);
				}
				else
					$result_contents .= file_get_contents($files[$index]);
				$index++;
			}
			file_put_contents($result, $result_contents);
			return $result;
		}
		else
		{
		//	There `should` be too many variables and tints for an array_merge() to be feasable/viable! Also, this cannot account for $arr['palette'][$tint] coming before loading the palette file!
		//	$palettes = $palette === null ? $arr['palette'] : array_merge($arr['palette'], require '../app/duo/palettes/' . $palette . '.php');
			//
			//	Generate Palette
			//
			if ($palette !== null) $palette = require '../app/duo/palettes/' . $palette . '.php';
			//	`$files` is currently a blank array, so we use it as an empty array paceholder
			$palette = array_merge($arr['palette'], isset($arr['tints'][$tint]) ? $arr['tints'][$tint] : $files, isset($palette[0]) ? $palette[0] : $files, isset($palette[$tint]) ? $palette[$tint] : $files, $overrides === null ? $files : $overrides);
			//	Create an md5 hash of the palette. Used as part of the filename in each cached themeable file!
			$palette_md5 = md5(serialize($palette));	//	. serialize($arr['global']) .... DISABLED (the serialize) FOR NOW!  TEST  IT !! The reason why we need to include the global, is in-case a global value changed ... HOWEVER, in that case, wouldn't the files also change ??? However again, we might be using dynamic overrides !?!?
			//	Add each `themeable` file to the files array! To be serialized and md5 hashed later to test the existance of the final file!
			foreach ($arr['themeable'] as $themeable)
			{
				$mtime = filemtime($basepath . $themeable);
				$max_mtime = max($max_mtime, $mtime);
				$files[] = $cachepath . pathinfo($themeable, PATHINFO_FILENAME) . '-' . $mtime . '-' . $palette_md5 . '.css';
			}
			$result = 'styles/duo-' . $max_mtime . '-' . md5(serialize($files)) . '.css';
			if (file_exists($result)) return $result;
			//
			//	Public styled palette doesn't exist; test the existence of each cached themeable file
			//
			$result_contents = null;	//	contents of the final file!
			require_once APP_LIB . 'duocss.php';
			$keys = array_merge(array_keys($arr['global']), array_keys($arr['palette']));	//	Create an array of the merged global and palette keys!
			$keys_md5 = md5(serialize($keys));
			$patterns = array();
			//	Build an array of keys to extract! We will split each file into class names and content, then scan the content for our keys and extract them!
			foreach ($keys as $key)
				$patterns[] = '~[^;]*' . $key . '[^;]*~';
			$global_keys = array_keys($arr['global']);
			$palette_keys = array_keys($palette);
			$palette_patterns = array();
			foreach ($palette_keys as $key)
				$palette_patterns[] = '~[^;]*' . $key . '[^;]*~';	//	Build the palette patterns! Once we split the cached contents into classes and attributes, then we can run these patterns!

			foreach ($arr['themeable'] as $key => $themeable)
			{
				if (!file_exists($files[$key]))
				{	//	First test for the intermediate cached file. This file contains ALL the possible styleable global and palette variables/attributes/styles.
					$mtime = filemtime($basepath . $themeable);
					$filename = pathinfo($themeable, PATHINFO_FILENAME);
					$cache = $cachepath . $filename . '-' . $mtime . '-' . $keys_md5 . '.css';
					if (!file_exists($cache))
					{	//	Create the intermediate cached file with ALL possible overwriteable styles!
						$path = $basepath . $themeable;
						$contents = minify_css(file_get_contents($path));	//	Minify the contents, mainly to remove the comments, because they CAN interfere with the class names and the regular expression matches (because they can contain the following characters {;} !!!
						preg_match_all('~([^{;}]+){([^{}]+)}~', $contents, $split);	//	Split the file into classes and content!	eg. html{overflow:auto} => `html` && `overflow:auto`
						$preserve = array();	//	classes and attributes/styles to preserve, basically we `remove` all the styles which are not configurable (we do this by INCLUDING only valid styleable classes and attributes here)! Leaving only styles that include a global or palette constant!
						foreach ($split[2] as $index => $value)	//	loop through each class and attribute list and extract the customizeable styles!
							foreach ($patterns as $pattern)
								if (preg_match_all($pattern, $value, $matches))	//	our `$patterns` don't actually capture anything, so we are able to use $matches[0]!
									if (isset($preserve[$split[1][$index]]))
										$preserve[$split[1][$index]] .= ';' . implode(';', $matches[0]);
									else
										$preserve[$split[1][$index]] = implode(';', $matches[0]);
						$contents = ''; // reset & rebuild contents with our `preserved` classes and constants!
						foreach ($preserve as $class => $style)
							$contents .= $class . '{' . $style . '}';
						file_put_contents($cache, $contents);
					}
					else
						$contents = file_get_contents($cache);

					//	Now we have the intermediate content, which includes both global and palette styles! Now we need to extract only the styles we need! For example, many `global` styles will be dropped here!
					preg_match_all('~([^{;}]+){([^{}]+)}~', $contents, $split);
					$preserve = array();
					foreach ($split[2] as $index => $value)
						foreach ($palette_patterns as $pattern)
							if (preg_match_all($pattern, $value, $matches))
								if (isset($preserve[$split[1][$index]]))
									$preserve[$split[1][$index]] .= ';' . implode(';', $matches[0]);
								else
									$preserve[$split[1][$index]] = implode(';', $matches[0]);
					$contents = '';	//	rebuild the contents, unused styles should have been removed!?!?
					foreach ($preserve as $class => $style)
						$contents .= $class . '{' . $style . '}';

				/*	//	This is only necessary if we use the str_replace() variant !!!
					if (!env::$production && preg_match_all('|[A-Z]{2,}(?:-[A-Z]{2,})+|', $contents, $matches))
						foreach ($matches[0] as $match)
							if (!isset($palette[$match]))
								die('<b>ERROR</b> finding the CSS variable: <b>' . $match . '</b> in file: <b>' . $themeable);
				*/

					// Now we have rebuilt the content, we need to replace the style placeholders!
					//	First, we replace the PALETTE styles, which could include global style overrides! THEN we place the global styles, if there are any left over! This has to be done if we've mixed palette with global styles! eg. border: BORDER-WIDTH BORDER-COLOR ... where BORDER-WIDTH might be a global, and BORDER-COLOR is a palette value!
				//	$result_contents .= $contents = str_replace($global_keys, $arr['global'], str_replace($palette_keys, $palette, $contents));	//	we `could` run the minifier AGAIN, but it's already 99.9% minified! For example, the placeholders could be unminified eg. `BORDER` => `0 0 0 0` instead of just 0 ... or `0px` instead of 0
					$result_contents .= $contents = preg_replace_callback('|[A-Z]{2,}(?:-[A-Z]{2,})+|', function($matches)use($palette){return $palette[$matches[0]];}, $contents);	// The preg_replace_callback() variant was about 30% faster than str_replace() on a very large file (about +200K)! They still executed at 0.01s vs. 0.007 on a very large file with 30 other files!

					file_put_contents($files[$key], $contents);
				}
				else
					$result_contents .= file_get_contents($files[$key]);
			}

			file_put_contents($result, $result_contents);
			return $result;
		}
	}

	//	Taken from: http://stackoverflow.com/a/15195752/2726557
	//	WARNING:	This will convert something silly like: border:-0.5rem -0.5rem -0.5em; => border:-0.5rem-0.5rem-0.5em;
	//				Which might not even be valid CSS, it was just something I noticed during experiments!
	static function minify($str)
	{
		# remove comments first (simplifies the other regex)
		$re1 = <<<'EOS'
%(?sx)
	# quotes
	(
		"(?:[^"\\]++|\\.)*+"
	|	'(?:[^'\\]++|\\.)*+'
	)
|
	# comments
	/\* (?> .*? \*/ )
%
EOS;

		$re2 = <<<'EOS'
%(?six)
	# quotes
	(
		"(?:[^"\\]++|\\.)*+"
	|	'(?:[^'\\]++|\\.)*+'
	)
|
	# ; before } (and the spaces after it while we're here)
	\s*+ ; \s*+ ( } ) \s*+
|
	# all spaces around meta chars/operators
#	WARNING: This is the original! HOWEVER, it's removing the spaces before the NEGATIVE `-16` sign! eg. margin: 0 -16px; !!!
#	NOTE: What was the reason/situation for removing this space before/after negative/dash signs ??? We COULD remove the space AFTER the dash! eg. - hello => -hello ???
#	\s*+ ( [*$~^|]?+= | [{};,>~+-] | !important\b ) \s*+
	\s*+ ( [*$~^|]?+= | [{};,>~+] | !important\b ) \s*+

|
	# spaces right of ( [ :
	( [[(:] ) \s++
|
	# spaces left of ) ]
	\s++ ( [])] )
|
	# spaces left (and right) of :
	\s++ ( : ) \s*+
	# but not in selectors: not followed by a {
	(?!
		(?>
			[^{}"']++
		|	"(?:[^"\\]++|\\.)*+"
		|	'(?:[^'\\]++|\\.)*+' 
		)*+
		{
	)
|
	# spaces at beginning/end of string
	^ \s++ | \s++ \z
|
	# double spaces to single
	(\s)\s+
%
EOS;

		//	MY EXTENDED minifiers!
		//	Some additional minification
		//	bold => 700 ... the problem right now with replacing `bold` is when it's inside a string!
		//					Because the RegExp starts from the top again! We need to protect comments!
		//					We could FORCE it to match only :bold; => :700; ... but it's still risky!
		//					The other issue with this is that it was replacing `.glyphicon-bold`
		//	0 0 0 0 => 0
		//	-0.5em => -.5em
		//	0px / 0% => 0	DISABLED!	WARNING: This will also convert a class name like: .border0px => .border0
		//	[type="button"] => [type=button]
		//	#aABbCC => #aBC
		$re3 = <<<'END'
~(?six)
	# quotes
	(
		"(?:[^"\\]++|\\.)*+"
	|	'(?:[^'\\]++|\\.)*+'
	)
|
	# [type="button"] => [type=button]
	# very basic/specific & restrictive conversion!
	(\[ [a-zA-Z\-]+ \=) ['"] ([^'"]+) ['"] (\])
|
	# -0.5em => -.5em
	# What about transition:color ease-in-out 0.2s ???
	(\D)0+(\.\d+)(\%|em|ex|px|in|cm|mm|pt|pc|rem)
|
	# 0px => 0
	# (\D0)(?:\%|em|ex|px|in|cm|mm|pt|pc|rem)(\W)
	# FUUUCK ... this fucking script isn't working, fuck this shit!
	# I wanted to PROTECT the conversion with the \W at the end
	# The \W was just an extra check; `Matches a non-alphanumeric character`
	# It doesn't pick up some cases like: border: 0px 0rem 0pt 0pc; => 0 0rem 0 0pc;
	#(\D0)(?:\%|em|ex|px|in|cm|mm|pt|pc|rem)(\W)
	# WARNING: I'm worried about replacing something like:
	#			.border0px{border:0}
#	(\D0)(?:\%|em|ex|px|in|cm|mm|pt|pc|rem)
	# Due to the situation of converting something like .border0px{} => .border0{} ... I've restricted this to 0px only with STRICTer checking!
	# I think this should work because classes cannot start with 0, so it will NOT match .border0px{} ... it would have matched .0px but this is an invalid class name!
	# We must also protect something like 5.0em ... LESS converts this to 5em, but we still need to protect it!
	([^a-z0-9\.]0)(?:\%|em|ex|px|in|cm|mm|pt|pc|rem)
~
END;

		$re4 = <<<'END'
~(?six)
	# quotes
	(
		"(?:[^"\\]++|\\.)*+"
	|	'(?:[^'\\]++|\\.)*+'
	)
|
	# #aABbCC => #aBC
	# WARNING:	This section uses HARD CODED BACK REFERENCES!
	#			If you move it, you must check the back references!
	(\#)([a-f0-9])\3([a-f0-9])\4([a-f0-9])\5(\W)
|
	# 0 0 0 0 => 0
	# this benefits from the other regular expression above where we shorten 0px
	#		because we convert something like `0px 0 0 0` => 0
	# pretty useless script!
	# because the conversion script above to convert 0rem => 0 doesn't work!
	(\W)(0)\ 0\ 0\ 0(\W)
~
END;

		$re5 = '~[^;\{\}]+\{\}~';	//	remove empty classes. Added on 3 Aug 2017 @ 12:17am

		return preg_replace([$re1, $re2, $re3, $re4, $re5], ['$1', '$1$2$3$4$5$6$7', '$1$2$3$4$5$6$7$8', '$1$2$3$4$5$6$7$8$9', null], $str);
	}
}


/**
 *	Notes on matching the @media section, which includes several inner styles:
 *
 *	eg.
 *	@media screen and (max-width: 767px) {
 *		.table {
 *			margin-bottom: 16px;
 *			overflow-y: hidden;
 *			overflow-x: auto;
 *		}
 *	}
 *
 *	This preg can match the whole outer @media section:
 *	preg_match_all('~([^{;}]+){([^{}]|(?R))*}~', $css, $split);
 *
 *	It uses a `recursive` search with `(?R)`
 *
 *	However, I cannot seem to match `all @media sections` like this doesn't work:
 *	'~(@media[^{;}]+){([^{}]|(?R))*}~'
 *
 *	The reason is, that the inner styles don't also start with @media, eg. @media { @media { ... } }
 *
 *	So I can't easily isolate `@media` from the rest,
 *		which means I have to check if every style starts with `@media`
 *		eg. strpos('@media', $str) === 0
 *		this is not acceptable!
 *
 *	So basically, I don't support templating inside the `@media` sections!
 *	And I also don't give any warnings about it!
 *
 *	IDEA: I can make a hard coded regex, that includes one or more inner {...} sections/rules?
 *
 *
 *	UPDATE:
 *
 *	After thinking about the idea I had, I came up with this:
 *	preg_match_all('~(?:\s*(@media)[^{;}]+{\s*(?:[^{;}]+\{[^{}]*\})*\s*})|(?:([^{;}]+){([^{}]+)})~', $css, $split);
 *
 *	It will separate the `@media` and normal rules, so that if ([1][$index] === '@media') then it's a @media section
 *		if ([1][$index] !== '@media') then [2][$index] will have the (normal) rule selector eg. '.button:hover'
 *		[3][$index] has the inner text of the normal rules
 *
 *
 *	UPDATE 2:
 *
 *	Currently I use a different regex:
 *
 *	preg_match_all('~@media[^{;}]*{(?:[^{;}]*{[^{}]*})+}~', $css, $media_sections);
 *
 *	which extracts all the @media sections, and checks to make sure they have no inner custom styling/theme CONSTANTS.
 *
 */