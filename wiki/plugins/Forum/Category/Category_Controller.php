<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Category_Controller extends List_Controller
{

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Category", $path, "edit", 0);
		return View::run($parameters, $form, $files, "Forum", "edit_simple");
	}

	//-------------------------------------------------------------------------------------- list_all
	public function list_all(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$class_name = get_class(new Category());
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters = Forum_Utils::generateContent($parameters, null, array(), "output", 2);
		if(isset($parameters["Category"])){
			unset($parameters["Category"]);
		}
		return View::run($parameters, $form, $files, "Forum", "structure_double");
	}

	//---------------------------------------------------------------------------------------- output
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Category", $path, "output", 1);
		return View::run($parameters, $form, $files, "Forum", "structure_simple");
	}

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return $this->output($parameters, $form, $files, $class_name);
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
}
