<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;

class Update_Nb_Values implements Plugin
{

	private static $class_names = array("SAF\\Wiki\\Post", "SAF\\Wiki\\Topic");

	private static $attributes_number = array("nb_posts", "nb_topics");

	private static $numbers = array();

	//------------------------------------------------------------------------------ addValueToNumber
	/**
	 * Add a value of a number case. If the case not exist in tab, create this with $value to default.
	 * @param $class_name string
	 * @param $value      int Positive or Negative value
	 */
	public static function addValueToNumber($class_name, $value)
	{
		$previous_value = 0;
		if(isset(self::$numbers[$class_name]))
			$previous_value = self::$numbers[$class_name];
		self::$numbers[$class_name] = $previous_value + $value;
	}

	//--------------------------------------------------------------- afterForumControllerUtilsDelete
	public static function afterForumControllerUtilsDelete()
	{
		self::updateNbValues();
	}

	//--------------------------------------------------------- afterForumControllerUtilsDeleteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterForumControllerUtilsDeleteObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		if(is_object($object)){
			$class = get_class($object);
			if(array_search($class, self::$class_names) !== false){
				self::decrementNumber($class);
			}
		}
	}

	//---------------------------------------------------------------- afterForumControllerUtilsWrite
	public static function afterForumControllerUtilsWrite()
	{
		self::updateNbValues();
	}

	//--------------------------------------------------------- beforeForumControllerUtilsWriteObject
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeForumControllerUtilsWriteObject(AopJoinpoint $joinpoint)
	{
		$object = $joinpoint->getArguments()[0];
		if(is_object($object)){
			$class = get_class($object);
			if(array_search($class, self::$class_names) !== false && Forum_Utils::isNotFound($object)){
				self::incrementNumber($class);
				if($class == Forum_Utils::$namespace . "Topic")
					self::incrementNumber(Forum_Names_Utils::getNextClass($class));
			}
		}
	}

	//------------------------------------------------------------------------------- decrementNumber
	/**
	 * @param $class_name string
	 */
	public static function decrementNumber($class_name)
	{
		self::addValueToNumber($class_name, -1);
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

	//------------------------------------------------------------------------------------- getNumber
	/**
	 * @param $class_name string
	 * @return int
	 */
	public static function getNumber($class_name)
	{
		if(isset(self::$numbers[$class_name]))
			return self::$numbers[$class_name];
		return 0;
	}

	//------------------------------------------------------------------------------- incrementNumber
	/**
	 * @param $class_name string
	 */
	public static function incrementNumber($class_name)
	{
		self::addValueToNumber($class_name, 1);
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
		Aop::add("before",
			'SAF\Wiki\Forum_Controller_Utils->writeObject()',
			array(__CLASS__, "beforeForumControllerUtilsWriteObject")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Controller_Utils->write()',
			array(__CLASS__, "afterForumControllerUtilsWrite")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Controller_Utils->delete()',
			array(__CLASS__, "afterForumControllerUtilsDelete")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Controller_Utils->deleteObject()',
			array(__CLASS__, "afterForumControllerUtilsDeleteObject")
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

	//--------------------------------------------------------------------------- updateNbPostsValues
	/**
	 * Updates numbers of elements values, with the tab of number and the path in session.
	 */
	public static function updateNbValues()
	{
		$path = Forum_Path_Utils::getPath();
		foreach(self::$class_names as $key => $class_name){
			$number = self::getNumber($class_name);
			if($number){
				$class = $class_name;
				while($class = Forum_Names_Utils::getParentClass($class)){
					$index = Namespaces::shortClassName($class);
					if(isset($path[$index])){
						$object = $path[$index];
						$attribute_number = self::$attributes_number[$key];
						if(isset($object) && property_exists($object, $attribute_number)){
							$object->$attribute_number += $number;
							Dao::write($object);
						}
					}
				}
				self::$numbers[$class_name] = 0;
			}
		}
		self::$numbers = array();
	}
}
