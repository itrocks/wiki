<?php
namespace SAF\Wiki\Articles;

/**
 * A wiki article
 *
 * @representative title
 */
class Article
{

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
	/**
	 * @param $title string
	 * @return string
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection */
	private function setTitle($title)
	{
		$this->title = $title;
		$this->uri = strUri($title);
	}

}
