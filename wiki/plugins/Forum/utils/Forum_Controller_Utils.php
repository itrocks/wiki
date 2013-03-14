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
	 * Ask confirmation, check if user accept, delete the item and charge parent output
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public static function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(isset($form["confirm"])){
			/** @var  $objects object[] */
			$objects = $parameters->getObjects();
			Dao::begin();
			self::delete_objects($objects);
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

	//-------------------------------------------------------------------------------- delete_objects
	/**
	 * Delete a list of objects. If this object have next elements, delete the next elements.
	 * @param $objects object[]|null List of objects to delete
	 */
	public static function delete_objects($objects)
	{
		if(isset($objects)){
			foreach ($objects as $object) {
				if (is_object($object)) {
					$objects_child = Forum_Utils::getNextElements($object);
					if($objects_child != null){
						self::delete_objects($objects_child);
					}
					Dao::delete($object);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- formToObject
	/**
	 * Assign form contains to object attributes. If element of the form is an object, assign to the id element.
	 * @param $object object
	 * @param $form   array
	 * @return object Return the object filled
	 */
	public static function formToObject($object, $form)
	{
		foreach ($form as $name => $value) {
			if (property_exists($object,$name)) {
				$object->$name = $value;
				if(is_object($value)){
					$name = "id_" . $name;
					$object->$name = Dao::getObjectIdentifier($value);
				}
			}
		}
		return $object;
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $class_name string
	 * @return array Return an array with the parameters.
	 */
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

	//------------------------------------------------------------------------------------- getOutput
	/**
	 * Return the result of the output for the controller of the class_name
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return string
	 */
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

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an element.
	 * @param $parameters         Controller_Parameters
	 * @param $form               array
	 * @param $class_name         string
	 * @param $attributes_object  array The attributes that are objects to search and update too.
	 * It must be in this form :
	 * array("attribute_name" => "object_full_class_name")
	 * @return Controller_Parameters Return the parameters updated
	 */
	public static function write(Controller_Parameters $parameters, $form, $class_name, $attributes_object = array())
	{
		$parent_type = Forum_Utils::getParentShortClass($class_name);
		$short_class_name = Namespaces::shortClassName($class_name);
		$path = Forum_Utils::getPath();
		$params = $parameters->getObjects();
		$object = reset($params);
		if(!is_object($object)){
			$object = new $class_name();
		}
		$parent_type_lower = strtolower($parent_type);
		if(isset($path[$parent_type]))
			$form[$parent_type_lower] = $path[$parent_type];
		$object = Forum_Controller_Utils::formToObject($object, $form);
		foreach($attributes_object as $attribute => $class_attribute){
			$object = Forum_Utils::assignAttributeObjectInElement($object, $attribute, $class_attribute);
			if(isset($object->$attribute)){
				$object->$attribute = Forum_Controller_Utils::formToObject($object->$attribute, $form);
			}
		}
		Dao::begin();
		foreach($attributes_object as $attribute => $class_attribute){
			if(isset($object->$attribute)){
				Dao::write($object->$attribute);
			}
		}
		$object = Dao::write($object);
		Dao::commit();
		$parameters->set($short_class_name, $object);
		Forum_Path::current()->set($short_class_name, Dao::read($object, $class_name));
		return $parameters;
	}
}
