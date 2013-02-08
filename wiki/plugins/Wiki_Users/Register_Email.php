<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;

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
			"SAF\\Framework\\User_Authentication->buildUserForRegister()",
			array(__CLASS__, "afterUserAuthenticationBuildUserForRegister")
		);
	}
	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	public static function afterUserAuthenticationGetRegisterInputs(AopJoinpoint $joinpoint){
		$listInputs = $joinpoint->getReturnedValue();
		$listInputs[] = array("name" => "email", "type" => "text", "isMultiple" => "false");
		$joinpoint->setReturnedValue($listInputs);
	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	public static function afterUserAuthenticationControlRegisterFormParameters(AopJoinpoint $joinpoint){
		if($joinpoint->getReturnedValue()){
			$joinpoint->setReturnedValue(preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $joinpoint->getArguments()[0]["email"]));
		}
	}

	//--------------------------------------------------- afterUserAuthenticationBuildUserForRegister
	public static function afterUserAuthenticationBuildUserForRegister(AopJoinpoint $joinpoint){
		$user = $joinpoint->getReturnedValue();
		$user->email = $joinpoint->getArguments()[0]["email"];
		$joinpoint->setReturnedValue($user);
	}
}
