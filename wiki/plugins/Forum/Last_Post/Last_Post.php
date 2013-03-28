<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;

class Last_Post implements Plugin
{

	//--------------------------------------------------------- aroundForumControllerUtilsWriteObject
	public static function aroundForumControllerUtilsWriteObject(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$object = $arguments[0];
		$is_new = false;
		$class = get_class($object);
		if(
			is_object($object)
			&& ($class == Forum_Utils::$namespace . "Post" || $class == Forum_Utils::$namespace . "Topic")
			&& Forum_Utils::isNotFound($object)
		){
			$is_new = true;
		}
		$joinpoint->process();
		if($is_new && !is_array($joinpoint->getReturnedValue())){
			self::updateLastPost($object);
		}
	}

	//------------------------------------------------------------------- aroundForumUtilsGetLastPost
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumUtilsGetLastPost(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$return = null;
		$object =	Forum_Utils::assignAttributeObjectInElement(
			$object, "last_post", Forum_Utils::$namespace . "Post", false
		);
		if(isset($object->last_post))
			$return = $object->last_post;
		$joinpoint->setReturnedValue($return);
	}

	//-------------------------------------------------------- aroundForumControllerUtilsDeleteObject
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function aroundForumControllerUtilsDeleteObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$needUpdate = array();
		if(get_class($object) == Forum_Utils::$namespace . "Post"){
			$topic = Forum_Utils::getParentObject($object);
			$needUpdate[] = $topic;
			$needUpdate[] = Forum_Utils::getParentObject($topic);
		} else if (get_class($object) == Forum_Utils::$namespace . "Topic"){
			$needUpdate[] = Forum_Utils::getParentObject($object);
		}
		$joinpoint->process();
		if(count($needUpdate) > 0){
			$attribute_name = "last_post";
			foreach($needUpdate as $element){
				if(!Forum_Utils::isNotFound($element)){
					$element = Dao::read(Dao::getObjectIdentifier($element), get_class($element));
					if(property_exists($element, $attribute_name)){
						$list_posts = Forum_Utils::getNextElements($element);
						$last_post = null;
						if(get_class($element) == Forum_Utils::$namespace . "Forum"){
							$last_post = reset($list_posts)->$attribute_name;
						}
						else if($last_post == false && get_class($element) == Forum_Utils::$namespace . "Topic"){
							$last_post = end($list_posts);
							if($last_post == false)
								$last_post = Forum_Utils::assignTopicFirstPost($element)->first_post;
						}
						if(!Forum_Utils::isNotFound($last_post)){
							Forum_Utils::setObjectAttribute($element, $attribute_name, $last_post);
							Dao::write($element);
						}
					}
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- recalculate
	/**
	 * Recalculate all "last post" champs.
	 * Use this if you think the last_post attributes are not right.
	 * Warning : much access of Dao.
	 */
	public static function recalculate()
	{
		foreach(Forum_Utils::getCategories() as $category){
			foreach(Forum_Utils::getForums($category) as $forum){
				$posts_forum = array();
				foreach(Forum_Utils::getTopics($forum) as $topic){
					$posts = Forum_Utils::getPosts($topic);
					if($posts)
						$last_post = end($posts);
					else
						$last_post = Forum_Utils::assignTopicFirstPost($topic)->first_post;
					Forum_Utils::setObjectAttribute($topic, "last_post", $last_post);
					Dao::write($topic);
					$posts_forum[] = $last_post;
				}
				$last_post = new Post();
				foreach($posts_forum as $post){
					if(isset($post) && $post->date_post > $last_post->date_post)
						$last_post = $post;
				}
				if(!Forum_Utils::isNotFound($last_post)){
					Forum_Utils::setObjectAttribute($forum, "last_post", $last_post);
					Dao::write($forum);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			'SAF\Wiki\Forum_Controller_Utils->writeObject()',
			array(__CLASS__, "aroundForumControllerUtilsWriteObject")
		);
		Aop::add("around",
			'SAF\Wiki\Forum_Controller_Utils->deleteObject()',
			array(__CLASS__, "aroundForumControllerUtilsDeleteObject")
		);
		Aop::add("around",
			'SAF\Wiki\Forum_Utils->getLastPost()',
			array(__CLASS__, "aroundForumUtilsGetLastPost")
		);
	}

	//-------------------------------------------------------------------------------- updateLastPost
	public static function updateLastPost($object)
	{
		$attribute_last_post = "last_post";
		if($object){
			// special case: first post of a topic
			$class_topic = Forum_Utils::$namespace . "Topic";
			if(get_class($object) == $class_topic){
				$topic = Forum_Utils::assignTopicFirstPost($object);
				$object = $topic->first_post;
			}
			$element = $object;
			while(($element = Forum_Utils::getParentObjectSome($element)) != null){
				if(property_exists($element, $attribute_last_post) && !Forum_Utils::isNotFound($element)){
					$element = Dao::read(Dao::getObjectIdentifier($element), get_class($element));
					Forum_Utils::setObjectAttribute($element, $attribute_last_post, $object);
					Dao::write($element);
				}
			}
		}
	}
}
