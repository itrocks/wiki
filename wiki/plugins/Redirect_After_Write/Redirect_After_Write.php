<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Namespaces;

class Redirect_After_Write implements Plugin
{

	//---------------------------------------------------------------- afterDefaultWriteControllerRun
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterDefaultWriteControllerRun(AopJoinpoint $joinpoint)
	{

	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			"SAF\\Framework\\Default_Write_Controller->run()",
			array(__CLASS__, "afterDefaultWriteControllerRun")
		);
	}

}
