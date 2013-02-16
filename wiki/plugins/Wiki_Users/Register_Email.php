<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Input;
use SAF\Framework\Plugin;

class Register_Email implements Plugin
{

	//--------------------------------------------------- afterUserAuthenticationBuildUserForRegister
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationArrayToUser(AopJoinpoint $joinpoint)
	{
		$user = $joinpoint->getReturnedValue();
		$user->email = $joinpoint->getArguments()[0]["email"];
		$joinpoint->setReturnedValue($user);
	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationControlRegisterFormParameters(
		AopJoinpoint $joinpoint
	) {
		if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $joinpoint->getArguments()[0]["email"]))
		{
			$value = $joinpoint->getReturnedValue();
			$value[] = array("name" => "Email error", "message" => "Email must be a valid email format.");
			$joinpoint->setReturnedValue($value);
		}
	}

	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationGetRegisterInputs(AopJoinpoint $joinpoint)
	{
		$list_inputs = $joinpoint->getReturnedValue();
		$list_inputs[] = new Input("email", "Email address", "text");
		$joinpoint->setReturnedValue($list_inputs);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\User_Authentication->getRegisterInputs()',
			array(__CLASS__, "afterUserAuthenticationGetRegisterInputs")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->controlRegisterFormParameters()',
			array(__CLASS__, "afterUserAuthenticationControlRegisterFormParameters")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->arrayToUser()',
			array(__CLASS__, "afterUserAuthenticationArrayToUser")
		);
	}

}
