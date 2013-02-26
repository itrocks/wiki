<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;

class Image_Wiki_Link_Parse implements Plugin
{
	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * Read the text in parameter, and parse wiki format link to the textile link.
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
		$list_extension_regex = join("|", Images_Upload_Utils::$list_extension_accepted);
		$text = preg_replace_callback("#\[(.+)\.(" . $list_extension_regex . ")\]#", $callback, $text);
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
		return "!" . $matches[1] . "." . $matches[2] . "!";
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			'SAF\Framework\Wiki->textile()',
			array(__CLASS__, "beforeWikiTextile")
		);
	}

}
