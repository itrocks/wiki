<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;
use SAF\Framework\Default_Write_Controller;

class Post_New_Controller extends List_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$post = new Post();
		if(isset($path["Post"])){
			$post = $path["Post"];
		}
		Forum_Controller_Utils::formToObject($post, $form);
		$parameters = Forum_Utils::generateContent($parameters, $post, $path, "new", 0);
		return View::run($parameters, $form, $files, "Forum", "edit_post");
	}
}
