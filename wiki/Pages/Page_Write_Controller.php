<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Default_Write_Controller;

class Page_Write_Controller extends Default_Write_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$form["name"] = rtrim(ltrim(str_replace("_", " ", $form["name"])));
		return parent::run($parameters, $form, $files, $class_name);
	}

}
