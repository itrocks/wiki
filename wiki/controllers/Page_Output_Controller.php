<?php
namespace SAF\Wiki;
use SAF\Framework\Button;
use SAF\Framework\View;
use SAF\Framework\Default_Output_Controller;

class Page_Output_Controller extends Default_Output_Controller
{

	protected function getGeneralButtons($object)
	{
		$destination = "#main";
		if($object->name == "Menu")
			$destination = "#menu";
		return Button::newCollection(array(
			array("Edit", View::link($object, "edit"), "edit", array(".submit", $destination))
		));
	}
}
