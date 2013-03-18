<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Category_New_Controller extends List_Controller
{
	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$path = Forum_Path_Utils::getPath();
		$category = new Category();
		if(isset($path["Category"])){
			$category = $path["Category"];
		}
		$parameters = Forum_Utils::generateContent($parameters, $category, $path, "new", 0);
		return View::run($parameters, $form, $files, "Forum", "edit_simple");
	}
}
