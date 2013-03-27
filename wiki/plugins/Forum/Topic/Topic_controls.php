<?php
namespace SAF\Wiki;
use SAF\Framework\Plugin;
use SAF\Framework\Aop;
use \AopJoinpoint;


class Topic_controls implements Plugin{

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
		if(is_object($object) && get_class($object) == Forum_Utils::$namespace . "Topic"){
			$errors = self::testObject($object);
		}
		if(count($errors) == 0){
			$joinpoint->process();
		}
		else {
			$joinpoint->setReturnedValue($errors);
		}
	}

	//-------------------------------------------------------------------------------------- testForm
	/**
	 * Test the form, and put in array all errors. If there are not errors, array returned is empty.
	 * @param $object object
	 * @return array
	 */
	public static function testObject($object)
	{
		$errors = array();
		$error = Forum_Controller_Utils::testTitle($object);
		if($error != null)
			$errors[] = $error;
		$errorsContent = Post_controls::testObject($object->first_post);
		$errors = array_merge($errors, $errorsContent);
		return $errors;
	}
}