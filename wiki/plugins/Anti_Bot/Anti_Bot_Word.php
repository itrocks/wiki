<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

class Anti_Bot_Word
{
	use Current { current as private pCurrent; }
	/**
	 * @var string
	 */
	var $word;

	function __construct($word)
	{
		$this->word = $word;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Anti_Bot_Word
	 * @return Anti_Bot_Word
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}
}
