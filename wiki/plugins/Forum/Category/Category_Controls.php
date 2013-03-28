<?php
namespace SAF\Wiki;
use SAF\Framework\Plugin;
use SAF\Framework\Aop;
use \AopJoinpoint;


class Category_Controls implements Plugin{

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
		if(is_object($object) && get_class($object) == Forum_Utils::$namespace . "Category"){
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
		$error = Forum_Controller_Utils::testTitle($object);
		if($error != null)
			$errors[] = $error;
		return $errors;
	}
}
