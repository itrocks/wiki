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

	public function __toString()
	{
		return $this->title . "";
	}

}
