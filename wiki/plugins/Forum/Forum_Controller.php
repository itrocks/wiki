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
		$parameters = Forum_Utils::generateContent($parameters, null, Forum_Utils::getBaseUrl(), "output", 3);
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function category(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($this->getLinkParameters($parameters));
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, "output", 2);
		$parameters["path"] = $answer["path"];
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function forum(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($this->getLinkParameters($parameters));
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, "output", 1);
		return View::run($parameters, $form, $files, $class_name, "structure_simple");
	}

	public function topic(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($this->getLinkParameters($parameters));
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$parameters = Forum_Utils::generateContent($parameters, $answer["element"], $base_url, "output", 1);
		return View::run($parameters, $form, $files, $class_name, "output_topic");
	}

	public function edit(Controller_Parameters $parameters, $form, $files, $class_name){
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = Forum_Utils::getElementsRequired($this->getLinkParameters($parameters, 1));
		$base_url = Forum_Utils::getBaseUrl($answer["path"]);
		$element = Forum_Utils::getElementOnGetters($parameters);
		if(!isset($element))
			$element = $answer["element"];
		$parameters = Forum_Utils::generateContent($parameters, $element, $base_url, "edit", 1);
		return View::run($parameters, $form, $files, $class_name, "edit_post");
	}

	private function getLinkParameters($parameters, $start_index = 0){
		$i = $start_index;
		$url = array();
		while(isset($parameters[$i])){
			$url[] = $parameters[$i];
			$i++;
		}
		return $url;
	}


}
