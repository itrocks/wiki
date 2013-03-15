<?php
namespace SAF\Wiki;

class Forum
{
	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public $title;

	//------------------------------------------------------------------------------------- $category
	/**
	 * @link Object
	 * @var Category
	 */
	public $category;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 */
	public $content;

	//------------------------------------------------------------------------------------ $nb_topics
	/**
	 * @var integer
	 */
	public $nb_topics;

	//------------------------------------------------------------------------------------- $nb_posts
	/**
	 * @var integer
	 */
	public $nb_posts;

	//------------------------------------------------------------------------------------ $last_post
	/**
	 * @link Object
	 * @var Post
	 */
	var $last_post;

	//------------------------------------------------------------------------------------- $position
	/**
	 * @var integer
	 */
	public $position;

	public function __toString()
	{
		return $this->title . "";
	}

}
