<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\User;

class Modification_Reserved_Connected implements Plugin
{
	public static function register()
	{
		Aop::add("after",
			"SAF\\Framework\\Output_Controller->getGeneralButtons()",
			array(__CLASS__, "afterOutputControllerGetGeneralButtons")
		);
	Aop::add("after",
		"SAF\\Framework\\Default_Edit_Controller->getViewParameters()",
		array(__CLASS__, "afterDefaultEditControllerGetViewParameters")
	);
	}
	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterOutputControllerGetGeneralButtons(AopJoinpoint $joinpoint){
		$is_connected = User::current();
		if($is_connected)
			$is_connected = $is_connected->login;
		if(!$is_connected){
			$joinpoint->setReturnedValue(array());
		}
	}

	//--------------------------------------------------- afterDefaultEditControllerGetViewParameters
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterDefaultEditControllerGetViewParameters(AopJoinpoint $joinpoint){
		$is_connected = User::current();
		if($is_connected)
			$is_connected = $is_connected->login;
		if(!$is_connected){
			$array = $joinpoint->getReturnedValue();
			$array["template_mode"] = null;
			$joinpoint->setReturnedValue($array);
		}
	}
}
