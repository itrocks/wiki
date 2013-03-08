<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

class Forum_Path
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var array
	 */
	var $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $path array
	 */
	function __construct($path = null)
	{
		$this->path = $path;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Forum_Path
	 * @return Forum_Path
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
