<?php
namespace SAF\Wiki;
use SAF\Framework\View;
use SAF\Framework\Button;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Edit_Controller;
use SAF\Framework\Color;

class Page_Edit_Controller extends Default_Edit_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($object)
	{
		$destination = "#main";
/*		if($object->name == "Menu")
			$destination = "#menu";*/
		return Button::newCollection(array(
			array("Enregistrer",  View::link($object, "write"),  "write",  array(Color::of("green"), ".submit", "#messages")),
			array("Annuler", str_replace(" ", "_", $object->name), "back", array(".submit", $destination)),
			array("Supprimer", View::link($object, "delete"), "delete", array(".submit", $destination))
		));
	}

	//----------------------------------------------------------------------------- getViewParameters
	protected function getViewParameters(Controller_Parameters $parameters, $class_name)
	{
		return parent::getViewParameters($parameters, $class_name);
	}


}
