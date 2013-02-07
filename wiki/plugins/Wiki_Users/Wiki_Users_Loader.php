<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Plugin;
use SAF\Framework\AOP;

abstract class Wiki_Users_Loader implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			"SAF\\Framework\\User_Authenticate_Controller->authenticate()",
			array(__CLASS__, "onUserAuthenticate")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authenticate_Controller->disconnect()",
			array(__CLASS__, "onUserDisconnect")
		);
	}

	//---------------------------------------------------------------------------- onUserAuthenticate
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function onUserAuthenticate(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments)) {

			Session::current()->set(Wiki_User::current(self::loadUser($arguments[0])));
		}
	}

	//------------------------------------------------------------------------------ onUserDisconnect
	public static function onUserDisconnect()
	{

	}
}
