<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\User;
use SAF\Framework\View;

class Edition_Reserved_To_Author implements Plugin
{

	private static $methods_controlled = array(
		array("Forum_Buttons_Utils", "getButtonsTopicModeOutputPrivate"),
		array("Forum_Buttons_Utils", "getButtonsPostModeOutputPrivate"),
		array("Forum_Controller_Utils", "deleteObject"),
		array("Forum_Controller_Utils", "writeObject")
	);

	private static $methods_controller_controlled = array(
		array("Topic_Controller", "edit"),
		array("Topic_Controller", "delete"),
		array("Topic_Controller", "write"),
		array("Post_Controller", "edit"),
		array("Post_Controller", "delete"),
		array("Post_Controller", "write")
	);

	//-------------------------------------------------------------- aroundControlWithObjectParameter
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundControlWithObjectParameter(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		self::control($joinpoint, $object);
	}

	//--------------------------------------------------------------------------------------- control
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundControlWithControllerParameters(AopJoinpoint $joinpoint)
	{
		/** @var $parameters Controller_Parameters */
		$parameters = $joinpoint->getArguments()[0];
		$objects = $parameters->getObjects();
		$is_process = false;
		$has_object = false;
		foreach($objects as $object){
			$has_object = $has_object || is_object($object);
			$is_process = self::control($joinpoint, $object);
			if($is_process)
				break;
		}
		if(!$has_object){
			$joinpoint->process();
			$is_process = true;
		}

		if(!$is_process){
			$class_name = $joinpoint->getArguments()[3];
			$path = Forum_Path_Utils::getPath();
			$parameters = Forum_Controller_Utils::getViewParameters($parameters, $class_name);
			/** @var $parameters array */
			$parameters = Forum_Path_Utils::addPathAttribute($parameters, $path);
			$parameters["message"] =
				"You have not the rights to modify this element.";
			$base_url = Forum_Url_Utils::getBaseUrl($path);
			if($parameters["mode"] == "new")
				$base_url = Forum_Url_Utils::getParentUrl($base_url);
			$parameters["link"] = $base_url;
			$view = View::run($parameters, array(), array(), 'Access', 'denied');
			$joinpoint->setReturnedValue($view);
		}
	}

	private static function control(AopJoinpoint $joinpoint, $object)
	{
		if(is_object($object)){
			switch(get_class($object)){
				case "SAF\\Wiki\\Post" :
					Forum_Utils::assignAuthorInPost($object);
					if(User::current() == $object->author || Forum_Utils::isNotFound($object)){
						$joinpoint->process();
						return true;
					}
					break;
				case "SAF\\Wiki\\Topic" :
					Forum_Utils::assignTopicFirstPost($object);
					return self::control($joinpoint, $object->first_post);
					break;
				default :
					$joinpoint->process();
					return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		foreach(self::$methods_controlled as $element){
			Aop::add("around",
				'SAF\Wiki\\' . $element[0] . '->' . $element[1] . '()',
				array(__CLASS__, "aroundControlWithObjectParameter")
			);
		}
		foreach(self::$methods_controller_controlled as $element){
			Aop::add("around",
				'SAF\Wiki\\' . $element[0] . '->' . $element[1] . '()',
				array(__CLASS__, "aroundControlWithControllerParameters")
			);
		}
	}
}
