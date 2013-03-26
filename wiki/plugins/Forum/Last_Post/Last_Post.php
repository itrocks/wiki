<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;

class Last_Post implements Plugin
{

	//---------------------------------------------------------------- afterForumControllerUtilsWrite
	public static function aroundForumControllerUtilsWriteObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$is_new = false;
		if(is_object($object)
			&& get_class($object) == Forum_Utils::$namespace . "Post"
			&& Forum_Utils::isNotFound($object)){
			$is_new = true;
		}
		$joinpoint->process();
		if($is_new)
			self::updateLastPost($object);
	}

	//------------------------------------------------------------------- aroundForumUtilsGetLastPost
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function aroundForumUtilsGetLastPost(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		$return = null;
		$object =
			Forum_Utils::assignAttributeObjectInElement($object, "last_post", "SAF\\Wiki\\Post", false);
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
		if(get_class($object) == Forum_Utils::$namespace . "Post")
			$object = clone $object;
		else
			$object = null;
		$joinpoint->process();
		if(isset($object)){
			$path = Forum_Path_Utils::getPath();
			$attribute_last_post = "last_post";
			$class = Forum_Utils::$namespace . "Post";
			$element = $object;
			while($class = Forum_Names_Utils::getParentClass($class)){
				$short_class_name = Namespaces::shortClassName($class);
				if(isset($path[$short_class_name]))
					$element = $path[$short_class_name];
				else
					$element = Forum_Utils::getParentObject($element);
				if(property_exists($element, $attribute_last_post)){
					if(Forum_Utils::isEqualAttributeAndObject($element, $attribute_last_post, $object)){
						$items = Forum_Utils::getNextElements($element);
						if(get_class($object) == Forum_Utils::$namespace . "Forum"){
							$tmp_tab = array();
							foreach($items as $item){
								$tmp_tab = array_merge($tmp_tab, Forum_Utils::getPosts($item));
							}
							$items = $tmp_tab;
						}
						$last_post = end($items);
						Forum_Utils::setObjectAttribute($element, $attribute_last_post, $last_post);
						Dao::write($element);
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
	public static function recalculate(){
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
		$path = Forum_Path_Utils::getPath();
		$attribute_last_post = "last_post";
		if($object){
			$class = Forum_Utils::$namespace . "Post";
			$element = $object;
			while($class = Forum_Names_Utils::getParentClass($class)){
				$short_class_name = Namespaces::shortClassName($class);
				if(isset($path[$short_class_name]))
					$element = $path[$short_class_name];
				else
					$element = Forum_Utils::getParentObject($element);
				if(property_exists($element, $attribute_last_post)){
					Forum_Utils::setObjectAttribute($element, $attribute_last_post, $object);
					Dao::write($element);
				}
			}
		}
	}
}
