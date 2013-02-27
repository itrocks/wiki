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
		if(isset($search_value) && str_replace(" ", "", $search_value) != ""){
			$parameters = parent::getViewParameters($parameters, $form, $class_name);
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
		$approximate_name  = $this->searchWords($object, $page_var_name, $search);

		$content = array();
		foreach($approximate_name as $result){
			$occurrences = $this->countOccurrences($result->$page_var_name, $search);
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
		$content_search = $this->searchWords($object, $page_var_text, $search);

		$content = array();
		foreach($content_search as $result){
			$occurrences = $this->countOccurrences($result->$page_var_text, $search);
			$content[] = array(
				"occurrence" => $occurrences,
				"label" => "occurrence" . ($occurrences > 1 ? "s" : "") . " in the text",
				"name" => $result->$page_var_name,
				"link" => str_replace(" ", "_", $result->$page_var_name)
			);
		}
		return array("title" => "Content search", "content" => $content);
	}

	/**
	 * Count the number of occurrences of search word in a text.
	 * @param $text string The text where search.
	 * @param $search string The search string, the different search/word are separate by space.
	 * @return int Return the times number where a search word appears.
	 */
	public function countOccurrences($text, $search){
		$tab = explode(" ", $search);
		$count = 0;
		foreach($tab as $element){
			$count += substr_count(strtolower($text),strtolower($element));
		}
		return $count;
	}

	/**
	 * Search different words in database
	 * @param $object string Objects corresponding to the database table.
	 * @param $var string The var name of the object where search.
	 * @param $search string A list of word, separate by space
	 * @return array Return all results.
	 */
	public function searchWords($object, $var, $search){
		$tab = explode(" ", $search);
		$searchResult = array();
		foreach($tab as $element){
			$object->$var = "%" . $element . "%";
			$searchResult = $this->mergeArray($searchResult, Dao::search($object));
		}
		return $searchResult;
	}

	/**
	 * Merge two array without duplicate value.
	 * @param $array1 array
	 * @param $array2 array
	 * @return array The result of the merge.
	 */
	public function mergeArray($array1, $array2){
		foreach($array2 as $element){
			if(!in_array($element, $array1))
				$array1[] = $element;
		}
		return $array1;
	}

}
