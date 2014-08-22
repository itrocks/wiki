<?php
namespace SAF\Wiki;

use SAF\Framework\Wiki;
use SAF\Plugins;

class Image_Wiki_Link_Parse implements Plugins\Registerable
{

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * Read the text in parameter, and parse wiki format link to the textile link.
	 */
	public static function beforeWikiTextile(&$string)
	{
		$string = self::parseLink($string);
	}

	//------------------------------------------------------------------------------------ formatLink
	/**
	 * Replace in write format, it's a callable for preg_replace_callback.
	 *
	 * @param $matches string[]
	 * @return string
	 */
	public static function formatLink($matches)
	{
		return '!/' . $matches[1] . '.' . $matches[2] . '!';
	}

	//------------------------------------------------------------------------------------- parseLink
	/**
	 * Parse wiki's links in a text to replace by textile's links.
	 *
	 * @param $text string The string to parse.
	 * @return string The parsed string
	 */
	private static function parseLink($text)
	{
		$callback = [__CLASS__, 'formatLink'];
		$list_extension_regex = join('|', Images_Upload_Utils::$list_extension_accepted);
		$text = preg_replace_callback('#\[(.+)\.(' . $list_extension_regex . ')\]#', $callback, $text);
		return $text;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$register->aop->beforeMethod([ Wiki::class, 'textile' ], [ __CLASS__, 'beforeWikiTextile' ]);
	}

}
