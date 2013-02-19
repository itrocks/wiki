<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;

class Parse_Wiki_Link implements Plugin
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
		return "\"" . $matches[1]. "\"" . ":" . str_replace(" ", "_", $matches[1]);
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
