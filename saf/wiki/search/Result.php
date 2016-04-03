<?php
namespace SAF\Wiki\Search;

use SAF\Wiki\Article;

/**
 * Search result
 */
class Result
{

	//-------------------------------------------------------------------------------------- $article
	/**
	 * @link Object
	 * @var Article
	 */
	public $article;

	//---------------------------------------------------------------------------------- $occurrences
	/**
	 * @var integer
	 */
	public $occurrences;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $article     Article
	 * @param $occurrences integer
	 */
	public function __construct($article = null, $occurrences = null)
	{
		if (isset($article))     $this->article     = $article;
		if (isset($occurrences)) $this->occurrences = $occurrences;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->article->title . ' : ' . $this->occurrences . SP . $this->occurrencesText();
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * @return string
	 */
	public function link()
	{
		return SL . strUri($this->article->title);
	}

	//------------------------------------------------------------------------------- occurrencesText
	/**
	 * @return string
	 */
	public function occurrencesText()
	{
		return 'occurrence' . (($this->occurrences > 1) ? 's' : '');
	}

}
