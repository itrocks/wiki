<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Input;

class Register_Email implements Plugin
{
	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->getRegisterInputs()",
			array(__CLASS__, "afterUserAuthenticationGetRegisterInputs")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->controlRegisterFormParameters()",
			array(__CLASS__, "afterUserAuthenticationControlRegisterFormParameters")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->arrayToUser()",
			array(__CLASS__, "afterUserAuthenticationArrayToUser")
		);
	}
	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	public static function afterUserAuthenticationGetRegisterInputs(AopJoinpoint $joinpoint){
		$listInputs = $joinpoint->getReturnedValue();
		$listInputs[] = new Input("email", "Email address", "text");
		$joinpoint->setReturnedValue($listInputs);
	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	public static function afterUserAuthenticationControlRegisterFormParameters(AopJoinpoint $joinpoint){
		if(!preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $joinpoint->getArguments()[0]["email"])){
			$value = $joinpoint->getReturnedValue();
			$value[] = array("name" => "Email error", "message" => "Email must be a valid email format.");
			$joinpoint->setReturnedValue($value);
		}
	}

	//--------------------------------------------------- afterUserAuthenticationBuildUserForRegister
	public static function afterUserAuthenticationArrayToUser(AopJoinpoint $joinpoint){
		$user = $joinpoint->getReturnedValue();
		$user->email = $joinpoint->getArguments()[0]["email"];
		$joinpoint->setReturnedValue($user);
	}
}
