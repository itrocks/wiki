<?php
namespace ITRocks\Wiki\Search;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Class_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Map;
use ITRocks\Framework\View;
use ITRocks\Wiki\Article;
use ITRocks\Wiki\Search;

/**
 * Search class controller
 */
class Controller implements Class_Controller
{

	//-------------------------------------------------------------------- Search controller features
	const F_FORM   = 'form';
	//-------------------------------------------------------------------------------------- F_RESULT
	const F_RESULT = 'result';

	//------------------------------------------------------------------------------ countOccurrences
	/**
	 * Count the number of occurrences of search word in a text.
	 * @param $text        string The text where search.
	 * @param $search_text string The search string, the different search/word are separate by space.
	 * @return int Return the times number where a search word appears.
	 */
	private function countOccurrences($text, $search_text)
	{
		$words = explode(SP, $search_text);
		$count = 0;
		foreach ($words as $word) {
			$count += substr_count(strtolower($text), strtolower($word));
		}
		return $count;
	}

	//------------------------------------------------------------------------------ exactTitleSearch
	/**
	 * Search in databases if an object have this exact text as title
	 *
	 * @param $search_text string The exact searched text
	 * @return array
	 */
	private function exactTitleSearch($search_text)
	{
		/** @var $article Article */
		$article = Dao::searchOne(['title' => $search_text], Article::class);
		return $article ? [Builder::create(Result::class, [$article, 0])] : [];
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array[]
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $feature_name)
	{
		user_error('Unknown feature ' . $feature_name);
		return;
	}

	//--------------------------------------------------------------------------------------- runForm
	/**
	 * The form controller always use current Search instance
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function runForm(Parameters $parameters, array $form, array $files)
	{
		/** @var $search Search */
		$search = $parameters->getMainObject(Search::class);
		$parameters = $parameters->getObjects();
		if (isset($form['search'])) {
			$search->text = $form['search'];
		}
		return View::run($parameters, $form, $files, Search::class, self::F_FORM);
	}

	//------------------------------------------------------------------------------------- runResult
	/**
	 * The controller search and print results if $form['search'] exist, else it print search field
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function runResult(Parameters $parameters, array $form, array $files)
	{
		if (isset($form['search'])) {
			$search_text = trim($form['search']);
		}
		/** @var $search Search */
		$search = $parameters->getMainObject(Search::class);
		$search->results = [];
		if (!empty($search_text)) {
			$search->results['Exact name']       = $this->exactTitleSearch($search_text);
			$search->results['Approximate name'] = $this->searchWords('title', $search_text);
			$search->results['Content search']   = $this->searchWords('text', $search_text);
			$search->text                        = $search_text;
		}
		return View::run($parameters->getObjects(), $form, $files, Search::class, self::F_RESULT);
	}

	//----------------------------------------------------------------------------------- searchWords
	/**
	 * Search different words into the articles database
	 *
	 * @param $property_name string The property name 'title' or 'text' of the article where to
	 *                              search into
	 * @param $search_text   string A list of words, separated by spaces
	 * @return Result[] All results
	 */
	private function searchWords($property_name, $search_text)
	{
		$words = explode(SP, $search_text);
		$map = new Map();
		foreach ($words as $element) {
			$map->add(Dao::search([$property_name => Func::like('%' . $element . '%')], Article::class));
		}
		/** @var $articles Article[] */
		$articles = $map->objects;
		/** @var $search_results Result[] */
		$search_results = [];
		foreach ($articles as $article) {
			$occurrences = $this->countOccurrences($article->$property_name, $search_text);
			$search_results[] = Builder::create(Result::class, [$article, $occurrences]);
		}
		return $search_results;
	}

}
