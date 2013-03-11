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
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Topic", $path, "output", 2);
		return View::run($parameters, $form, $files, "Forum", "output_topic");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$params = $parameters->getObjects();
		$object = reset($params);
		if(!is_object($object))
			$object = new Topic();
		$object->content = $form["content"];
		$object->title = $form["title"];
		$object->forum = Forum_Utils::getPath()["Forum"];
		Dao::begin();
		$object = Dao::write($object);
		Dao::commit();
		$parameters->set("Topic", $object);
		return $this->output($parameters, $form, $files, $class_name);
	}
}
