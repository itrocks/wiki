<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Plugin;
use SAF\Framework\Aop;
use SAF\Framework\Controller_Parameters;
use \AopJoinpoint;
use SAF\Framework\User;

/**
 * Class ReadOrNotRead
 * Without acls
 * @package SAF\Wiki
 */
class ReadOrNotRead implements Plugin{

	public static function register()
	{
		Aop::add("after",
			'SAF\Wiki\Forum_Utils->getTitle()',
			array(__CLASS__, "afterForumUtilsGetTitle")
		);
		Aop::add("after",
			'SAF\Wiki\Topic_Controller->output()',
			array(__CLASS__, "afterTopicControllerOutput")
		);
	}

	//----------------------------------------------------------------------- afterForumUtilsGetTitle
	/**
	 * Control if read or not read
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterForumUtilsGetTitle(AopJoinpoint $joinpoint){
		$object = $joinpoint->getArguments()[0];
		if(isset($object)){
			$class = get_class($object);
			if($class == Forum_Utils::$namespace . "Topic"){
				$user = User::current();
				$search = new Read();
				$search->user = $user;
				$search->topic = $object;
				$search = Dao::searchOne($search);
				if(isset($search)){
					$object = Forum_Utils::assignLastPost($object);
					if(Forum_Utils::isEqualAttributeAndObject($search, "last_post", $object->last_post)){
						return;
					}
				}
				$joinpoint->setReturnedValue("<strong>" . $joinpoint->getReturnedValue() . "</strong>");
			}
			else if($class == Forum_Utils::$namespace . "Forum"){
				$user = User::current();
				$object = Forum_Utils::assignLastPost($object);
				$search = new Read();
				$search->user = $user;
				$search->last_post = $object->last_post;
				$search = Dao::searchOne($search);
				if(isset($search)){
					return;
				}
				$joinpoint->setReturnedValue("<strong>" . $joinpoint->getReturnedValue() . "</strong>");
			}
		}
	}

	//-------------------------------------------------------------------- afterTopicControllerOutput
	/**
	 * Write that last read.
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterTopicControllerOutput(AopJoinpoint $joinpoint){
		/** @var $parameters Controller_Parameters */
		$parameters = $joinpoint->getArguments()[0];
		$object = $parameters->getObject("Topic");
		if(isset($object)){
			$user = User::current();
			$last_post = Forum_Utils::assignLastPost($object)->last_post;
			$read = new Read();
			$read->topic = $object;
			$read->user = $user;
			$search = Dao::searchOne($read);
			if(isset($search)){
				if(Forum_Utils::isEqualAttributeAndObject($search, "last_post", $last_post)){
					return;
				}
				$read = $search;
			}
			$read->last_post = $last_post;
			Dao::write($read);
		}
	}
}
