<?php
namespace SAF\Wiki;
use \AopJoinpoint;
use SAF\Framework\AOP;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Search_Object;

class Change_Name_Page_Refactor implements Plugin
{
	static $PAGE_CLASS_NAME = "Page";
	static $PAGE_VAR_NAME = "name";
	static $PAGE_VAR_TEXT = "text";
	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("before",
			"SAF\\Framework\\Default_Write_Controller->run()",
			array(__CLASS__, "beforeDefaultWriteControllerRun")
		);
	}

	//--------------------------------------------------------------- beforeDefaultWriteControllerRun
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeDefaultWriteControllerRun(AopJoinpoint $joinpoint)
	{
		$page_object = Search_Object::newInstance(self::$PAGE_CLASS_NAME);
		$class = get_class($page_object);
		/** @var $parameters Controller_Parameters */
		$parameters = $joinpoint->getArguments()[0];
		$parameters = $parameters->getObjects();
		$object = reset($parameters);
		// It's specific to Pages objects
		if(is_object($object) && get_class($object) == $class){
			$name = self::$PAGE_VAR_NAME;
			$form = $joinpoint->getArguments()[1];
			if($object->$name != $form[$name]){
				$page_var_text = self::$PAGE_VAR_TEXT;
				$old_name = $object->$name;
				$new_name = $form[$name];
				$references = self::generateReferences($old_name, $new_name);
				foreach($references as $reference){
					self::replaceInContent($reference["old_reference"], $reference["new_reference"], $class, $page_var_text);
				}
			}
		}
	}

	//------------------------------------------------------------------------------ replaceInContent
	/**
	 * Replaces a string in all the contents of a field specified by a table $var_text specified by the corresponding class.
	 * @param $old_string string String searched.
	 * @param $new_string string The string used to replace.
	 * @param $class string The class corresponding to the databases table.
	 * @param $var_text string Field's name.
	 */
	public static function replaceInContent($old_string, $new_string, $class, $var_text)
	{
		$object_search = Search_Object::newInstance($class);
		$object_search->$var_text = "%" . $old_string . "%";
		$pages = Dao::search($object_search);
		foreach($pages as $page){
			$page->$var_text =
				str_ireplace(
					$old_string,
					$new_string,
					$page->$var_text
				);
			Dao::write($page);
		}
	}

	//---------------------------------------------------------------------------- generateReferences
	protected static function generateReferences($old_name, $new_name)
	{
		return array(
			array(
				"old_reference" => "[" . $old_name . "]",
				"new_reference" => "[" . $new_name . "]"
			),
			array(
				"old_reference" => "\":" . str_replace(" ", "_", $old_name) . "",
				"new_reference" => "\":" . str_replace(" ", "_", $new_name) . ""
			)
		);
	}
}
