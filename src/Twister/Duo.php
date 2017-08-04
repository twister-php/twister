<?php

namespace Twister;

class Duo
{
	static function duo($masterfile, $palette = null, $tint = null, array $overrides = null)
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

		return preg_replace([$re1, $re2, $re3, $re4], ['$1', '$1$2$3$4$5$6$7', '$1$2$3$4$5$6$7$8', '$1$2$3$4$5$6$7$8$9'], $str);
	}
}
