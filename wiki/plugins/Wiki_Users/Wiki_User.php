<?php
namespace SAF\Wiki;
use SAF\Framework\User;

class Wiki_User extends User
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var string
	 */
	public $email;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $user Wiki_User
	 * @return Wiki_User
	 */
	public static function current($user = null)
	{
		return parent::current($user);
	}

}
