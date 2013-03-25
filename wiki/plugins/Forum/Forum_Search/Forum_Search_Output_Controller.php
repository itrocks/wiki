<?php
namespace SAF\Wiki;
use SAF\Framework\Output_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Forum_Search_Output_Controller extends Output_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Print the search panel view.
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = self::getViewParameters($parameters, $form, $class_name);
		return View::run($parameters, $form, $files, $class_name, "output");
	}

	//----------------------------------------------------------------------------- getViewParameters
	public function getViewParameters(Controller_Parameters $parameters, $form, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters["previous_search"] = "";
		$parameters["action_url"] = "http://" . $_SERVER["HTTP_HOST"]
			. str_replace(".php", "", $_SERVER["SCRIPT_NAME"])
			. "/Forum_Search";
		if(Search::current()){
			$parameters["previous_search"] = Search::current()->search;
		}
		return $parameters;
	}

}
