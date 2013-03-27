<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;

class Update_Nb_Values implements Plugin
{

	//-------------------------------------------------------- aroundForumControllerUtilsDeleteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumControllerUtilsDeleteObject(AopJoinpoint $joinpoint)
	{
		self::updateNbValues($joinpoint, -1);
	}

	//--------------------------------------------------------- aroundForumControllerUtilsWriteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumControllerUtilsWriteObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		if(Forum_Utils::isNotFound($object)){
			self::updateNbValues($joinpoint, +1);
		} else {
			$joinpoint->process();
		}
	}

	//------------------------------------------------------------------- aroundForumUtilsGetNbForums
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumUtilsGetNbForums(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$nb = 0;
		if(isset($object->nb_forums))
			$nb = $object->nb_forums;
		$joinpoint->setReturnedValue($nb);
	}

	//------------------------------------------------------------------- aroundForumUtilsGetNbTopics
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumUtilsGetNbTopics(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$nb = 0;
		if(isset($object->nb_topics))
			$nb = $object->nb_topics;
		$joinpoint->setReturnedValue($nb);
	}

	//-------------------------------------------------------------------- aroundForumUtilsGetNbPosts
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumUtilsGetNbPosts(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$nb = 0;
		if(isset($object->nb_posts))
			$nb = $object->nb_posts;
		$joinpoint->setReturnedValue($nb);
	}

	//----------------------------------------------------------------------------------- recalculate
	/**
	 * Recalculate all nb champs.
	 * Use this if you think the counter is false.
	 * Warning : much access of Dao.
	 */
	public static function recalculate()
	{
		foreach(Forum_Utils::getCategories() as $category){
			foreach(Forum_Utils::getForums($category) as $forum){
				$nb_posts_forum = 0;
				$nb_topics_forum = 0;
				foreach(Forum_Utils::getTopics($forum) as $topic){
					$topic->nb_posts = count(Forum_Utils::getPosts($topic)) + 1;
					$nb_posts_forum = $topic->nb_posts;
					$nb_topics_forum++;
					Dao::write($topic);
				}
				$forum->nb_posts = $nb_posts_forum;
				$forum->nb_topics = $nb_topics_forum;
				Dao::write($forum);
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
			'SAF\Wiki\Forum_Utils->getNbPosts()',
			array(__CLASS__, "aroundForumUtilsGetNbPosts")
		);
		Aop::add("around",
			'SAF\Wiki\Forum_Utils->getNbTopics()',
			array(__CLASS__, "aroundForumUtilsGetNbTopics")
		);
		Aop::add("around",
			'SAF\Wiki\Forum_Utils->getNbForums()',
			array(__CLASS__, "aroundForumUtilsGetNbForums")
		);
	}

	//-------------------------------------------------------------------------------- updateNbValues
	/**
	 * @param $joinpoint AopJoinpoint
	 * @param $change int
	 */
	public static function updateNbValues(AopJoinpoint $joinpoint, $change)
	{
		$arguments = $joinpoint->getArguments();
		$object = $arguments[0];
		$object_to_update = array();
		$update_attributes = array();
		if(is_object($object)){
			$class = get_class($object);
			if($class == Forum_Utils::$namespace . "Post"){
				$update_attributes[] = "nb_posts";
			}
			else if($class == Forum_Utils::$namespace . "Topic"){
				$update_attributes[] = "nb_posts";
				$update_attributes[] = "nb_topics";
				$object->nb_posts = $object->nb_posts + $change;
			}
			$parent = $object;
			if(count($update_attributes) > 0){
				while(($parent = Forum_Utils::getParentObject($parent)) != null){
					if(!Forum_Utils::isNotFound($parent)){
						$object_to_update[] = $parent;
					}
					else {
						foreach($update_attributes as $attribute){
							if(property_exists($parent, $attribute)){
								$parent->$attribute = $parent->$attribute + $change;
							}
						}
					}
				}
			}
		}
		$arguments[0] = $object;
		$joinpoint->setArguments($arguments);
		$joinpoint->process();
		foreach($object_to_update as $object){
			$object = Dao::read(Dao::getObjectIdentifier($object), get_class($object));
			$one_exist = false;
			foreach($update_attributes as $attribute){
				if(property_exists($object, $attribute)){
					$object->$attribute = $object->$attribute + $change;
					$one_exist = true;
				}
			}
			if($one_exist && !Forum_Utils::isNotFound($object))
				Dao::write($object);
		}
	}
}
