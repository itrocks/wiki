<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

class Anti_Bot_Word
{

	//----------------------------------------------------------------------------------------- $word
	/**
	 * @var string
	 */
	public $word;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $word string
	 */
	function __construct($word)
	{
		$this->word = $word;
	}

}
