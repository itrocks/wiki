<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Category_Controller extends List_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return $this->output($parameters, $form, $files, $class_name);
	}

	//---------------------------------------------------------------------------------------- output
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Category", $path, "output", 2);
		return View::run($parameters, $form, $files, "Forum", "structure_double");
	}

	//-------------------------------------------------------------------------------------- list_all
	public function list_all(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters = Forum_Utils::generateContent($parameters, null, array(), "output", 3);
		return View::run($parameters, $form, $files, "Forum", "structure_double");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
		return $this->output($parameters, $form, $files, $class_name);
	}

}
