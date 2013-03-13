<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Output_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;
use SAF\Framework\Namespaces;

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
		$answer = Forum_Utils::getElementsRequired($this->getLinkParameters($parameters, 1));
		$element = Forum_Utils::getElementOnGetters($parameters);
		if(!isset($element))
			$element = $answer["element"];
		$parameters = Forum_Utils::generateContent($parameters, $element, $answer["path"], "edit", 1);
		return View::run($parameters, $form, $files, $class_name, "edit_post");
	}

	//---------------------------------------------------------------------------------------- output
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Forum", $path, "output", 2);
		return View::run($parameters, $form, $files, "Forum", "structure_simple");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
		return $this->output($parameters, $form, $files, $class_name);
	}

}
