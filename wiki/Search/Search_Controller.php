<?php
namespace SAF\Wiki;

use SAF\Framework\Builder;
use SAF\Framework\Class_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;
use SAF\Framework\Dao;

class Search_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------ $page_class_name
	/**
	 * The class name of the objects into which the search will be performed
	 *
	 * @var string
	 */
	private static $page_class_name = Page::class;

	//-------------------------------------------------------------------------------- $page_var_name
	/**
	 * The name attribute of the objects in which the search should be performed.
	 *
	 * @var string
	 */
	private static $page_var_name = 'name';

	//-------------------------------------------------------------------------------- $page_var_text
	/**
	 * The content attribute of the objects in which the search should be performed.
	 *
	 * @var string
	 */
	private static $page_var_text = 'text';

	//------------------------------------------------------------------------- approximateNameSearch
	/**
	 * Search if a name contain this search word
	 *
	 * @param $search_text string The search word.
	 * @return array
	 */
	private function approximateNameSearch($search_text)
	{
		// approximate name search
		$page_var_name = self::$page_var_name;
		$object = Builder::create(self::$page_class_name);
		$approximate_name  = $this->searchWords($object, $page_var_name, $search_text);
		// search
		$content = array();
		foreach ($approximate_name as $result) {
			$occurrences = $this->countOccurrences($result->$page_var_name, $search_text);
			$content[] = array(
				'occurrence' => $occurrences,
				'label'      => 'occurrence' . (($occurrences > 1) ? 's' : '') . ' in the name',
				'name'       => $result->$page_var_name,
				'link'       => str_replace(' ', '_', $result->$page_var_name)
			);
		}
		return array('title' => 'Approximate name', 'content' => $content);
	}

	//--------------------------------------------------------------------------------- contentSearch
	/**
	 * Search in the content
	 *
	 * @param $search_text string The search word.
	 * @return array
	 */
	private function contentSearch($search_text)
	{
		// content search
		$page_var_name = self::$page_var_name;
		$page_var_text = self::$page_var_text;
		$object = Builder::create(self::$page_class_name);
		$content_search = $this->searchWords($object, $page_var_text, $search_text);
		// search
		$content = array();
		foreach ($content_search as $result) {
			$occurrences = $this->countOccurrences($result->$page_var_text, $search_text);
			$content[] = array(
				'occurrence' => $occurrences,
				'label'      => 'occurrence' . ($occurrences > 1 ? 's' : '') . ' in the text',
				'name'       => $result->$page_var_name,
				'link'       => str_replace(' ', '_', $result->$page_var_name)
			);
		}
		return array('title' => 'Content search', 'content' => $content);
	}

	//------------------------------------------------------------------------------ countOccurrences
	/**
	 * Count the number of occurrences of search word in a text.
	 * @param $text        string The text where search.
	 * @param $search_text string The search string, the different search/word are separate by space.
	 * @return int Return the times number where a search word appears.
	 */
	private function countOccurrences($text, $search_text)
	{
		$tab = explode(' ', $search_text);
		$count = 0;
		foreach ($tab as $element) {
			$count += substr_count(strtolower($text),strtolower($element));
		}
		return $count;
	}

	//------------------------------------------------------------------------------- exactNameSearch
	/**
	 * Search in databases if an object have this exact name.
	 *
	 * @param $search_text string The search word.
	 * @return array
	 */
	private function exactNameSearch($search_text)
	{
		// exact name search
		$page_var_name = self::$page_var_name;
		$object = Builder::create(self::$page_class_name);
		$object->$page_var_name = $search_text;
		$exact_name = Dao::searchOne($object);
		// search
		$content = array();
		if (isset($exact_name)) {
			$content[] = array(
				'occurrence' => '',
				'label'      => 'A page have this exact name',
				'name'       => $exact_name,
				'link'       => str_replace(' ', '_', $exact_name)
			);
		}
		return array('title' => 'Exact name', 'content' => $content);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $feature_name string
	 * @return string
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $feature_name)
	{
		return 'unknown feature Search::' . $feature_name;
	}

	//--------------------------------------------------------------------------------------- runList
	/**
	 * list is the default feature : redirect it to result
	 *
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runList(Controller_Parameters $parameters, $form, $files)
	{
		return $this->runResult($parameters, $form, $files);
	}

	//------------------------------------------------------------------------------------- runOutput
	/**
	 * The output controller always user current Search instance
	 *
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	public function runOutput(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		$search = new Search();
		if (isset($form['search'])) {
			$search->text = $form['search'];
		}
		array_unshift($parameters, $search);
		return View::run($parameters, $form, $files, Search::class, 'output');
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * The controller search and print results if $form['search'] exist, else it print search field.
	 *
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @return mixed
	 */
	private function runResult(Controller_Parameters $parameters, $form, $files)
	{
		if (isset($form['search'])) {
			$search_text = trim($form['search']);
		}
		/** @var $search Search */
		$search = Builder::Create(Search::class);
		$result = array();
		if (!empty($search_text)) {
			$result[] = $this->exactNameSearch($search_text);
			$result[] = $this->approximateNameSearch($search_text);
			$result[] = $this->contentSearch($search_text);
			$search->text = $search_text;
		}
		$search->result = $result;

		$parameters = $parameters->getObjects();
		array_unshift($parameters, $search);
		return View::run($parameters, $form, $files, Search::class, 'result');
	}

	//----------------------------------------------------------------------------------- searchWords
	/**
	 * Search different words in database
	 *
	 * @param $object      object Objects corresponding to the database table.
	 * @param $var         string The var name of the object where search.
	 * @param $search_text string A list of word, separate by space
	 * @return array Return all results.
	 */
	private function searchWords($object, $var, $search_text)
	{
		$tab = explode(' ', $search_text);
		$search_result = array();
		foreach ($tab as $element) {
			$object->$var = '%' . $element . '%';
			$search_result = array_merge($search_result, Dao::search($object));
		}
		return $search_result;
	}

}
