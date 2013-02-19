<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;
use SAF\Framework\Reflection_Class;
use SAF\Framework\User;

/**
 * This plugin delete all control of the output object if user is not connected.
 */
class Modification_Reserved_Connected implements Plugin
{

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * Remove all control buttons of outputs if the user is not connected.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterOutputControllerGetGeneralButtons(AopJoinpoint $joinpoint)
	{
		$is_connected = User::current();
		if ($is_connected) {
			$is_connected = $is_connected->login;
		}
		if (!$is_connected) {
			$joinpoint->setReturnedValue(array());
		}
	}

	//--------------------------------------------------- afterDefaultEditControllerGetViewParameters
	/**
	 * Redirect the default edit in the output template if the user is not connected.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterDefaultEditControllerGetViewParameters(AopJoinpoint $joinpoint)
	{
		$is_connected = User::current();
		if ($is_connected) {
			$is_connected = $is_connected->login;
		}
		if (!$is_connected) {
			$array = $joinpoint->getReturnedValue();
			$array["template_mode"] = null;
			$joinpoint->setReturnedValue($array);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\Output_Controller->getGeneralButtons()',
			array(__CLASS__, "afterOutputControllerGetGeneralButtons")
		);
		Aop::add("after",
			'SAF\Framework\Default_Edit_Controller->getViewParameters()',
			array(__CLASS__, "afterDefaultEditControllerGetViewParameters")
		);
	}

}
