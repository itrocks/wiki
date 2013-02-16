<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;

class Parse_Wiki_Link implements Plugin
{

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeWikiTextile(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$text = $arguments[0];
		$callback = function($matches) {
			return self::formatLink($matches);
		};
		$text = preg_replace_callback("#\[\[(.+)\]\]#", $callback, $text);
		$text = preg_replace_callback("#\[(.+)\]#", $callback, $text);
		$arguments[0] = $text;
		$joinpoint->setArguments($arguments);
	}

	//------------------------------------------------------------------------------------ formatLink
	/**
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
