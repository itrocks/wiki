<?php
namespace SAF\Wiki;
use SAF\Framework\Button;
use SAF\Framework\Color;
use SAF\Framework\Default_Edit_Controller;
use SAF\Framework\View;

class Page_Edit_Controller extends Default_Edit_Controller
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
				array(".submit", $destination)
			),
			array(
				"Delete", View::link($object, "delete"), "delete",
				array(".submit", $destination)
			)
		));
	}

}
