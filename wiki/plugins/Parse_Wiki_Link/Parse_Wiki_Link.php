<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;

class Parse_Wiki_Link implements Plugin
{

	//------------------------------------------------------------------------- beforeTextileParseURI
	/**
	 * When textile parse a link, each non / nor protocol:// links must be absolute instead of relative
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function beforeTextileParseURI(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$uri = $arguments[0];
		if ((substr($uri, 0, 1) !== "/") && !strpos($uri, "://")) {
			$arguments[0] = "/" . $uri;
			$joinpoint->setArguments($arguments);
		}
	}

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * Read the text in parameter, and parse wiki format link to the textile link.
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeWikiTextile(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$arguments[0] = self::parseWikiLinks($arguments[0]);
		$joinpoint->setArguments($arguments);
	}

	//------------------------------------------------------------------------------------- parseLink
	/**
	 * Parse [Wiki links] and replace then with "Wiki links":/Wiki_links
	 *
	 * @param $text string The string to parse.
	 * @return string The parsed string
	 */
	private static function parseWikiLinks($text)
	{
		$i = 0;
		$length = strlen($text);
		while (($i < $length) && (($i = strpos($text, "[", $i)) !== false)) {
			$i++;
			if (($i < $length)) {
				// escape with [[ => [ without parsing
				if ($text[$i] == "[") {
					$text = substr($text, 0, $i) . substr($text, $i + 1);
					$length --;
				}
				// parse link : replace [A link] with "A link":/A_link
				elseif (($text[$i] != "]") && ($j = strpos($text, "]", $i)) !== false) {
					$uri = substr($text, $i, $j - $i);
					$length -= (strlen($uri) + 2);
					$uri = "\"" . $uri . "\"" . ":" . str_replace(array(" ", "'"), "_", $uri);
					$uri_length = strlen($uri);
					$length += $uri_length;
					$text = substr($text, 0, $i - 1) . $uri . substr($text, $j + 1);
					$i += $uri_length;
				}
			}
		}
		return $text;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			'SAF\Framework\Wiki->textile()',
			array(__CLASS__, "beforeWikiTextile")
		);
		Aop::add("before",
			'SAF\Framework\Textile->parseURI()',
			array(__CLASS__, "beforeTextileParseURI")
		);
	}

}
