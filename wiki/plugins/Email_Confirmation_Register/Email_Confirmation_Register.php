<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;
use SAF\Framework\Search_Object;
use SAF\Framework\View;

/**
 * Use the mail() function of PHP
 * By default, the mail function need the sendmail package.
 * Please install this package or redefine the mail function of php to use this plugin.
 */
class Email_Confirmation_Register implements Plugin
{

	//----------------------------------------------------------------------------- $mail_domain_from
	/**
	 * @var string
	 */
	private static $mail_domain_from = '@noreply.fr';

	//--------------------------------------------------------------- afterUserAuthenticationRegister
	/**
	 * Generate a mail content, an activation key, save the key in databases, and send the email.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationRegister(AopJoinpoint $joinpoint)
	{
		$user = Search_Object::create('SAF\Framework\User');
		$form = $joinpoint->getArguments()[0];
		$user->login = $form["login"];
		$user = Dao::searchOne($user);
		if ($user) {
			$key = self::generateKey();
			$link = self::generateActivationLink($key);
			$email_confirm = Search_Object::create('SAF\Wiki\Email_Confirmation');
			$email_confirm->user = $user;
			$email_confirm->link = $key;
			Dao::write($email_confirm);
			$application_name = end($GLOBALS["CONFIG"])["app"];
			$email_from = self::getEmailFrom($application_name);
			$headers = self::getHeaders($application_name, $email_from);
			$parameters = self::getViewParameters(
				$user->login, $form["password"], $application_name, $link
			);
			$files = array();
			$class_name = 'SAF\Wiki\Email_Confirmation';
			$feature_name = "content";
			$subject = "[" . $application_name . "] " . "Confirm your subscribe";
			ini_set("sendmail_from", $email_from);
			mail(
				$user->email, $subject,
				View::run($parameters, $form, $files, $class_name, $feature_name), $headers
			);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\User_Authentication->register()',
			array(__CLASS__, "afterUserAuthenticationRegister")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->login()',
			array(__CLASS__, "afterUserAuthenticationLogin")
		);
	}

	//------------------------------------------------------------------ afterUserAuthenticationLogin
	/**
	 * Disable login function if the mail has not be validate by the link.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationLogin(AopJoinpoint $joinpoint)
	{
		$user = $joinpoint->getReturnedValue();
		$email_confirm = Search_Object::create('SAF\Wiki\Email_Confirmation');
		$email_confirm->user = $user;
		$is_not_validate = Dao::search($email_confirm);
		if ($is_not_validate) {
			$joinpoint->setReturnedValue(null);
		}
	}

	//------------------------------------------------------------------------------------ getHeaders
	/**
	 * Return header generated for a mail.
	 * @param $application_name string The name of the application.
	 * @param $email_from       string The email address from.
	 * @return string Return the header in string format.
	 */
	private static function getHeaders($application_name, $email_from)
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: ' . $application_name . ' <' . $email_from . '>' . "\r\n";
		return $headers;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * Generate the view parameters
	 * @param $login            string The login of the user
	 * @param $password         string The password of the user
	 * @param $application_name string The name of the application
	 * @param $link             string The activation link.
	 * @return array A list of parameters.
	 */
	private static function getViewParameters($login, $password, $application_name, $link)
	{
		$parameters = array();
		$parameters["login"]      = $login;
		$parameters["password"]   = $password;
		$parameters["site_name"]  = $application_name;
		$parameters["link"]       = $link;
		return $parameters;
	}

	//------------------------------------------------------------------------- generateActivationKey
	/**
	 * Generate an unique key.
	 * @return string A key.
	 */
	private static function generateKey()
	{
		$key = md5(uniqid(rand(), true));
		return $key;
	}

	//------------------------------------------------------------------------ generateActivationLink
	/**
	 * Build an activation link with a key put in parameters.
	 * @param $key string The key to use in activation link.
	 * @return string The activation link.
	 */
	private static function generateActivationLink($key)
	{
		$link = $_SERVER["HTTP_HOST"]
			. explode("User", $_SERVER["REQUEST_URI"])[0]
			. "User/confirmEmail?action=" . $key;
		return $link;
	}

	//---------------------------------------------------------------------------------- getEmailFrom
	/**
	 * Generate an email address type to send a mail.
	 * @param $application_name string The name of the application
	 * @return string An email address type.
	 */
	private static function getEmailFrom($application_name)
	{
		$email_from = $application_name;
		$email_from .= self::$mail_domain_from;
		return $email_from;
	}
}
