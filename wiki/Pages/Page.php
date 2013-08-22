<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

/**
 * @representative name
 */
class Page
{
	use Current { current as private pCurrent; }

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
	 * @textile
	 */
	public $text;

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return trim($this->name);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Sets/gets current page
	 *
	 * @param Page $current
	 * @return Page
	 */
	public static function current(Page $current = null)
	{
		return self::pCurrent($current);
	}

}
