<?php
namespace SAF\Wiki;
use SAF\Framework\Output_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Search_Output_Controller extends Output_Controller
{

	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = self::getViewParameters($parameters, $class_name);
		return View::run($parameters, $form, $files, $class_name, "output");
	}

	public function getViewParameters(Controller_Parameters $parameters, $class_name){
		$parameters = parent::getViewParameters($parameters, $class_name);
		$parameters["previous_search"] = "";
		$parameters["action_url"] = "http://" . $_SERVER["HTTP_HOST"]
			. str_replace(".php", "", $_SERVER["SCRIPT_NAME"])
			. "/Search";
		if(Search::current()){
			$parameters["previous_search"] = Search::current()->search;
		}
		return $parameters;
	}
}
