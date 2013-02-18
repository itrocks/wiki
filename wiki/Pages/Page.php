<?php
namespace SAF\Wiki;

/**
 * @representative name
 */
class Page
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 * @max_length 2000000
	 * @multiline
	 */
	public $text;

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return trim($this->name);
	}

}
