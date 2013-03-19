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
					$object = $path[Namespaces::shortClassName($class)];
					$attribute_number = self::$attributes_number[$key];
					if(property_exists($object, $attribute_number)){
						$object->$attribute_number += $number;
						Dao::write($object);
					}
				}
				self::$numbers[$class_name] = 0;
			}
		}
		self::$numbers = array();
	}
}
