<?php
namespace ITRocks\Wiki\Search;

use ITRocks\Wiki\Article;
use ITRocks\Wiki\markup\Links;

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

	//------------------------------------------------------------------------------------- $multiple
	/**
	 * @getter
	 * @var boolean
	 */
	public $multiple;

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

	//----------------------------------------------------------------------------------- getMultiple
	/**
	 * @noinspection PhpUnused @getter
	 * @return boolean
	 */
	protected function getMultiple()
	{
		return $this->occurrences > 1;
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * @return string
	 */
	public function link()
	{
		return SL . Links::strUri($this->article->title);
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
