<?php
namespace SAF\Wiki;
use \SAF\Framework\Default_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Tabs_Builder_Object;
use SAF\Framework\Tab;
use SAF\Framework\View;

class Wiki_User_Register_Controller extends Default_Controller
{
	public function run(Controller_Parameters $parameters, $form, $files, $class_name, $feature_name)
	{
		parent::run($parameters, $form, $files, $class_name, $feature_name);
		$param["User"] = $class_name::current();
		View::run($param, $form, $files, $class_name, "output");
	}
}
