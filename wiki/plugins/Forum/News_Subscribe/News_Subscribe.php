<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Namespaces;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Search_Object;
use SAF\Framework\User;
use SAF\Framework\View;

class News_Subscribe implements Plugin
{

	//----------------------------------------------------------------------------- $mail_domain_from
	/**
	 * @var string
	 */
	private static $mail_domain_from = '@noreply.fr';

	//-------------------------------------------------------------------------- changeToOutputReturn
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function addSubscribeButton(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$url = $joinpoint->getArguments()[1];
		$buttons = $joinpoint->getReturnedValue();
		if(self::isSubscribe($object)){
			$buttons[] = array(
				"Unsubscribe",
				Forum_Url_Utils::getUrl('', $url, array("mode" => "unsubscribe")),
				"unsubscribe"
			);
		}
		else {
			$buttons[] = array(
				"Subscribe",
				Forum_Url_Utils::getUrl('', $url, array("mode" => "subscribe")),
				"subscribe"
			);
		}
		$joinpoint->setReturnedValue($buttons);
	}

	public static function isSubscribe($object)
	{
		$search = new Subscribe();
		$search->user = User::current();
		$search->from = $object;
		$search->class_name = get_class($object);
		$search = Dao::search($search);
		return count($search) > 0;
	}

	//------------------------------------------------------------------------------ preventSubscribe
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function preventSubscribe(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		if(is_object($object) && !Forum_Utils::isNotFound($object)){
			$class_name = get_class($object);
			$parent = Forum_utils::getParentObject($object);
			$search = new Subscribe();
			$search->from = $parent;
			$search->class_name = get_class($parent);
			$results = Dao::search($search);
			if(count($results)){
				$path = Forum_Path_Utils::getPath();
				$short_class_name = Namespaces::shortClassName($class_name);
				$application_name = end($GLOBALS["CONFIG"])["app"];
				$email_from = $application_name . self::$mail_domain_from;
				$base_url = Forum_Url_Utils::getParentUrl(Forum_Url_Utils::getBaseUrl($path));
				$parameters["link"] = Forum_Url_Utils::getUrl($object, $base_url);
				$parameters["link_stop"] = Forum_Url_Utils::getUrl("", $base_url, array("mode" => "unsubscribe"));
				$parameters["type"] = $short_class_name;
				$parameters["object"] = $object;
				$subject = "[" . $application_name . "] " . "A new " . $short_class_name . " has be send.";
				$view = View::run($parameters, array(), array(), get_class(), "email_send");
				$headers = self::getHeaders($application_name, $email_from);
				ini_set("sendmail_from", $email_from);
				foreach($results as $result){
					$result = Forum_Utils::assignAttributeObjectInElement
						($result, "user", get_class(Search_Object::create("SAF\\Framework\\User")));
					/*mail(
						$result->user->email, $subject,
						$view, $headers
					);*/
				}
			}
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

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Wiki\Forum_Buttons_Utils->getBottomButtonsModeOutput()',
			array(__CLASS__, "addSubscribeButton")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Controller_Utils->writeCompleteObjectDao()',
			array(__CLASS__, "preventSubscribe")
		);
	}
}
