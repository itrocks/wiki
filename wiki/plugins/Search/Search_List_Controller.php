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
	 * The class name of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_class_name = 'SAF\Wiki\Page';

	//-------------------------------------------------------------------------------- $page_var_name
	/**
	 * The name attribute of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_var_name = "name";

	//-------------------------------------------------------------------------------- $page_var_text
	/**
	 * The content attribute of the objects in which the search should be performed.
	 * @var string
	 */
	private static $page_var_text = "text";

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
		if(isset($search_value) && $search_value != ""){
			$parameters = parent::getViewParameters($parameters, $class_name);
			$search_type = array();
			$search_type[] = $this->exactlyNameSearch($search_value);
			$search_type[] = $this->approximateNameSearch($search_value);
			$search_type[] = $this->contentSearch($search_value);
			$parameters["search_type"] = $search_type;
			$search = new Search();
			$search->search = $search_value;
			Search::current($search);
			return View::run($parameters, $form, $files, $class_name, "result");
		} else {
			return (new Search_Output_Controller())->run($parameters, $form, $files, $class_name);
		}
	}

	//----------------------------------------------------------------------------- exactlyNameSearch
	/**
	 * Search in databases if an object have this exactly name.
	 * @param $search string The search word.
	 * @return array
	 */
	private function exactlyNameSearch($search)
	{
		// Exactly name search
		$page_var_name = self::$page_var_name;
		$object = new self::$page_class_name();
		$object->$page_var_name = $search;
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
		return array("title" => "Exactly name", "content" => $content);
	}

	//------------------------------------------------------------------------- approximateNameSearch
	/**
	 * Search if a name contain this search word.
	 * @param $search string The search word.
	 * @return array
	 */
	private function approximateNameSearch($search)
	{
		// Approximate name search
		$page_var_name = self::$page_var_name;
		$object = new self::$page_class_name();
		$object->$page_var_name = "%" . $search . "%";
		$approximate_name = Dao::search($object);

		$content = array();
		foreach($approximate_name as $result){
			$occurrences = substr_count(
				strtolower($result->$page_var_name),
				strtolower($search)
			);
			$content[] = array(
				"occurrence" => $occurrences,
				"label" => "occurrence" . ($occurrences > 1 ? "s" : "") . " in the name",
				"name" => $result->$page_var_name,
				"link" => str_replace(" ", "_", $result->$page_var_name)
			);
		}
		return array("title" => "Approximate name", "content" => $content);
	}

	//--------------------------------------------------------------------------------- contentSearch
	/**
	 * Search in the content.
	 * @param $search string The search word.
	 * @return array
	 */
	private function contentSearch($search)
	{
		// Content search
		$page_var_name = self::$page_var_name;
		$page_var_text = self::$page_var_text;
		$object = new self::$page_class_name();
		$object->$page_var_text = "%" . $search . "%";
		$content_search = Dao::search($object);

		$content = array();
		foreach($content_search as $result){
			$occurrences = substr_count(
				strtolower($result->$page_var_text),
				strtolower($search)
			);
			$content[] = array(
				"occurrence" => $occurrences,
				"label" => "occurrence" . ($occurrences > 1 ? "s" : "") . " in the text",
				"name" => $result->$page_var_name,
				"link" => str_replace(" ", "_", $result->$page_var_name)
			);
		}
		return array("title" => "Content search", "content" => $content);
	}
}
