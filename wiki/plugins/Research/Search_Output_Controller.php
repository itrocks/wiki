<?php
namespace SAF\Wiki;
use SAF\Framework\Output_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Search_Output_Controller extends Output_Controller
{

	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $class_name);
		$parameters["previous_search"] = "";
		if(isset($form["search"])){
			$parameters["previous_search"] = $form["search"];
		}
		return View::run($parameters, $form, $files, $class_name, "output");
	}
}
