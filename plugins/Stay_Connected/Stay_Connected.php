<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Component\Input;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Wiki\Application;
use ITRocks\Wiki\Plugins\Stay_Connected\Connection_Cookie;

/**
 * Stay connected
 */
class Stay_Connected implements Registerable
{

	//------------------------------------------------------------ afterUserAuthenticateControllerRun
	/**
	 * @param $form array
	 */
	public static function afterUserAuthenticateControllerRun(array $form)
	{
		$checkbox_result = !empty($form['stay_connected']);
		if ($checkbox_result) {
			/** @var $user User */
			$user = User::current();
			if (isset($user)) {
				$user_name       = $user->login;
				$user_name_title = self::generateNameCookie('user_name');
				$content_title   = self::generateNameCookie($user_name);
				$random_var      = self::generateRandomVar();
				$content_key     = self::generateContentCookie($user_name, $random_var);
				self::registerHashInDao($user, $content_key, $random_var);
				$path = self::getPath();
				$expire = time() + 60 * 60 * 24 * 30;
				setcookie($user_name_title, $user_name, $expire, $path);
				setcookie($content_title, $content_key, $expire, $path);
			}
		}
	}

	//------------------------------------------------------------- afterUserAuthenticationDisconnect
	/**
	 * @param $user User
	 */
	public static function afterUserAuthenticationDisconnect(User $user)
	{
		$user_name_title = self::generateNameCookie('user_name');
		$content_title   = self::generateNameCookie($user->login);
		if(isset($_COOKIE[$content_title])){
			$hash = $_COOKIE[$content_title];
			$connection_cookie       = Search_Object::create(Connection_Cookie::class);
			$connection_cookie->user = $user;
			$connection_cookie->hash = $hash;
			$connection_cookie       = Dao::searchOne($connection_cookie);
			Dao::delete($connection_cookie);
			$path   = self::getPath();
			$expire = time() - 3600;
			setcookie($user_name_title, false, $expire, $path);
			setcookie($content_title,   false, $expire, $path);
			unset($_COOKIE[$user_name_title]);
			unset($_COOKIE[$content_title]);
		}
	}

	//--------------------------------------------------------- afterUserAuthenticationGetLoginInputs
	/**
	 * @param $result Input[]
	 */
	public static function afterUserAuthenticationGetLoginInputs(array &$result)
	{
		$result[] = new Input('stay_connected', 'stay connected', 'checkbox');
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public static function beforeMainControllerRun()
	{
		/** @var $user User */
		$user = Search_Object::create(User::class);
		if (!Session::current() || !Session::current()->get(get_class($user))) {
			$user_name_title = self::generateNameCookie('user_name');
			$user_name = null;
			if (isset($_COOKIE[$user_name_title])) {
				$user_name = $_COOKIE[$user_name_title];
			}
			if ($user_name) {
				$content_key = $_COOKIE[self::generateNameCookie($user_name)];
				if ($content_key) {
					$user->login = $user_name;
					$user        = Dao::searchOne($user);
					if (isset($user)) {
						/** @var $connection_cookie Connection_Cookie */
						$connection_cookie       = Search_Object::create(Connection_Cookie::class);
						$connection_cookie->user = $user;
						/** @var $list_connection_cookie Connection_Cookie[] */
						$list_connection_cookie = Dao::search($connection_cookie);
						$connection_cookie = self::compareHashPossible($list_connection_cookie, $content_key);
						if ($connection_cookie) {
							// Test if the context has not change
							$content_check
								= self::generateContentCookie($user->login, $connection_cookie->random_var);
							if ($content_key == $content_check) {
								Authentication::authenticate($user);
							}
						}
					}
				}
			}
		}
	}

	//--------------------------------------------------------------------------- compareHashPossible
	/**
	 * @param $list_connection_cookie Connection_Cookie[]
	 * @param $content_key            string
	 * @return Connection_Cookie|null
	 */
	private static function compareHashPossible(array $list_connection_cookie, $content_key)
	{
		foreach ($list_connection_cookie as $connection_cookie) {
			if ($connection_cookie->hash == $content_key) {
				return $connection_cookie;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------- generateContentCookie
	/**
	 * Generate a content cookie width a key depending of local context
	 *
	 * @param $user_name  string The login of the user
	 * @param $random_var string A random value
	 * @return string The content of the cookie
	 */
	public static function generateContentCookie($user_name, $random_var)
	{
		$content = ':';
		$list_server_values = [
			'HTTP_HOST',
			'HTTP_USER_AGENT',
			'SERVER_SIGNATURE',
			'SERVER_NAME',
			'SERVER_ADDR'
		];
		$var_server = '';
		foreach ($list_server_values as $key) {
			$var_server .= $_SERVER[$key];
		}
		$key = md5($user_name . $var_server . $random_var);
		$content .= $key;
		return $content;
	}

	//---------------------------------------------------------------------------- generateNameCookie
	/**
	 * Give a name of a cookie attached to this project
	 *
	 * @param $name string Name of the cookie
	 * @return string
	 */
	public static function generateNameCookie($name)
	{
		return Application::current()->name . '_' . $name;
	}

	//----------------------------------------------------------------------------- generateRandomVar
	/**
	 * Return a random var
	 *
	 * @return string
	 */
	private static function generateRandomVar()
	{
		return uniqid(rand(), true);
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * @return string
	 */
	private static function getPath()
	{
		$script_name = $_SERVER['SCRIPT_NAME'];
		$script_name = str_replace('.php', '', $script_name);
		return $script_name . SL;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			[Controller::class, 'runController'],
			[__CLASS__, 'beforeMainControllerRun']
		);
		$aop->afterMethod(
			[Authenticate\Controller::class, 'run'],
			[__CLASS__, 'afterUserAuthenticateControllerRun']
		);
		$aop->afterMethod(
			[Authentication::class, 'getLoginInputs'],
			[__CLASS__, 'afterUserAuthenticationGetLoginInputs']
		);
		$aop->afterMethod(
			[Authentication::class, 'disconnect'],
			[__CLASS__, 'afterUserAuthenticationDisconnect']
		);
	}

	//----------------------------------------------------------------------------- registerHashInDao
	/**
	 * Add a generate hash in databases with Dao object
	 *
	 * @param $user        User
	 * @param $content_key string
	 * @param $random_var  string
	 */
	private static function registerHashInDao($user, $content_key, $random_var)
	{
		/** @var $connection_cookie Connection_Cookie */
		$connection_cookie             = Search_Object::create(Connection_Cookie::class);
		$connection_cookie->user       = $user;
		$connection_cookie->hash       = $content_key;
		$connection_cookie->random_var = $random_var;
		Dao::write($connection_cookie);
	}

}
