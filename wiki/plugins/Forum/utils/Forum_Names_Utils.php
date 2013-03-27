<?php
namespace SAF\Wiki;
use SAF\Framework\Namespaces;


class Forum_Names_Utils
{
	public static $list_class =
		array("SAF\\Wiki\\Category", "SAF\\Wiki\\Forum", "SAF\\Wiki\\Topic", "SAF\\Wiki\\Post");
	public static $class_attributes =
		array("SAF\\Wiki\\Category" => array("plural_name" => "Categories"),
		      "SAF\\Wiki\\Forum" => array(),
		      "SAF\\Wiki\\Topic" => array(),
		      "SAF\\Wiki\\Post" => array()
		);

	//--------------------------------------------------------------------------------- getFirstClass
	/**
	 * Return the first class name
	 * @return string A full class name
	 */
	public static function getFirstClass()
	{
		if(isset(self::$list_class[0]))
			return self::$list_class[0];
		return "";
	}

	//---------------------------------------------------------------------------- getFirstShortClass
	/**
	 * Return the first class name
	 * @return string A short class name
	 */
	public static function getFirstShortClass()
	{
		return Namespaces::shortClassName(self::getFirstClass());
	}

	//---------------------------------------------------------------------------------- getNextClass
	/**
	 * Return the next class of the object or class name
	 * @param $class object|string Object or full class name
	 * @return string The class name.
	 */
	public static function getNextClass($class = null)
	{
		if($class == null && isset(self::$list_class[0]))
			return self::$list_class[0];
		if(is_object($class))
			$class = get_class($class);
		$index = array_search($class, self::$list_class);
		if($index !== false && isset(self::$list_class[$index+1])){
			return self::$list_class[$index+1];
		}
		return "";
	}

	//----------------------------------------------------------------------------- getNextShortClass
	/**
	 * Return the next short class of the object or class name
	 * @param $class object|string Object or full class name
	 * @return string The short class name.
	 */
	public static function getNextShortClass($class = null)
	{
		return Namespaces::shortClassName(self::getNextClass($class));
	}

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * Return the parent class of the object or class name
	 * @param $class object|string Object or full class name
	 * @return string The class name.
	 */
	public static function getParentClass($class)
	{
		if(is_object($class))
			$class = get_class($class);
		$index = array_search($class, self::$list_class);
		if(isset(self::$list_class[$index-1])){
			return self::$list_class[$index-1];
		}
		return "";
	}

	//--------------------------------------------------------------------------- getParentShortClass
	/**
	 * Return the parent short class name of the object or class name
	 * @param $class object|string Object or full class name
	 * @return string The short class name.
	 */
	public static function getParentShortClass($class)
	{
		return Namespaces::shortClassName(self::getParentClass($class));
	}

	public static function getPluralName($class_name){
		if(isset(self::$class_attributes[$class_name]["plural_name"]))
			return self::$class_attributes[$class_name]["plural_name"];
		return Namespaces::shortClassName($class_name) . "s";
	}

	//------------------------------------------------------------------------------- getClassInLevel
	/**
	 * Return a short class name in function of level.
	 * @param $level int Current level
	 * @return string Class name
	 */
	public static function getClassInLevel($level)
	{
		$level_name = self::getNextClass();
		for($i=1;$i<=$level;$i++){
			$level_name = self::getNextClass($level_name);
		}
		return Namespaces::shortClassName($level_name);
	}

	//------------------------------------------------------------------------------------- isAParent
	/**
	 * Test if a object or class is a parent of the element.
	 * @param $element  object
	 * @param $parent   object
	 * @return bool Return true if the parent attribute is a parent of the element, false else,
	 * false if an object is not in forum's hierarchy.
	 */
	public static function isAParent($element, $parent){
		if(is_object($element))
			$element = get_class($element);
		if(is_object($parent))
			$parent = get_class($parent);

		$index_element = array_search($element, self::$list_class);
		$index_parent = array_search($parent, self::$list_class);
		if($index_element === false || $index_parent === false)
			return false;
		return $index_parent < $index_element;
	}
}
