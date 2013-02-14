<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Input;
use SAF\Framework\Session;
use SAF\Framework\Search_Object;
use \SAF\Framework\User_Authentication;

class Stay_Connected_Register implements Plugin
{
	//------------------------------------------------------------ afterUserAuthenticateControllerRun
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticateControllerRun(AopJoinpoint $joinpoint)
	{
		$checkbox_result = $joinpoint->getArguments()[1]["stay_connected"];
		if($checkbox_result){
			$user = Search_Object::newInstance("User");
			$user = Session::current()->get(get_class($user));
			if($user){
				$user_name = $user->login;
				$user_name_title = self::generateNameCookie("user_name");
				$content_title = self::generateNameCookie($user_name);
				$random_var = self::generateRandomVar();
				$content_key = self::generateContentCookie($user_name, $random_var);
				self::registerHashInDao($user, $content_key, $random_var);
				$path = self::getPath();
				setcookie($user_name_title, $user_name, time()+60*60*24*30, $path);
				setcookie($content_title, $content_key, time()+60*60*24*30, $path);
			}
		}
	}

	//------------------------------------------------------------- afterUserAuthenticationDisconnect
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterUserAuthenticationDisconnect(AopJoinpoint $joinpoint){
		$user = $joinpoint->getArguments()[0];$user_name = $user->login;
		$user_name_title = self::generateNameCookie("user_name");
		$content_title = self::generateNameCookie($user->login);
		$hash = $_COOKIE[$content_title];
		$connection_cookie = Search_Object::newInstance("Connection_Cookie");
		$connection_cookie->user = $user;
		$connection_cookie->hash = $hash;
		$connection_cookie = Dao::searchOne($connection_cookie);
		Dao::delete($connection_cookie);
		$path = self::getPath();
		setcookie($user_name_title, false, time() - 3600, $path);
		setcookie($content_title, false, time() - 3600, $path);
		unset($_COOKIE[$user_name_title]);
		unset($_COOKIE[$content_title]);
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
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeMainControllerRun(AopJoinpoint $joinpoint)
	{
		$user = Search_Object::newInstance("User");
		if(!Session::current() || !Session::current()->get(get_class($user))){
			$user_name_title = self::generateNameCookie("user_name");
			$user_name = null;
			if(isset($_COOKIE[$user_name_title]))
				$user_name = $_COOKIE[$user_name_title];
			if($user_name){
				$content_key = $_COOKIE[self::generateNameCookie($user_name)];
				if($content_key){
					$user->login = $user_name;
					$user = Dao::searchOne($user);
					if($user){
						$connection_cookie = Search_Object::newInstance("Connection_Cookie");
						$connection_cookie->user = $user;
						$list_connection_cookie = Dao::search($connection_cookie);
						$connection_cookie = self::compareHashPossible($list_connection_cookie, $content_key);
						if($connection_cookie){
							//Test if the context has not change
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
	private static function compareHashPossible(/* array */ $list_connection_cookie, $content_key){
		foreach($list_connection_cookie as $connection_cookie){
			if($connection_cookie->hash == $content_key)
				return $connection_cookie;
		}
		return null;
	}

	//------------------------------------------------------------------------- generateContentCookie
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
		foreach($list_server_values as $key){
			$var_server .= $_SERVER[$key];
		}
		$key = md5($user_name . $var_server . $random_var);
		$content .= $key;
		return $content;
	}

	//---------------------------------------------------------------------------- generateNameCookie
	public static function generateNameCookie($name)
	{
		$application_name = end($GLOBALS["CONFIG"])["app"];
		return $application_name . "_" . $name;
	}

	//----------------------------------------------------------------------------- generateRandomVar
	private static function generateRandomVar(){
		return uniqid(rand(), true);
	}

	//--------------------------------------------------------------------------------------- getPath
	private static function getPath(){
		$script_name = $_SERVER["SCRIPT_NAME"];
		$script_name = str_replace(".php", "", $script_name);
		return $script_name . "/";
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			"SAF\\Framework\\Main_Controller->runController()",
			array(__CLASS__, "beforeMainControllerRun")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authenticate_Controller->run()",
			array(__CLASS__, "afterUserAuthenticateControllerRun")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->getLoginInputs()",
			array(__CLASS__, "afterUserAuthenticationGetLoginInputs")
		);
		Aop::add("after",
			"SAF\\Framework\\User_Authentication->disconnect()",
			array(__CLASS__, "afterUserAuthenticationDisconnect")
		);
	}

	//----------------------------------------------------------------------------- registerHashInDao
	private static function registerHashInDao($user, $content_key, $random_var){
		$connection_cookie = Search_Object::newInstance("Connection_Cookie");
		$connection_cookie->user = $user;
		$connection_cookie->hash = $content_key;
		$connection_cookie->random_var = $random_var;
		Dao::write($connection_cookie);
	}

}
