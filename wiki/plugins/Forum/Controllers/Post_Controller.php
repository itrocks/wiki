<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;
use SAF\Framework\Default_Write_Controller;

class Post_Controller extends List_Controller
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
		$topic = Dao::read($parameters["Post"]->id_topic, "SAF\\Wiki\\Topic");
		$parameters = Forum_Utils::generateContent($parameters, $topic, $path, "output", 1);
		return View::run($parameters, $form, $files, "Forum", "output_topic");
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Post", $path, "edit", 1);
		return View::run($parameters, $form, $files, "Forum", "edit_post");
	}

}
