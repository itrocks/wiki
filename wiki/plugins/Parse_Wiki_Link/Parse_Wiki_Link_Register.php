<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Namespaces;

class Parse_Wiki_Link_Register implements Plugin
{
	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			"SAF\\Framework\\Wiki->textile()",
			array(__CLASS__, "beforeWikiTextile")
		);
	}

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeWikiTextile(AopJoinpoint $joinpoint){
		$arguments = $joinpoint->getArguments();
		$text = $arguments[0];
		$callback = function( $matches )  {
			return self::formatLink($matches);
		};
		$text = preg_replace_callback("#\[\[(.+)\]\]#", $callback, $text);
		$text = preg_replace_callback("#\[(.+)\]#", $callback, $text);
		$arguments[0] = $text;
		$joinpoint->setArguments($arguments);
	}

	static function formatLink($matches)
	{
		return "\"" . $matches[1]. "\"" . ":" . str_replace(" ", "_", $matches[1]);
	}
}
