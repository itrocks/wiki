<?php
namespace SAF\Wiki;
use SAF\Framework\Namespaces;
use SAF\Framework\Plugin;
use SAF\Framework\Aop;
use \AopJoinpoint;


class Ascent_Controls implements Plugin{

	public static function register()
	{
		Aop::add("around",
			'SAF\Wiki\Forum_Controller_Utils->writeCompleteObject()',
			array(__CLASS__, "aroundForumControllerUtilsWriteCompleteObject")
		);
	}

	//------------------------------------------------------- ForumControllerUtilsWriteCompleteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumControllerUtilsWriteCompleteObject(AopJoinpoint $joinpoint){
		$object = $joinpoint->getArguments()[0];
		$errors = array();
		if(is_object($object) && get_class($object) == Forum_Utils::$namespace . "Forum"){
		}
		while(
			($object = Forum_Utils::getParentObject($object)) != null
			&& Forum_Utils::isNotFound($object)
		){
			$name = get_class($object) . "_Controls";
			$method_name = "testObject";
			if(method_exists($name , $method_name)){
				$errors = array_merge($errors, $name::$method_name($object));
			}
		}
		if(count($errors) == 0){
			$joinpoint->process();
		}
		else {
			$joinpoint->setReturnedValue($errors);
		}
	}
}
