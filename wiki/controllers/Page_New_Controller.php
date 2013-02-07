<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_New_Controller;
use SAF\Framework\View;
use SAF\Framework\Button;

class Page_New_Controller extends Default_New_Controller
{

	protected function getGeneralButtons($object)
	{
		$destination = "#main";
		/*		if($object->name == "Menu")
					$destination = "#menu";*/
		return Button::newCollection(array(
				array("Enregistrer",  View::link($object, "write"),  "write",  array(".submit", "#messages")),
				array("Annuler", str_replace(" ", "_", $object->name), "back", array(".submit", $destination))
			));
	}


	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $class_name);
		// TODO : pass the name of the new page by the $GETS when it possible
		if($_SERVER["PATH_INFO"] && $_SERVER["PATH_INFO"] != "new"){
			if($parameters){
				$object = $parameters[$class_name];
				$object->name = str_replace("_", " ", str_replace("/", "", $_SERVER["PATH_INFO"]));
			}
		}

		return $parameters;
	}
}
