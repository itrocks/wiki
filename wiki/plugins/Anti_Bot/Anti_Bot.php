<?php
namespace SAF\Wiki;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;

class Anti_Bot implements Plugin
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
	}
	//------------------------------------------------------ afterUserAuthenticationGetRegisterInputs
	public static function afterUserAuthenticationGetRegisterInputs(AopJoinpoint $joinpoint){

	}

	//------------------------------------------ afterUserAuthenticationControlRegisterFormParameters
	public static function afterUserAuthenticationControlRegisterFormParameters(AopJoinpoint $joinpoint){

	}
}
