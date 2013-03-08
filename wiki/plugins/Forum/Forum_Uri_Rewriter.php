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
			$str = Forum_Utils::uriToArray($link);
			if(strtolower($str[0]) == "forum" && (!isset($str[1]) || !is_numeric($str[1]))){
				$getters = $joinpoint->getArguments()[1];
				$params = array();
				for($i = 1; $i < count($str); $i++){
					$params[] = $str[$i];
				}
				$element = Forum_Utils::getElementOnGetters($getters);
				$answer = Forum_Utils::getElementsRequired($params, $element);
				$mode = "output";
				if(isset($getters["mode"])){
					$mode = $getters["mode"];
				}
				$link_read[0] = self::getTypeElement($answer["path"]);
				if(isset($answer["element"])){
					$link_read[1] = Dao::getObjectIdentifier($answer["element"]);
					$link_read[2] = $mode;
				}
				else {
					$link_read[1] = "list_all";
				}
				$link = Forum_Utils::arrayToUri($link_read);
				$arguments[0] = $link;
				\SAF\Framework\Session::current()->set(Forum_Path::current(new Forum_Path($answer["path"])));
				$joinpoint->setArguments($arguments);
			}
		}
	}

	/**
	 * Return the type of the current element.
	 * @param $str
	 * @return string
	 */
	private static function getTypeElement($str)
	{
		switch(count($str)){
			case 1;
				return "Category";
			case 2;
				return "Forum";
			case 3;
				return "Topic";
			case 4;
				return "Post";
			default:
				return "Category";
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			'SAF\Framework\Main_Controller->runController()',
			array(__CLASS__, "beforeMainControllerRunController")
		);
	}

}
