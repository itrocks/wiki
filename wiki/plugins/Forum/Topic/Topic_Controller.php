<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Topic_Controller extends List_Controller
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
		$parameters["mode"] = "output";
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Topic", $path, "output", 1);
		return View::run($parameters, $form, $files, "Topic", "output");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$errors = array("Error");
		if(count($form) > 0){
			$form = (new Post_Controller())->getFormAdditionalParameters($parameters, $form);
			$attributes = array("first_post" => "SAF\\Wiki\\Post");
			$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name, $attributes);
			$errors = $parameters->getRawParameter("errors");
		}
		if(count($errors) == 0){
			return $this->output($parameters, array(), $files, $class_name);
		}
		else {
			return $this->edit($parameters, $form, $files, $class_name);
		}
	}

	//--------------------------------------------------------------------------------------- preview
	public function preview(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters->set("preview", Forum_Utils::contentFormatting($form["content"]));
		return $this->edit($parameters, $form, $files, $class_name);
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Topic", $path, "edit", 0);
		$parameters = array_merge($parameters, $form);
		if(isset($form["content"]))
			$parameters["main_post"][0]["content"] = $form["content"];
		return View::run($parameters, $form, $files, "Forum", "edit_topic");
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

}
