<?php
namespace SAF\Wiki;

/**
 * @representative title
 */
class Post
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

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @var \SAF\Framework\User
	 */
	var $author;

	//---------------------------------------------------------------------------------------- $topic
	/**
	 * @link Object
	 * @var Topic
	 */
	var $topic;

	//------------------------------------------------------------------------------------ $date_post
	/**
	 * @var int
	 */
	var $date_post;

	public function __toString()
	{
		return $this->title . "";
	}

}
