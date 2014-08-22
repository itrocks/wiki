<?php
namespace SAF\Wiki;

use SAF\Framework\Textile;
use SAF\Framework\Wiki;
use SAF\Plugins;

class Parse_Wiki_Link implements Plugins\Registerable
{

	//------------------------------------------------------------------------- beforeTextileParseURI
	/**
	 * When textile parse a link, each non / nor protocol:// links must be absolute instead of relative
	 *
	 * @param $uri string
	 */
	public static function beforeTextileParseURI(&$uri)
	{
		if ((substr($uri, 0, 1) !== '/') && !strpos($uri, '://')) {
			$uri = '/' . $uri;
		}
	}

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * Read the text in parameter, and parse wiki format link to the textile link.
	 *
	 * @param $string string
	 */
	public static function beforeWikiTextile(&$string)
	{
		$string = self::parseWikiLinks($string);
	}

	//------------------------------------------------------------------------------------- parseLink
	/**
	 * Parse [Wiki links] and replace then with 'Wiki links':/Wiki_links
	 *
	 * @param $string string The string to parse.
	 * @return string The parsed string
	 */
	private static function parseWikiLinks($string)
	{
		$i = 0;
		$length = strlen($string);
		while (($i < $length) && (($i = strpos($string, '[', $i)) !== false)) {
			$i++;
			if (($i < $length)) {
				// escape with [[ => [ without parsing
				if ($string[$i] == '[') {
					$string = substr($string, 0, $i) . substr($string, $i + 1);
					$length --;
				}
				// parse link : replace [A link] with 'A link':/A_link
				elseif (($string[$i] != ']') && ($j = strpos($string, ']', $i)) !== false) {
					$uri = substr($string, $i, $j - $i);
					$length -= (strlen($uri) + 2);
					$uri = '"' . $uri . '"' . ':' . str_replace(array(' ', "'"), '_', $uri);
					$uri_length = strlen($uri);
					$length += $uri_length;
					$string = substr($string, 0, $i - 1) . $uri . substr($string, $j + 1);
					$i += $uri_length;
				}
			}
		}
		return $string;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([ Wiki::class, 'textile' ], [ __CLASS__, 'beforeWikiTextile' ]);
		$aop->beforeMethod([ Textile::class, 'parseURI' ], [ __CLASS__, 'beforeTextileParseURI' ]);
	}

}
