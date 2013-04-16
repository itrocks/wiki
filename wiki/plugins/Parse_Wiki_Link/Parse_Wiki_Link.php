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
		$text = $arguments[0];
		$text = self::parseLink($text);
		$arguments[0] = $text;
		$joinpoint->setArguments($arguments);
	}

	//------------------------------------------------------------------------------------- parseLink
	/**
	 * Parse wiki's links in a text to replace by textile's links.
	 * @param $text string The string to parse.
	 * @return string The parsed string
	 */
	static function parseLink($text)
	{
		$callback = function($matches) {
			return self::formatLink($matches);
		};
		$text = preg_replace_callback("#\[\[(.+)\]\]#", $callback, $text);
		$text = preg_replace_callback("#\[(.+)\]#", $callback, $text);
		return $text;
	}

	//------------------------------------------------------------------------------------ formatLink
	/**
	 * Replace in write format, it's a callable for preg_replace_callback.
	 * @param $matches string[]
	 * @return string
	 */
	static function formatLink($matches)
	{
		return "\"" . $matches[1]. "\"" . ":/" . str_replace(" ", "_", $matches[1]);
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
