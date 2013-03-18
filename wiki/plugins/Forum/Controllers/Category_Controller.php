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

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
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

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$is_written = false;
		$path = Forum_Utils::getPath();
		if(count($form) > 0){
			$errors = $this->testForm($form, end($path));
			if(count($errors) == 0){
				$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
				$is_written = true;
			}
			$parameters->set("errors", $errors);
		}
		if($is_written){
			return $this->output($parameters, array(), $files, $class_name);
		}
		else {
			return $this->edit($parameters, $form, $files, $class_name);
		}
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name){
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

	//-------------------------------------------------------------------------------------- testForm
	/**
	 * Test the form, and put in array all errors. If there are not errors, array returned is empty.
	 * @param $form   array
	 * @param $object int|object
	 * @return array
	 */
	public function testForm($form, $object){
		$errors = array();
		$error = Forum_Controller_Utils::testTitle($form, $object, "SAF\\Wiki\\Category");
		if($error != null)
			$errors[] = $error;
		return $errors;
	}
}
