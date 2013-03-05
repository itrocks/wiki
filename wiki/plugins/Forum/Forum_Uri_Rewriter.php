<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;
use SAF\Framework\Reflection_Class;

class Forum_Uri_Rewriter implements Plugin
{

	//------------------------------------------------------------------------------------ arrayToUri
	/**
	 * @param $array string[]
	 * @return string
	 */
	private static function arrayToUri($array)
	{
		$uri = "";
		$isGets = false;
		foreach ($array as $element) {
			if (strstr($element, "?") == true) {
				$isGets = true;
			}
			if ($isGets) {
				$uri .= $element;
			}
			else {
				$uri .= "/" . $element;
			}
		}
		return $uri;
	}

	//------------------------------------------------------------- beforeMainControllerRunController
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeMainControllerRunController(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$link = $arguments[0];
		$link_read = array();
		if ($link) {
			$str = self::uriToArray($link);
			if(strtolower($str[0]) == "forum"){
				$link_read[0] = $str[0];
				$link_read[1] = self::getTypeElement($str);
				for($i = 1; $i < count($str); $i++){
					$link_read[$i+1] = $str[$i];
				}
				$link = self::arrayToUri($link_read);
				$arguments[0] = $link;
				$joinpoint->setArguments($arguments);
			}
		}
	}

	private static function getTypeElement($str){
		switch(count($str)){
			case 2;
				return "Category";
			case 3;
				return "Forum";
			case 4;
				return "Topic";
			case 5;
				return "Post";
			default:
				return "";
		}
	}

	//--------------------------------------------------------------------------------------- isOrder
	/**
	 * @param $item_test string
	 * @param $list_order string[]
	 * @return bool
	 */
	private static function isOrder($item_test, $list_order)
	{
		foreach ($list_order as $item_order) {
			if (strtolower($item_order) == strtolower($item_test)) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			'SAF\Framework\Main_Controller->runController()',
			array(__CLASS__, "beforeMainControllerRunController")
		);
	}

	//------------------------------------------------------------------------------------ uriToArray
	/**
	 * @param $uri string
	 * @return string[]
	 */
	private static function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		if (end($uri) === "") {
			array_pop($uri);
		}
		return $uri;
	}

}
