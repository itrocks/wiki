<?php
namespace ITRocks\Wiki\Plugins\Wiki_Users;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\Widget\Input;

/**
 * Register email
 */
class Register_Email implements Registerable
{

	//------------------------------------------------------------ afterUserAuthenticationArrayToUser
	/**
	 * @param $array array
	 * @param $result User
	 */
	public static function afterUserAuthenticationArrayToUser(array $array, User $result)
	{
		$result->email = $array['email'];
	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	/**
	 * @param $form   array
	 * @param $result array
	 */
	public static function afterUserAuthenticationControlRegisterFormParameters(
		array $form, array &$result
	) {
		if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $form['email'])) {
			$result[] = ['name' => 'Email error', 'message' => 'Email must be a valid email format.'];
		}
	}

	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	/**
	 * @param $result Input[]
	 */
	public static function afterUserAuthenticationGetRegisterInputs(array &$result)
	{
		$result[] = new Input('email', 'Email address', 'text');
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[Authentication::class, 'getRegisterInputs'],
			[__CLASS__, 'afterUserAuthenticationGetRegisterInputs']
		);
		$aop->afterMethod(
			[Authentication::class, 'controlRegisterFormParameters'],
			[__CLASS__, 'afterUserAuthenticationControlRegisterFormParameters']
		);
		$aop->afterMethod(
			[Authentication::class, 'arrayToUser'],
			[__CLASS__, 'afterUserAuthenticationArrayToUser']
		);
	}

}
