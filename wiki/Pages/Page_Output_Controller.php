<?php
namespace SAF\Wiki;
use SAF\Framework\Button;
use SAF\Framework\Default_Output_Controller;
use SAF\Framework\View;

class Page_Output_Controller extends Default_Output_Controller
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
			array("New page", "new", "new"),
			array("Edit", View::link($object, "edit"), "edit", array(".submit", $destination))
		));
	}

}
