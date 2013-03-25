<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Button;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\User;
use SAF\Framework\View;

class Pages_number implements Plugin
{

	//-------------------------------------------------------------- aroundControlWithObjectParameter
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundControlWithObjectParameter(AopJoinpoint $joinpoint)
	{

	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			'SAF\Wiki\\' . $element[0] . '->' . $element[1] . '()',
			array(__CLASS__, "aroundControlWithObjectParameter")
		);
	}
}
