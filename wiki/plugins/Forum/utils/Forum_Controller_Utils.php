<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Dao;
use SAF\Framework\View;
use SAF\Framework\Namespaces;

class Forum_Controller_Utils
{

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form
	 * @param $files
	 * @param $class_name
	 * @return mixed
	 */
	public static function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(isset($form["confirm"])){
			$objects = $parameters->getObjects();
			Dao::begin();
			foreach ($objects as $object) {
				if (is_object($object)) {
					Dao::delete($object);
				}
			}
			Dao::commit();
			return self::getParentOutput($parameters, $form, $files, $class_name);
		}
		else {
			$parameters = Forum_Controller_Utils::getViewParameters($parameters, $class_name);
			$path = Forum_Utils::getPath();
			$class_name = Namespaces::shortClassName($class_name);
			$parameters = Forum_Utils::generateContent($parameters, $class_name, $path, "delete", 1);
			$parameters["message"] = "Are you sure to permanently delete this element ?";
			return View::run($parameters, $form, $files, "Forum", "write_message");
		}
	}

	//----------------------------------------------------------------------------- getViewParameters
	public static function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		if (empty($object) || !is_object($object) || (get_class($object) !== $class_name)) {
			$object = new $class_name();
			$parameters = array_merge(array($class_name => $object), $parameters);
		}
		return $parameters;
	}

	public static function getOutput(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(is_object($class_name))
			$class_name = get_class($class_name);
		switch(Namespaces::shortClassName($class_name)){
			case "Category":
				return (new Category_Controller())->output($parameters, $form, $files, $class_name);
			case "Forum":
				return (new Forum_Controller())->output($parameters, $form, $files, $class_name);
			case "Topic":
				return (new Topic_Controller())->output($parameters, $form, $files, $class_name);
			case "Post":
				return (new Post_Controller())->output($parameters, $form, $files, $class_name);
			default:
				return (new Category_Controller())->list_all($parameters, $form, $files, $class_name);
		}
	}

	public static function getNextOutput($parameters, $form, $files, $class_name)
	{
		return self::getOutput($parameters, $form, $files, Forum_Utils::getNextClass($class_name));
	}

	public static function getParentOutput($parameters, $form, $files, $class_name)
	{
		return self::getOutput($parameters, $form, $files, Forum_Utils::getParentClass($class_name));
	}
}
