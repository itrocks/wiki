<?php
namespace SAF\Wiki;

use SAF\Framework\Input;
use SAF\Framework\User;
use SAF\Framework\User_Authentication;
use SAF\Plugins;

class Register_Email implements Plugins\Registerable
{

	//--------------------------------------------------- afterUserAuthenticationBuildUserForRegister
	/**
	 * @param $array array
	 * @param $result User
	 */
	public static function afterUserAuthenticationArrayToUser($array, $result)
	{
		$result->email = $array['email'];
	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	/**
	 * @param $form   array
	 * @param $result array
	 */
	public static function afterUserAuthenticationControlRegisterFormParameters($form, &$result)
	{
		if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $form['email'])) {
			$result[] = array('name' => 'Email error', 'message' => 'Email must be a valid email format.');
		}
	}

	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	/**
	 * @param $result Input[]
	 */
	public static function afterUserAuthenticationGetRegisterInputs(&$result)
	{
		$result[] = new Input('email', 'Email address', 'text');
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[ User_Authentication::class, 'getRegisterInputs' ],
			[ __CLASS__, 'afterUserAuthenticationGetRegisterInputs' ]
		);
		$aop->afterMethod(
			[ User_Authentication::class, 'controlRegisterFormParameters' ],
			[ __CLASS__, 'afterUserAuthenticationControlRegisterFormParameters']
		);
		$aop->afterMethod(
			[ User_Authentication::class, 'arrayToUser' ],
			[ __CLASS__, 'afterUserAuthenticationArrayToUser' ]
		);
	}

}
