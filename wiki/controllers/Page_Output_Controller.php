<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Output_Controller;

class Page_Output_Controller extends Default_Output_Controller
{

	protected function getGeneralButtons($object)
	{
		return Button::newCollection(array(
			array("Edit", View::link($object, "edit"), "edit", array(Color::of("green"), "#main"))
		));
	}


	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $class_name);
		if($_SERVER["PATH_INFO"] && $_SERVER["PATH_INFO"] != "new"){
			$object = $parameters[$class_name];
			$object->name = str_replace("_", " ", str_replace("/", "", $_SERVER["PATH_INFO"]));
		}

		return $parameters;
	}
}
