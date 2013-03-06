<?php
namespace SAF\Wiki;
use SAF\Framework\Default_Edit_Controller;
use SAF\Framework\Controller_Parameters;

class Topic_Edit_Controller extends Default_Edit_Controller
{
	public function run(Controller_Parameters $parameters, $form, $files, $class_name){
		return parent::run($parameters, $form, $files, $class_name);
	}
}
