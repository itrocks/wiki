<?php
namespace SAF\Wiki;
use SAF\Framework\Default_Write_Controller;
use SAF\Framework\Controller_Parameters;

class Page_Write_Controller extends Default_Write_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$form["name"] = ucfirst(strtolower(rtrim(ltrim(str_replace("_", " ", $form["name"])))));
		return parent::run($parameters, $form, $files, $class_name);
	}

}
