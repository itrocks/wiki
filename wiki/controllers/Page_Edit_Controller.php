<?php
namespace SAF\Wiki;
use SAF\Framework\View;
use SAF\Framework\Button;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Edit_Controller;

class Page_Edit_Controller extends Default_Edit_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($object)
	{
		$array = parent::getGeneralButtons($object);
		$destination = "#main";
		if($object->name == "Menu")
			$destination = "#menu";
		$array[] = new Button(
			array("Write",  View::link($object, "write"),  "write",  array(Color::of("green"), "#messages", ".submit")),
			"Back", str_replace(" ", "_", $object->name), "back", array(".submit", $destination)
		);
		return $array;
	}

	//----------------------------------------------------------------------------- getViewParameters
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		return parent::getViewParameters($parameters, $class_name);
	}


}
