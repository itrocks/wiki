<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Forum_Controller extends List_Controller
{
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters = Forum_Utils::generateContent($parameters, null, Forum_Utils::getBaseUrl(), 3);
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function category(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($parameters[0]);
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, 2);
		$parameters["path"] = $answer["path"];
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function forum(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($parameters[0], $parameters[1]);
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, 1);
		return View::run($parameters, $form, $files, $class_name, "structure_simple");
	}

	public function topic(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($parameters[0], $parameters[1], $parameters[2]);
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, 1);
		return View::run($parameters, $form, $files, $class_name, "output_topic");
	}


}
