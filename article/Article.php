<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Dao;
use ITRocks\Framework\History\Has_History;
use ITRocks\Framework\Traits\Date_Logged;
use ITRocks\Wiki\markup\Links;

/**
 * A wiki article
 *
 * @business
 * @representative title
 */
class Article implements Has_History
{
	use Date_Logged;

	//-------------------------------------------------------------------------------------- $history
	/**
	 * History of changes to the article
	 *
	 * @link Collection
	 * @var History[]
	 */
	public $history;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * Main title for the article
	 *
	 * @setter setTitle
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * Full text of the article
	 *
	 * @max_length 2000000
	 * @multiline
	 * @textile
	 * @var string
	 */
	public $text;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * URI for this article, relative to the root of the wiki.
	 * This is a 'nude' URI : it does not begin with /
	 *
	 * @var string
	 */
	public $uri;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->title);
	}

	//-------------------------------------------------------------------------------- getTextHistory
	/**
	 * Returns history of property values changes, newest is first
	 *
	 * @return History[]
	 */
	public function getTextHistory()
	{
		/** @var $history History[] */
		$history = Dao::search(
			['article' => $this, 'property_name' => ['text', 'title']],
			History::class,
			[Dao::sort([Dao::reverse('date')])]
		);
		return $history;
	}

	//--------------------------------------------------------------------------- getHistoryClassName
	/**
	 * @return string
	 */
	public function getHistoryClassName()
	{
		return History::class;
	}

	//-------------------------------------------------------------------------------------- setTitle
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * @param $title string
	 */
	private function setTitle($title)
	{
		$this->title = $title;
		$this->uri = Links::strUri($title);
	}

}
