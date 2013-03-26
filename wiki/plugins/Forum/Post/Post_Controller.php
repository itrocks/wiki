<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Output_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Post_Controller extends Output_Controller
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
		$path = Forum_Path_Utils::getPath();
		$topic = $path["Topic"];
		$parameters = Forum_Utils::generateContent($parameters, $topic, $path, "output", 1);
		return View::run($parameters, $form, $files, "Topic", "output");
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$params = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$params = Forum_Utils::generateContent($params, "Post", $path, "edit", 0);
		$params = array_merge($params, $form);
		return View::run($params, $form, $files, "Forum", "edit_post");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$form = $this->getFormAdditionalParameters($parameters, $form);
		$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
		return $this->output($parameters, $form, $files, $class_name);
	}

	//------------------------------------------------------------------- getFormAdditionalParameters
	/**
	 * @param Controller_Parameters $parameters
	 * @param                       $form
	 * @return mixed
	 */
	public function getFormAdditionalParameters(Controller_Parameters $parameters, $form){
		if(!$this->isModification($parameters)){
			$form["author"] = User::current();
			$form["date_post"] = time();
		}
		else {
			$object = $parameters->getObject("Post");
			$form["last_edited_by"] = User::current()->login;
			$form["last_edited"] = time();
			$form["nb_edited"] = $object->nb_edited + 1;
		}
		return $form;
	}

	//-------------------------------------------------------------------------------- isModification
	public function isModification(Controller_Parameters $parameters)
	{
		return $parameters->getRawParameter("Post") != null;
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

	//--------------------------------------------------------------------------------------- preview
	public function preview(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters->set("preview", Forum_Utils::contentFormatting($form["content"]));
		return $this->edit($parameters, $form, $files, $class_name);
	}

	//----------------------------------------------------------------------------------------- quote
	public function quote(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters->set("post", 0);
		$parameters->set("Post", 0);
		$post = Forum_Path::current()->get("Post");
		$post = Forum_Utils::assignAuthorInPost($post);
		$new_post = new Post();
		$new_post->content = $post->author->login . " :" . "\n\n";
		$new_post->content .= "&gt;" . str_replace("\n","\n&gt;", $post->content);
		Forum_Path::current()->set("Post", $new_post);
		return (new Post_New_Controller())->run($parameters, $form, $files, $class_name);
	}

	//-------------------------------------------------------------------------------------- testForm
	/**
	 * Test the form, and put in array all errors. If there are not errors, array returned is empty.
	 * @param $form   array
	 * @param $object int|object
	 * @return array
	 */
	public function testForm($form, $object)
	{
		$errors = array();
		return $errors;
	}
}
