<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Dao;
use SAF\Framework\Input;
use SAF\Framework\Plugin;
use SAF\Framework\Search_Object;
use SAF\Framework\Session;
use SAF\Framework\User_Authentication;

class Stay_Connected implements Plugin
{

	//------------------------------------------------------------ afterUserAuthenticateControllerRun
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticateControllerRun(AopJoinpoint $joinpoint)
	{
		$checkbox_result = !empty($joinpoint->getArguments()[1]["stay_connected"]);
		if ($checkbox_result) {
			/** @var $user \SAF\Framework\User */
			$user = Search_Object::newInstance('SAF\Framework\User');
			$user = Session::current()->get(get_class($user));
			if (isset($user)) {
				$user_name = $user->login;
				$user_name_title = self::generateNameCookie("user_name");
				$content_title = self::generateNameCookie($user_name);
				$random_var = self::generateRandomVar();
				$content_key = self::generateContentCookie($user_name, $random_var);
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
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationDisconnect(AopJoinpoint $joinpoint)
	{
		$user = $joinpoint->getArguments()[0];
		$user_name_title = self::generateNameCookie("user_name");
		$content_title = self::generateNameCookie($user->login);
		if(isset($_COOKIE[$content_title])){
			$hash = $_COOKIE[$content_title];
			$connection_cookie = Search_Object::newInstance('SAF\Wiki\Connection_Cookie');
			$connection_cookie->user = $user;
			$connection_cookie->hash = $hash;
			$connection_cookie = Dao::searchOne($connection_cookie);
			Dao::delete($connection_cookie);
			$path = self::getPath();
			$expire = time() - 3600;
			setcookie($user_name_title, false, $expire, $path);
			setcookie($content_title, false, $expire, $path);
			unset($_COOKIE[$user_name_title]);
			unset($_COOKIE[$content_title]);
		}
	}

	//--------------------------------------------------------- afterUserAuthenticationGetLoginInputs
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationGetLoginInputs(AopJoinpoint $joinpoint)
	{
		$list_inputs = $joinpoint->getReturnedValue();
		$list_inputs[] = new Input("stay_connected", "stay connected", "checkbox");
		$joinpoint->setReturnedValue($list_inputs);
	}

	//----------------------------------------------------------------------- beforeMainControllerRun
	public static function beforeMainControllerRun()
	{
		/** @var $user \SAF\Framework\User */
		$user = Search_Object::newInstance('SAF\Framework\User');
		if (!Session::current() || !Session::current()->get(get_class($user))) {
			$user_name_title = self::generateNameCookie("user_name");
			$user_name = null;
			if (isset($_COOKIE[$user_name_title])) {
				$user_name = $_COOKIE[$user_name_title];
			}
			if ($user_name) {
				$content_key = $_COOKIE[self::generateNameCookie($user_name)];
				if ($content_key) {
					$user->login = $user_name;
					$user = Dao::searchOne($user);
					if (isset($user)) {
						/** @var $connection_cookie \SAF\Wiki\Connection_Cookie */
						$connection_cookie = Search_Object::newInstance('SAF\Wiki\Connection_Cookie');
						$connection_cookie->user = $user;
						/** @var $list_connection_cookie Connection_Cookie[] */
						$list_connection_cookie = Dao::search($connection_cookie);
						$connection_cookie = self::compareHashPossible($list_connection_cookie, $content_key);
						if ($connection_cookie) {
							// Test if the context has not change
							$content_check =
								self::generateContentCookie($user->login, $connection_cookie->random_var);
							if($content_key == $content_check){
								User_Authentication::authenticate($user);
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
	 * @param $content_key string
	 * @return Connection_Cookie|null
	 */
	private static function compareHashPossible($list_connection_cookie, $content_key)
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
	 * @param $user_name  string
	 * @param $random_var string
	 * @return string
	 */
	public static function generateContentCookie($user_name, $random_var)
	{
		$content = ":";
		$list_server_values = array(
			"HTTP_HOST",
			"HTTP_USER_AGENT",
			"SERVER_SIGNATURE",
			"SERVER_NAME",
			"SERVER_ADDR"
		);
		$var_server = "";
		foreach ($list_server_values as $key) {
			$var_server .= $_SERVER[$key];
		}
		$key = md5($user_name . $var_server . $random_var);
		$content .= $key;
		return $content;
	}

	//---------------------------------------------------------------------------- generateNameCookie
	/**
	 * @param $name string
	 * @return string
	 */
	public static function generateNameCookie($name)
	{
		$application_name = end($GLOBALS["CONFIG"])["app"];
		return $application_name . "_" . $name;
	}

	//----------------------------------------------------------------------------- generateRandomVar
	/**
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
		$script_name = $_SERVER["SCRIPT_NAME"];
		$script_name = str_replace(".php", "", $script_name);
		return $script_name . "/";
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			'SAF\Framework\Main_Controller->runController()',
			array(__CLASS__, "beforeMainControllerRun")
		);
		Aop::add("after",
			'SAF\Framework\User_Authenticate_Controller->run()',
			array(__CLASS__, "afterUserAuthenticateControllerRun")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->getLoginInputs()',
			array(__CLASS__, "afterUserAuthenticationGetLoginInputs")
		);
		Aop::add("after",
			'SAF\Framework\User_Authentication->disconnect()',
			array(__CLASS__, "afterUserAuthenticationDisconnect")
		);
	}

	//----------------------------------------------------------------------------- registerHashInDao
	/**
	 * @param $user        \SAF\Framework\User
	 * @param $content_key string
	 * @param $random_var  string
	 */
	private static function registerHashInDao($user, $content_key, $random_var)
	{
		/** @var $connection_cookie Connection_Cookie */
		$connection_cookie = Search_Object::newInstance('SAF\Wiki\Connection_Cookie');
		$connection_cookie->user = $user;
		$connection_cookie->hash = $content_key;
		$connection_cookie->random_var = $random_var;
		Dao::write($connection_cookie);
	}

}
