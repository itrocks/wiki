<?php
namespace SAF\Wiki;

use SAF\Framework\History\Has_History;
use SAF\Framework\Traits\Date_Logged;
use SAF\Wiki\Article\History;

/**
 * A wiki article
 *
 * @business
 * @representative title
 */
class Article implements Has_History
{
	use Date_Logged;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @setter setTitle
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @max_length 2000000
	 * @multiline
	 * @textile
	 * @var string
	 */
	public $text;

	//------------------------------------------------------------------------------------------ $uri
	/**
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
		$this->uri = strUri($title);
	}

}