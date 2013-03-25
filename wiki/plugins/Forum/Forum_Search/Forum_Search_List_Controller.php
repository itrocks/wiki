<?php
namespace SAF\Wiki;
use SAF\Framework\List_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;
use SAF\Framework\Dao;

class Forum_Search_List_Controller extends List_Controller
{

	//------------------------------------------------------------------------------ $page_class_name
	/**
	 * The class name of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_class_name = 'SAF\Wiki\Post';

	//-------------------------------------------------------------------------------- $page_var_name
	/**
	 * The name attribute of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_var_name = "title";

	//-------------------------------------------------------------------------------- $page_var_text
	/**
	 * The content attribute of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_var_text = "content";

	//------------------------------------------------------------------------------------------- run
	/**
	 * The controller search and print results if $form["search"] exist,
	 * else print search field.
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(isset($form["search"]))
			$search_value = $form["search"];
		if(isset($search_value) && str_replace(" ", "", $search_value) != ""){
			return (new Forum_Search_Advanced_Controller())->run($parameters, $form, $files, $class_name);
		} else {
			return (new Forum_Search_Output_Controller())->run($parameters, $form, $files, $class_name);
		}
	}
}
