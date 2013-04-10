<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Namespaces;
use SAF\Framework\Session;

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
			$str = Forum_Url_Utils::uriToArray($link);
			if($str && strtolower($str[0]) == "forum" && (!isset($str[1]) || !is_numeric($str[1]))){
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
				if(strtolower($mode) == "new"){
					$class_children = Forum_Names_Utils::getNextClass($answer["element"]);
					$short_class_name = Namespaces::shortClassName($class_children);
					$new_element = new $class_children();
					$new_element->title = "New " . $short_class_name;
					$answer["path"][$short_class_name] = $new_element;
					$answer["element"] = $new_element;
				}
				$type = self::getTypeElement($answer["path"]);
				$link_read[0] = $type;
				if(isset($answer["element"])){
					$link_read[1] = Dao::getObjectIdentifier($answer["element"]);
					if($link_read[1] == null){
						if(strtolower($mode) == "output")
							$mode = "new";
						$link_read[1] = $mode;
					}
					else {
						$link_read[2] = $mode;
					}
				}
				else {
					$link_read[1] = "list_all";
				}
				$link = Forum_Url_Utils::arrayToUri($link_read);
				$arguments[0] = $link;
				$arguments[1] = self::cleanGetters($getters);
				Session::current()->set(Forum_Path::current(new Forum_Path($answer["path"])));
				$joinpoint->setArguments($arguments);
			}
		}
	}

	//---------------------------------------------------------------------------------- cleanGetters
	/**
	 * Clean getters, remove the objects id put in parameters.
	 * @param $getters array
	 * @return array
	 */
	public static function cleanGetters($getters)
	{
		foreach($getters as $key => $getter){
			switch(strtolower($key)){
				case "post":
				case "topic":
				case "forum":
				case "category":
					unset($getters[$key]);
			}
		}
		return $getters;
	}

	/**
	 * Return the type of the current element.
	 * @param $count array|int
	 * @return string
	 */
	private static function getTypeElement($count)
	{
		if(is_array($count))
			$count = count($count);
		switch($count){
			case 2:
				return "Forum";
			case 3:
				return "Topic";
			case 4:
				return "Post";
			case 1:
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
