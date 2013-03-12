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

	//---------------------------------------------------------------------------------------- $forum
	/**
	 * @link Object
	 * @var Forum
	 */
	var $forum;

	//----------------------------------------------------------------------------------- $first_post
	/**
	 * @link Object
	 * @var Post
	 */
	var $first_post;

	public function __toString()
	{
		return $this->title . "";
	}

}
