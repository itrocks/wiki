<?php
namespace ITRocks\Wiki\Plugins\Anti_Bot;

/**
 * Anti-bot word
 */
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
