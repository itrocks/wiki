<?php

namespace SAF\Wiki;
use SAF\Framework\User;

class Email_Confirmation
{
	/**
	 * @var string
	 */
	public $link;

	/**
	 * @getter Aop::getObject
	 * @var User
	 */
	public $user;

}
