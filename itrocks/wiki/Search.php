<?php
namespace ITRocks\Wiki;

/**
 * Search data
 */
class Search
{

	//----------------------------------------------------------------------------------------- $text
	/**
	 * The search string
	 *
	 * @var string
	 */
	public $text;

	//--------------------------------------------------------------------------------------- $result
	/**
	 * @var array each element is a Search_Result[]
	 */
	public $results;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->text);
	}

}
