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

	//-------------------------------------------------------------------------------------- $address
	/**
	 * @var string
	 * @multiline
	 */
	public $text;

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return trim($this->name);
	}

}
