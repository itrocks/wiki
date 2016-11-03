<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Button;
use ITRocks\Framework\Default_Edit_Controller;
use ITRocks\Framework\Output_Controller;
use ITRocks\Framework\User;
use ITRocks\Plugins;

/**
 * This plugin delete all control of the output object if user is not connected.
 */
class Modification_Reserved_Connected implements Plugins\Registerable
{

	//--------------------------------------------------- afterDefaultEditControllerGetViewParameters
	/**
	 * Redirect the default edit in the output template if the user is not connected
	 *
	 * @param $result array
	 */
	public static function afterDefaultEditControllerGetViewParameters(&$result)
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
	public static function afterOutputControllerGetGeneralButtons(&$result)
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
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[ Output_Controller::class, 'getGeneralButtons' ],
			[ __CLASS__, 'afterOutputControllerGetGeneralButtons' ]
		);
		$aop->afterMethod(
			[ Default_Edit_Controller::class, 'getViewParameters' ],
			[ __CLASS__, 'afterDefaultEditControllerGetViewParameters' ]
		);
	}

}
