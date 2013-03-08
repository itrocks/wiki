<?php
namespace SAF\Wiki;

/**
 * @representative title
 */
class Topic
{
	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	var $title;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 * @max_length 2000000
	 * @multiline
	 */
	var $content;

	//---------------------------------------------------------------------------------------- $forum
	/**
	 * @link Object
	 * @var Forum
	 */
	var $forum;

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @link Object
	 * @var \SAF\Framework\User
	 */
	var $author;

	public function __toString()
	{
		return $this->title . "";
	}

}
