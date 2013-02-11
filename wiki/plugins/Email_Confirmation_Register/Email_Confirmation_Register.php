<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Search_Object;

class Email_Confirmation_Register implements Plugin
{
	private static $MAIL_DOMAIN_FROM = '@noreply.fr';

	//--------------------------------------------------------------- afterUserAuthenticationRegister
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationRegister(AopJoinpoint $joinpoint)
	{
		$user = Search_Object::newInstance("User");
		$form = $joinpoint->getArguments()[0];
		$user->login = $form["login"];
		$user = Dao::searchOne($user);
		if($user){
			$key = self::generateActivationKey();
			$link = self::generateActivationLink($key);
			$email_confirm = Search_Object::newInstance("Email_Confirmation");
			$email_confirm->user = $user;
			$email_confirm->link = $key;
			Dao::write($email_confirm);
			$name_application = end($GLOBALS["CONFIG"])["app"];
			$email_from = self::getEmailFrom($name_application);
			$headers  = self::getHeaders($name_application, $email_from);
			$parameters = self::getViewParameters($user->login, $form["password"], $name_application, $link);
			$files = array();
			$class_name = "Email_Confirmation";
			$feature_name = "content";
			$subject = "[" . $name_application . "] " . "Confirm your subscribe";
			ini_set("sendmail_from", $email_from);
			mail($user->email, $subject,
				(new Html_Email_View())->run($parameters, $form, $files, $class_name, $feature_name), $headers);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->register()",
			array(__CLASS__, "afterUserAuthenticationRegister")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->login()",
			array(__CLASS__, "afterUserAuthenticationLogin")
		);
	}

	//------------------------------------------------------------------ afterUserAuthenticationLogin
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationLogin(AopJoinpoint $joinpoint)
	{
		$user = $joinpoint->getReturnedValue();
		$email_confirm = Search_Object::newInstance("Email_Confirmation");
		$email_confirm->user = $user;
		$is_not_validate = Dao::search($email_confirm);
		if($is_not_validate){
			$joinpoint->setReturnedValue(null);
		}
	}

	//------------------------------------------------------------------------------------ getHeaders
	private static function getHeaders($name_application, $email_from){
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: ' . $name_application . ' <' . $email_from . '>' . "\r\n";
		return $headers;
	}

	//----------------------------------------------------------------------------- getViewParameters
	private static function getViewParameters($login, $password, $name_application, $link){
		$parameters = array();
		$parameters["login"] = $login;
		$parameters["password"] = $password;
		$parameters["site_name"] = $name_application;
		$parameters["link"] = $link;
		return $parameters;
	}

	//------------------------------------------------------------------------- generateActivationKey
	private static function generateActivationKey(){
		$key = md5(uniqid(rand(), true));
		return $key;
	}

	//------------------------------------------------------------------------ generateActivationLink
	private static function generateActivationLink($activationKey){
		$link = $_SERVER["HTTP_HOST"]
			. explode("User", $_SERVER["REQUEST_URI"])[0]
			. "User/confirmEmail?action=" . $activationKey;
		return $link;
	}

	//---------------------------------------------------------------------------------- getEmailFrom
	private static function getEmailFrom($name_application){
		$email_from = $name_application;
		$email_from .= self::$MAIL_DOMAIN_FROM;
		return $email_from;
	}
}
