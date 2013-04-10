<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Loc;
use SAF\Framework\Plugin;

class Uri_Rewriter implements Plugin
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Current feature after url rewriting
	 *
	 * @var string
	 */
	public static $feature;

	//-------------------------------------------------------------------------------- $features_list
	public static $features_list = array(
		"delete", "edit", "include", "new", "output", "write"
	);

	//---------------------------------------------------------------------------------- $ignore_list
	/**
	 * @todo some ignores come from plugins and should be declared into these plugins, not here
	 * @var string[]
	 */
	public static $ignore_list = array(
		"Images", "Menu", "Page", "Search", "User"
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
		$arguments = $joinpoint->getArguments();
		$link = $arguments[0];
		if (!$link || ($link == "/")) {
			$link = "/" . Loc::tr("Home");
		}
		$page_class = 'SAF\Wiki\Page';
		$parameters = self::uriToArray($link);
		if ($parameters && !self::isIgnored($parameters[0], self::$ignore_list)) {
			if (
				!$parameters
				|| ($parameters && count($parameters) && (strtolower($parameters[0]) == "new"))
				|| ($parameters && (count($parameters) < 1))
			) {
				$feature = "new";
				$parameters[0] = $page_class;
				$parameters[1] = $feature;
			}
			elseif ($parameters && count($parameters)) {
				if (@class_exists($page_class)) {
					$name = trim(str_replace("_", " ", $parameters[0]));
					/** @var $page_search Page */
					$page_search = Builder::create($page_class);
					$page_search->name = $name;
					/** @var $page Page */
					$page = Dao::searchOne($page_search, $page_class);
					if ($page) {
						Page::current($page);
						$feature = "output";
						if(isset($parameters[1]) && self::isFeature($parameters[1], self::$features_list)) {
							$feature = strtolower($parameters[1]);
						}
						$parameters[0] = $page_class;
						$parameters[1] = Dao::getObjectIdentifier($page);
						$parameters[2] = $feature;
					}
					else {
						$feature = "new";
						$parameters[0] = $page_class;
						$parameters[1] = $feature;
					}
					self::$feature = $feature;
				}
				$arguments[0] = self::arrayToUri($parameters);
				$joinpoint->setArguments($arguments);
			}
		}
		else {
			if (isset($parameters[1]) && $parameters[1] == "new") {
				self::$feature = $parameters[1];
			}
		}
	}

	//------------------------------------------------------------------------------------- isFeature
	/**
	 * Returns true if $test is a feature from $features_list
	 *
	 * @param $test          string
	 * @param $features_list string[]
	 * @return boolean
	 */
	private static function isFeature($test, $features_list)
	{
		foreach ($features_list as $feature) {
			if (strtolower($feature) == strtolower($test)) {
				return true;
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------- isIgnored
	/**
	 * @param $test        string
	 * @param $ignore_list string[]
	 * @return boolean
	 */
	private static function isIgnored($test, $ignore_list)
	{
		foreach ($ignore_list as $ignore) {
			if ($ignore == $test) {
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
