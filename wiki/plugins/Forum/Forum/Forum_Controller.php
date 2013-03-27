<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Output_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Forum_Controller extends Output_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return $this->output($parameters, $form, $files, $class_name);
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Forum", $path, "edit", 0);
		return View::run($parameters, $form, $files, "Forum", "edit_simple");
	}

	//---------------------------------------------------------------------------------------- output
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Forum", $path, "output", 1);
		return View::run($parameters, $form, $files, "Forum", "structure_simple");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$errors = array();
		if(count($form) > 0){
			$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
			$errors = $parameters->getRawParameter("errors");
		}
		if(count($errors) == 0){
			return $this->output($parameters, array(), $files, $class_name);
		}
		else {
			return $this->edit($parameters, $form, $files, $class_name);
		}
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}
}
