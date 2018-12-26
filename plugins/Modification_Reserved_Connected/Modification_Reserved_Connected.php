<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Edit;
use ITRocks\Framework\Widget\Output;

/**
 * This plugin delete all control of the output object if user is not connected.
 */
class Modification_Reserved_Connected implements Registerable
{

	//--------------------------------------------------- afterDefaultEditControllerGetViewParameters
	/**
	 * Redirect the default edit in the output template if the user is not connected
	 *
	 * @param $result array
	 */
	public static function afterDefaultEditControllerGetViewParameters(array &$result)
	{
		$is_connected = User::current();
		if ($is_connected) {
			$is_connected = $is_connected->login;
		}
		if (!$is_connected) {
			$result['template_mode'] = null;
		}
	}

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * Remove all control buttons of outputs if the user is not connected.
	 *
	 * @param $result Button[]
	 */
	public static function afterOutputControllerGetGeneralButtons(array &$result)
	{
		$is_connected = User::current();
		if ($is_connected) {
			$is_connected = $is_connected->login;
		}
		if (!$is_connected) {
			$result = [];
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'],
			[__CLASS__, 'afterOutputControllerGetGeneralButtons']
		);
		$aop->afterMethod(
			[Edit\Controller::class, 'getViewParameters'],
			[__CLASS__, 'afterDefaultEditControllerGetViewParameters']
		);
	}

}
