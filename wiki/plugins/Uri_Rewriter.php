<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;

class Uri_Rewriter implements Plugin
{

	//---------------------------------------------------------------------------------- $ignore_list
	/**
	 * @todo some ignores come from plugins and should be declared into these plugins, not here
	 * @var string[]
	 */
	public static $ignore_list = array(
		"Menu", "User", "Page", "Search", "Content", "Upload", "Images_Upload",
		"uploaded_img", "Forum", "Category", "Post", "Topic", "Forum_Search"
	);

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
		$list_orders = array("edit", "output", "new", "delete", "included");
		$link = $joinpoint->getArguments()[0];
		if (!$link || ($link == "/")) {
			$link = "/Accueil";
		}
		$class_name = 'SAF\Wiki\Page';
		$str = self::uriToArray($link);
		if ($str && self::notIgnored($str[0], self::$ignore_list)) {
			if (
				!$str
				|| ($str && count($str) && (strtolower($str[0]) == "new"))
				|| ($str && (count($str) < 1))
			) {
				$str[0] = $class_name;
				$str[1] = "new";
			}
			elseif (
				$str && count($str)
				&& !($str && (count($str) > 1) && ((strtolower($str[1]) == "write") || is_numeric($str[1])))
			) {
				if (@class_exists($class_name)) {
					$object = Builder::create($class_name);
					$name = $str[0];
					$name = trim(str_replace("_", " ", $name));
					$object->name = $name;
					$result = Dao::searchOne($object, $class_name);
					if ($result) {
						$order = "output";
						if(isset($str[1]) && self::isOrder($str[1], $list_orders)){
							$order = strtolower($str[1]);
						}
						$str[0] = $class_name;
						$str[1] = Dao::getObjectIdentifier($result);
						$str[2] = $order;
					}
					else {
						$str[0] = $class_name;
						$str[1] = "new";
					}
				}
				self::putInArguments($str, $joinpoint);
			}
		}
	}

	//--------------------------------------------------------------------------------------- isOrder
	/**
	 * @param $item_test  string
	 * @param $list_order string[]
	 * @return boolean
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

	//------------------------------------------------------------------------------------ notIgnored
	/**
	 * @param $item_test   string
	 * @param $list_ignore string[]
	 * @return boolean
	 */
	private static function notIgnored($item_test, $list_ignore)
	{
		foreach ($list_ignore as $item_ignored) {
			if ($item_ignored == $item_test) {
				return false;
			}
		}
		return true;
	}

	//-------------------------------------------------------------------------------- putInArguments
	/**
	 * @param $str       string[]
	 * @param $joinpoint AopJoinpoint
	 */
	private static function putInArguments($str, AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$arguments[0] = self::arrayToUri($str);
		$joinpoint->setArguments($arguments);
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
