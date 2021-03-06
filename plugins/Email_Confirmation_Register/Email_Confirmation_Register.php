<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\View;
use ITRocks\Wiki\Application;
use ITRocks\Wiki\Plugins\Email_Confirmation_Register\Email_Confirmation;

/**
 * Use the mail() function of PHP
 * By default, the mail function need the sendmail package.
 * Please install this package or redefine the mail function of php to use this plugin.
 */
class Email_Confirmation_Register implements Registerable
{

	//----------------------------------------------------------------------------- $mail_domain_from
	/**
	 * @var string
	 */
	private $mail_domain_from = '@noreply.fr';

	//--------------------------------------------------------------- afterUserAuthenticationRegister
	/**
	 * Generate a mail content, an activation key, save the key in databases, and send the email
	 *
	 * @param $form array
	 */
	public function afterUserAuthenticationRegister(array $form)
	{
		/** @var $user User */
		$user        = Search_Object::create(User::class);
		$user->login = $form['login'];
		$user        = Dao::searchOne($user);
		if ($user) {
			$key  = $this->generateKey();
			$link = $this->generateActivationLink($key);
			/** @var $email_confirm Email_Confirmation */
			$email_confirm = Search_Object::create(Email_Confirmation::class);
			$email_confirm->user = $user;
			$email_confirm->link = $key;
			Dao::write($email_confirm);
			$application_name = Application::current()->name;
			$email_from       = $this->getEmailFrom($application_name);
			$headers          = $this->getHeaders($application_name, $email_from);
			$parameters       = $this->getViewParameters(
				$user->login, $form['password'], $application_name, $link
			);
			$files        = [];
			$class_name   = Email_Confirmation::class;
			$feature_name = 'content';
			$subject      = '[' . $application_name . '] ' . 'Confirm your subscribe';
			ini_set('sendmail_from', $email_from);
			mail(
				$user->email, $subject,
				View::run($parameters, $form, $files, $class_name, $feature_name), $headers
			);
		}
	}

	//------------------------------------------------------------------ afterUserAuthenticationLogin
	/**
	 * Disable login function if the mail has not be validate by the link
	 *
	 * @param $result User
	 */
	public function afterUserAuthenticationLogin(&$result)
	{
		$email_confirm = Search_Object::create(Email_Confirmation::class);
		$email_confirm->user = $result;
		$is_not_validate = Dao::search($email_confirm);
		if ($is_not_validate) {
			$result = null;
		}
	}

	//------------------------------------------------------------------------------------ getHeaders
	/**
	 * Return header generated for a mail.
	 * @param $application_name string The name of the application.
	 * @param $email_from       string The email address from.
	 * @return string Return the header in string format.
	 */
	private function getHeaders($application_name, $email_from)
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: ' . $application_name . ' <' . $email_from . '>' . "\r\n";
		return $headers;
	}

	//------------------------------------------------------------------------ generateActivationLink
	/**
	 * Build an activation link with a key put in parameters
	 *
	 * @param $key string The key to use in activation link
	 * @return string The activation link
	 */
	private function generateActivationLink($key)
	{
		return $_SERVER['HTTP_HOST']
			. explode('User', $_SERVER['REQUEST_URI'])[0]
			. 'User/confirmEmail?action=' . $key;
	}

	//----------------------------------------------------------------------------------- generateKey
	/**
	 * Generate an unique key
	 *
	 * @return string A key
	 */
	private function generateKey()
	{
		return md5(uniqid(rand(), true));
	}

	//---------------------------------------------------------------------------------- getEmailFrom
	/**
	 * Generate an email address type to send a mail
	 *
	 * @param $application_name string The name of the application
	 * @return string An email address type
	 */
	private function getEmailFrom($application_name)
	{
		$email_from = $application_name;
		$email_from .= $this->mail_domain_from;
		return $email_from;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * Generate the view parameters
	 *
	 * @param $login            string The login of the user
	 * @param $password         string The password of the user
	 * @param $application_name string The name of the application
	 * @param $link             string The activation link.
	 * @return array A list of parameters.
	 */
	private function getViewParameters($login, $password, $application_name, $link)
	{
		$parameters = [];
		$parameters['login']     = $login;
		$parameters['password']  = $password;
		$parameters['site_name'] = $application_name;
		$parameters['link']      = $link;
		return $parameters;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[Authentication::class, 'register'],
			[$this, 'afterUserAuthenticationRegister']
		);
		$aop->afterMethod(
			[Authentication::class, 'login'],
			[$this, 'afterUserAuthenticationLogin']
		);
	}

}
