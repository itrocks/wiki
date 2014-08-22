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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->title);
	}

}
