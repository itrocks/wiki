<?php
namespace SAF\Wiki;

class Forum
{
	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	var $title;

	//------------------------------------------------------------------------------------- $category
	/**
	 * @link Object
	 * @var Category
	 */
	var $category;

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
