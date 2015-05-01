<?php
namespace SAF\Wiki\Articles;

use SAF\Framework\Traits\Date_Logged;

/**
 * A wiki article
 *
 * @business
 * @representative title
 */
class Article
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

	//-------------------------------------------------------------------------------------- setTitle
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * @param $title string
	 * @return string
	 */
	private function setTitle($title)
	{
		$this->title = $title;
		$this->uri = strUri($title);
	}

}
