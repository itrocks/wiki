<?php
namespace SAF\Wiki;
use SAF\Framework\List_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;
use SAF\Framework\Dao;

class Search_List_Controller extends List_Controller
{
	//------------------------------------------------------------------------------ $page_class_name
	/**
	 * @var string
	 */
	private static $page_class_name = 'SAF\Wiki\Page';

	//-------------------------------------------------------------------------------- $page_var_name
	/**
	 * @var string
	 */
	private static $page_var_name = "name";

	//-------------------------------------------------------------------------------- $page_var_text
	/**
	 * @var string
	 */
	private static $page_var_text = "text";

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		if(isset($form["search"])){
			$page_var_name = self::$page_var_name;
			$page_var_text = self::$page_var_text;

			$parameters = parent::getViewParameters($parameters, $class_name);
			$search_type = array();

			// Exactly name search
			$object = new self::$page_class_name();
			$object->$page_var_name = $form["search"];
			$exactly_name = Dao::searchOne($object);
			$content = array();
			if(isset($exactly_name)){
				$content[] = array(
					"occurrence" => "",
					"label" => "A page have this exactly name",
				  "name" => $exactly_name,
				  "link" => str_replace(" ", "_", $exactly_name)
				);
			}
			$search_type[] = array("title" => "Exactly name", "content" => $content);

			// Approximate name search
			$object = new self::$page_class_name();
			$object->$page_var_name = "%" . $form["search"] . "%";
			$approximate_name = Dao::search($object);

			$content = array();
			foreach($approximate_name as $result){
				$occurrences = substr_count(
					strtolower($result->$page_var_name),
					strtolower($form["search"])
				);
				$content[] = array(
					"occurrence" => $occurrences,
					"label" => "occurrence" . ($occurrences > 1 ? "s" : "") . " in the name",
				  "name" => $result->$page_var_name,
				  "link" => str_replace(" ", "_", $result->$page_var_name)
				);
			}
			$search_type[] = array("title" => "Approximate name", "content" => $content);

			// Content search
			$object = new self::$page_class_name();
			$object->$page_var_text = "%" . $form["search"] . "%";
			$content_search = Dao::search($object);

			$content = array();
			foreach($content_search as $result){
				$occurrences = substr_count(
					strtolower($result->$page_var_text),
					strtolower($form["search"])
				);
				$content[] = array(
					"occurrence" => $occurrences,
					"label" => "occurrence" . ($occurrences > 1 ? "s" : "") . " in the text",
					"name" => $result->$page_var_name,
					"link" => str_replace(" ", "_", $result->$page_var_name)
				);
			}
			$search_type[] = array("title" => "Content search", "content" => $content);

			$parameters["search_type"] = $search_type;
			return View::run($parameters, $form, $files, $class_name, "result");
		} else {
			return (new Search_Output_Controller)->run($parameters, $form, $files, $class_name);
		}
	}
}
