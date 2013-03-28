<?php
namespace SAF\Wiki;
use SAF\Framework\Plugin;
use SAF\Framework\Aop;
use \AopJoinpoint;


class Post_Controls implements Plugin{

	public static function register()
	{
		Aop::add("around",
			'SAF\Wiki\Forum_Controller_Utils->writeCompleteObject()',
			array(__CLASS__, "aroundForumControllerUtilsWriteCompleteObject")
		);
	}

	//------------------------------------------------- aroundForumControllerUtilsWriteCompleteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumControllerUtilsWriteCompleteObject(AopJoinpoint $joinpoint){
		$object = $joinpoint->getArguments()[0];
		$errors = array();
		if(is_object($object) && get_class($object) == Forum_Utils::$namespace . "Post"){
			$errors = self::testObject($object);
		}
		if(count($errors) == 0){
			$joinpoint->process();
		}
		else {
			$joinpoint->setReturnedValue($errors);
		}
	}

	//------------------------------------------------------------------------------------ testObject
	/**
	 * Test the object, and put in array all errors. If there are not errors, array returned is empty.
	 * @param $object object
	 * @return array
	 */
	public static function testObject($object)
	{
		$errors = array();
		if(!isset($object) || strlen($object->content) < 3)
			$errors[] = "The message must contain at least 3 characters.";
		return $errors;
	}
}
