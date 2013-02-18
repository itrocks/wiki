<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_New_Controller;
use SAF\Framework\View;
use SAF\Framework\Button;
use SAF\Framework\Color;

class Page_New_Controller extends Default_New_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string object or class name
	 * @return Button[]
	 */
	protected function getGeneralButtons($object)
	{
		$destination = "#main";
		return Button::newCollection(array(
			array(
				"Save", View::link($object, "write"), "write",
				array(Color::of("green"), ".submit", "#messages")
			),
			array(
				"Cancel", str_replace(" ", "_", $object->name), "back",
				array(Color::of("green"), ".submit", $destination)
			)
		));
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Controller_Parameters
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $class_name);
		// TODO : pass the name of the new page by the $GETS when possible
		if (!empty($_SERVER["PATH_INFO"]) && ($_SERVER["PATH_INFO"] != "new")) {
			if ($parameters) {
				$object = $parameters[$class_name];
				$object->name = str_replace("_", " ", str_replace("/", "", $_SERVER["PATH_INFO"]));
			}
		}
		return $parameters;
	}

}
