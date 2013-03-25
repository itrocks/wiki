<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Only_Output_For_Not_Connected implements Plugin
{

	private static $methods_output = array(
		array("Category_Controller", "edit"),
		array("Category_Controller", "delete"),
		array("Category_Controller", "write"),
		array("Category_New_Controller", "run"),
		array("Forum_Controller", "edit"),
		array("Forum_Controller", "delete"),
		array("Forum_Controller", "write"),
		array("Forum_New_Controller", "run"),
		array("Topic_Controller", "edit"),
		array("Topic_Controller", "delete"),
		array("Topic_Controller", "write"),
		array("Topic_New_Controller", "run"),
		array("Post_Controller", "edit"),
		array("Post_Controller", "delete"),
		array("Post_Controller", "write"),
		array("Post_New_Controller", "run")
	);

	private static $methods_disable = array(
		array("Forum_Buttons_Utils", "getButtons"),
		array("Forum_Utils", "getButtons"),
		array("Forum_Utils", "getBottomButtons"),
		array("Forum_Controller_Utils", "delete"),
		array("Forum_Controller_Utils", "write")
	);

	//-------------------------------------------------------------------------- changeToOutputReturn
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function changeToErrorMessageReturn(AopJoinpoint $joinpoint)
	{
		$user = User::current();
		if(isset($user)){
			$joinpoint->process();
		}
		else {
			$parameters = $joinpoint->getArguments()[0];
			$class_name = $joinpoint->getArguments()[3];
			$parameters = Forum_Controller_Utils::getViewParameters($parameters, $class_name);
			$path = Forum_Path_Utils::getPath();
			$parameters = Forum_Path_Utils::addPathAttribute($parameters, $path);
			$parameters["message"] =
				"You have not access to this part of the forum because you're not connected";
			$base_url = Forum_Url_Utils::getBaseUrl($path);
			if($parameters["mode"] == "new")
				$base_url = Forum_Url_Utils::getParentUrl($base_url);
			$parameters["link"] = $base_url;
			$view = View::run($parameters, array(), array(), 'Access', 'denied');
			$joinpoint->setReturnedValue($view);
		}
	}

	//-------------------------------------------------------------------------- changeToOutputReturn
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function disableMethod(AopJoinpoint $joinpoint)
	{
		$user = User::current();
		if(isset($user)){
			$joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		foreach(self::$methods_output as $element){
			Aop::add("around",
				'SAF\Wiki\\' . $element[0] . '->' . $element[1] . '()',
				array(__CLASS__, "changeToErrorMessageReturn")
			);
		}
		foreach(self::$methods_disable as $element){
			Aop::add("around",
				'SAF\Wiki\\' . $element[0] . '->' . $element[1] . '()',
				array(__CLASS__, "disableMethod")
			);
		}
	}
}
