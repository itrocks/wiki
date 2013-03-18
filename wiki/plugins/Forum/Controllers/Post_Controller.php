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
		$path = Forum_Utils::getPath();
		$topic = $path["Topic"];
		$parameters = Forum_Utils::generateContent($parameters, $topic, $path, "output", 1);
		return View::run($parameters, $form, $files, "Forum", "output_topic");
	}

	//------------------------------------------------------------------------------------------ edit
	public function edit(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$params = parent::getViewParameters($parameters, $form, $class_name);
		if(!Forum_Utils::hasElementAtAttribute($params["Post"], "topic"))
			return (new Topic_Controller())->edit($parameters, $form, $files, $class_name);
		$path = Forum_Utils::getPath();
		$params = Forum_Utils::generateContent($params, "Post", $path, "edit", 0);
		return View::run($params, $form, $files, "Forum", "edit_post");
	}

	//----------------------------------------------------------------------------------------- write
	public function write(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$form["author"] = User::current();
		$parameters = Forum_Controller_Utils::write($parameters, $form, $class_name);
		return $this->output($parameters, $form, $files, $class_name);
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return Forum_Controller_Utils::delete($parameters, $form, $files, $class_name);
	}

	//----------------------------------------------------------------------------------------- quote
	public function quote(Controller_Parameters $parameters, $form, $files, $class_name){
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

}
