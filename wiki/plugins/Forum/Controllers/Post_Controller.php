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
	/**
	 * Return the parent topic output
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Utils::getPath();
		$topic = $path["Topic"];
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

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$path = Forum_Utils::getPath();
		$params = $parameters->getObjects();
		$object = reset($params);
		if(!is_object($object)){
			$object = new Post();
			$object->topic = $path["Topic"];
		}
		$object->content = $form["content"];
		$object->title = $form["title"];
		$user = User::current();
		if(isset($user)){
			$object->author = $user;
		}
		Dao::begin();
		Dao::write($object);
		Dao::commit();
		return $this->output($parameters, $form, $files, $class_name);
	}

	/*//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(isset($form["confirm"])){
			$parameters = parent::getViewParameters($parameters, $form, $class_name);
			return View::run($parameters, $form, $files, "Forum", "edit_post");
		}
		else {
			$parameters = parent::getViewParameters($parameters, $form, $class_name);
			$path = Forum_Utils::getPath();
			$parameters = Forum_Utils::generateContent($parameters, "Post", $path, "remove", 1);
			return View::run($parameters, $form, $files, "Forum", "edit_post");
		}
	}*/

}
