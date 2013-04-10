<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;

/**
 * Fix urls build by Textile.
 * Build urls as : /url
 */
class Fix_Link_Url implements Plugin
{

	//------------------------------------------------------------------------------ afterWikiTextile
	/**
	 * Read the text returned, and parse url.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterWikiTextile(AopJoinpoint $joinpoint)
	{
		$text = $joinpoint->getReturnedValue();
		$text = self::parseUrl($text);
		$joinpoint->setReturnedValue($text);
	}

	//-------------------------------------------------------------------------------------- parseUrl
	/**
	 * Parse wiki's links in a text to replace by textile's links.
	 * @param $text string The string to parse.
	 * @return string The parsed string
	 */
	static function parseUrl($text)
	{
		$callback = function($matches) {
			return self::formatUrl($matches);
		};
		$text = preg_replace_callback("#href\=\"(.+)\"#", $callback, $text);
		return $text;
	}

	//------------------------------------------------------------------------------------- formatUrl
	/**
	 * Replace in write format, it's a callable for preg_replace_callback.
	 * @param $matches string[]
	 * @return string
	 */
	static function formatUrl($matches)
	{
		if(filter_var($matches[1], FILTER_VALIDATE_URL)) {
			return "href=\"" . $matches[1] .  "\"";
		}
		else {
			return "href=\"" . "/". $matches[1] . "\"";
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\Wiki->textile()',
			array(__CLASS__, "afterWikiTextile")
		);
	}

}
