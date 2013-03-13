<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Default_Delete_Controller;
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
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Topic", $path, "output", 2);
		return View::run($parameters, $form, $files, "Forum", "output_topic");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$is_written = false;
		if(count($form) > 0){
			$params = clone $parameters;
			$params = $params->getObjects();
			$object = reset($params);
			if(!is_object($object))
				$object = new Topic();
			Forum_Utils::assignTopicFirstPost($object);
			$object->first_post->content = $form["content"];
			$object->title = $form["title"];
			$object->forum = Forum_Utils::getPath()["Forum"];
			$object->user = User::current();
			Dao::begin();
			Dao::write($object->first_post);
			$topic = Dao::write($object);
			Dao::commit();
			$parameters->set("Topic", $topic);
			$is_written = true;
		}
		if($is_written){
			return $this->output($parameters, array(), $files, $class_name);
		}
		else {
			return $this->edit($parameters, $form, $files, $class_name);
		}
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
		$parameters = Forum_Utils::generateContent($parameters, "Topic", $path, "edit", 1);
		return View::run($parameters, $form, $files, "Forum", "edit_topic");
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name){
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

}
