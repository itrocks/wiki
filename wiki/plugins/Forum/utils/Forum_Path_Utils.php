<?php
namespace SAF\Wiki;
use SAF\Framework\Namespaces;

class Forum_Path_Utils
{

	//----------------------------------------------------------------------------------- stopInLevel
	/**
	 * Stop the path in a level, corresponding of a class name, and delete all object after.
	 * @param $path array
	 * @param $class_name string|object
	 * @return array
	 */
	public static function stopInLevel($path, $class_name){
		$path_stopped = array();
		if(is_object($class_name))
			$class_name = get_class($class_name);
		$class_name = Namespaces::shortClassName($class_name);
		foreach($path as $key => $element){
			$path_stopped[$key] = $element;
			if(strtolower($key) == strtolower($class_name))
				break;
		}
		return $path_stopped;
	}

	//------------------------------------------------------------------------------ addPathAttribute
	/**
	 * Add the path in attributes
	 * @param $parameters array Array of attributes where add the path attributes
	 * @param $path       array The path, an array of objects
	 * @return array Return the attributes with the path
	 */
	public static function addPathAttribute($parameters, $path)
	{
		$url = Forum_Utils::getBaseUrl();
		$path_array = array(array("type" => "index", "title" => "Index", "link" => $url));
		foreach($path as $key => $element){
			if(isset($element->title))
				$title = $element->title;
			else
				$title = get_class($element);
			$url = Forum_Utils::getUrl($element, $url);
			$path_array[] = array("type" => $key, "title" => $title, "link" => $url);
		}
		$parameters["path"] = $path_array;
		return $parameters;
	}
}
